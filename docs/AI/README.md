# AI Development Guidelines for Master Server

> **IMPORTANT FOR AI ASSISTANTS**: This document MUST be read and followed when working on this repository. Always check `docs/AI/README.md` first before making any changes.

## Project Overview

Master Server is a centralized server management system for game servers built with Unreal Engine 5. It provides:

- **Server Registry**: List of available game servers with status and metadata
- **Server Types**: Support for listen-servers and dedicated-servers
- **Admin System**: Create and manage game instances with custom data structures
- **Steam Integration**: Games identified by Steam App ID (e.g., 2965940)
- **Redis Cache**: Fast, isolated data storage per game

## Architecture Principles

### SOLID Principles (MANDATORY)

1. **Single Responsibility**: Each class has one job
2. **Open/Closed**: Open for extension, closed for modification
3. **Liskov Substitution**: Subtypes must be substitutable for base types
4. **Interface Segregation**: Many specific interfaces over one general
5. **Dependency Inversion**: Depend on abstractions, not concretions

### Code Organization

```
app/
├── Http/
│   ├── Controllers/       # Thin controllers - delegate to services
│   ├── Requests/          # Form request validation
│   └── Resources/         # API Resources for JSON responses
├── Services/              # Business logic layer
│   ├── Contracts/         # Service interfaces
│   └── Implementations/   # Service implementations
├── DTOs/                  # Data Transfer Objects
├── Repositories/          # Data access layer
│   ├── Contracts/         # Repository interfaces
│   └── Implementations/   # Repository implementations
├── Models/                # Eloquent models
├── Enums/                 # PHP 8.1+ enums
└── Exceptions/            # Custom exceptions
```

### Mandatory Patterns

#### 1. Services
All business logic MUST be in services, not controllers.

```php
// GOOD
class GameServerService implements GameServerServiceInterface
{
    public function __construct(
        private readonly GameServerRepositoryInterface $repository,
        private readonly CacheServiceInterface $cache,
    ) {}

    public function getActiveServers(GameServerFilterDTO $filter): GameServerCollection
    {
        // Business logic here
    }
}

// BAD - Logic in controller
class GameServerController
{
    public function index()
    {
        $servers = GameServer::where('active', true)->get(); // NO!
    }
}
```

#### 2. DTOs (Data Transfer Objects)
Use DTOs for data passing between layers.

```php
readonly class GameServerFilterDTO
{
    public function __construct(
        public int $gameId,
        public ?int $maxPing = null,
        public ?string $region = null,
        public ?ServerType $serverType = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            gameId: (int) $request->input('game'),
            maxPing: $request->input('max_ping') ? (int) $request->input('max_ping') : null,
            region: $request->input('region'),
            serverType: $request->input('type') ? ServerType::from($request->input('type')) : null,
        );
    }
}
```

#### 3. API Resources
Always use Resources for API responses.

```php
class GameServerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'port' => $this->port,
            'players' => $this->current_players,
            'max_players' => $this->max_players,
            'ping' => $this->ping,
            'server_type' => $this->server_type->value,
            'metadata' => $this->whenLoaded('metadata'),
        ];
    }
}
```

#### 4. Repository Pattern
Abstract data access through repositories.

```php
interface GameServerRepositoryInterface
{
    public function findByGameId(int $gameId): Collection;
    public function findActive(GameServerFilterDTO $filter): Collection;
    public function create(GameServerDTO $data): GameServer;
    public function update(GameServer $server, GameServerDTO $data): GameServer;
}
```

### N+1 Prevention (CRITICAL)

**ALWAYS** prevent N+1 queries:

```php
// GOOD - Eager loading
$servers = GameServer::with(['game', 'metadata', 'players'])->get();

// GOOD - Explicit eager loading in repository
public function findWithRelations(int $id): ?GameServer
{
    return GameServer::with($this->defaultRelations)->find($id);
}

// BAD - Will cause N+1
$servers = GameServer::all();
foreach ($servers as $server) {
    echo $server->game->name; // N+1!
}
```

**Use Laravel Debugbar or Telescope in development to detect N+1 queries.**

### Redis Cache Strategy

#### Game Data Isolation
Each game's data MUST be isolated in Redis using prefixed keys:

```php
// Key structure: game:{steam_id}:{resource}:{identifier}
"game:2965940:servers:list"
"game:2965940:server:abc123"
"game:2965940:instance:def456"
```

#### Cache Service Pattern
```php
interface GameCacheServiceInterface
{
    public function getServers(int $gameId): ?array;
    public function setServers(int $gameId, array $servers, int $ttl = 60): void;
    public function invalidateGame(int $gameId): void;
}

class GameCacheService implements GameCacheServiceInterface
{
    private function getKey(int $gameId, string $resource): string
    {
        return "game:{$gameId}:{$resource}";
    }
}
```

### API Security

#### Authentication Layers

1. **Admin API**: Laravel Sanctum with role-based access
2. **Server API**: API keys with HMAC signature validation
3. **Client API**: Read-only, rate-limited, game-scoped tokens

#### Server Authentication Flow
```
1. Server registers with admin-provided API key
2. Each request includes: X-API-Key, X-Timestamp, X-Signature
3. Signature = HMAC-SHA256(method + path + timestamp + body, secret)
4. Validate timestamp within 5-minute window
5. Validate signature matches
```

#### Client Authentication Flow
```
1. Client requests read token with game ID
2. Token scoped to specific game only
3. Cannot access other games' data
4. Rate limited per IP/token
```

### Coding Standards

#### Type Safety
- Use PHP 8.1+ features: readonly, enums, named arguments
- Always declare return types
- Use union types sparingly, prefer specific types

```php
// GOOD
public function findById(int $id): ?GameServer
{
    return GameServer::find($id);
}

// BAD
public function findById($id)
{
    return GameServer::find($id);
}
```

#### Naming Conventions
- Services: `{Entity}Service` (e.g., `GameServerService`)
- Repositories: `{Entity}Repository`
- DTOs: `{Entity}DTO` or `{Action}{Entity}DTO`
- Resources: `{Entity}Resource`
- Requests: `{Action}{Entity}Request`
- Enums: Singular, PascalCase (e.g., `ServerType`, `ServerStatus`)

#### File Organization
- One class per file
- Group related files in subdirectories when > 5 files
- Keep controllers thin (max 10 lines per method ideally)

### Testing Requirements

- Unit tests for all services
- Feature tests for all API endpoints
- Use factories and seeders for test data
- Mock external services (Redis, Steam API)

### Environment Configuration

Required environment variables:
```env
# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1

# Steam API
STEAM_API_KEY=your_steam_api_key

# Server Auth
SERVER_AUTH_SECRET=your_hmac_secret
SERVER_AUTH_TIMESTAMP_TOLERANCE=300
```

## Quick Reference

### Creating a New Feature

1. Create interface in `Services/Contracts/`
2. Create implementation in `Services/Implementations/`
3. Create DTO if passing complex data
4. Create Repository if data access needed
5. Create Resource for API response
6. Create FormRequest for validation
7. Bind interface in ServiceProvider
8. Write tests

### API Endpoints Structure

```
GET    /api/v1/games/{gameId}/servers          # List servers (Client)
GET    /api/v1/games/{gameId}/servers/{id}     # Get server (Client)
POST   /api/v1/servers/heartbeat               # Server heartbeat (Server)
POST   /api/v1/servers/register                # Register server (Server)
DELETE /api/v1/servers/{id}                    # Unregister (Server)

# Admin endpoints
GET    /api/v1/admin/games                     # List games
POST   /api/v1/admin/games                     # Create game
GET    /api/v1/admin/instances                 # List instances
POST   /api/v1/admin/instances                 # Create instance
PUT    /api/v1/admin/instances/{id}/schema     # Update instance schema
```

## Remember

- **Read this document first** before any code changes
- **Services over controllers** - keep controllers thin
- **DTOs for data transfer** - never pass arrays between layers
- **Resources for responses** - consistent API output
- **Eager load relations** - prevent N+1 at all costs
- **Isolate game data** - Redis keys prefixed with game ID
- **Type everything** - PHP 8.1+ strict typing
- **Test everything** - no untested business logic
