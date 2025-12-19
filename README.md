# Master Server

A centralized master server for managing Unreal Engine 5 game servers. Provides server listing, status tracking, and admin management with Redis-cached data for high performance.

## Features

- **Server Registry**: Track and list available game servers
- **Server Types**: Support for listen-servers and dedicated-servers
- **Admin System**: Create and manage game instances with custom data structures
- **Steam Integration**: Games identified by Steam App ID
- **Redis Cache**: Fast, isolated data storage per game
- **Secure API**: HMAC authentication for servers, Sanctum for admins

## Requirements

- Docker & Docker Compose
- PHP 8.3+
- Node.js 20+
- Composer

## Quick Start

### 1. Clone and Setup

```bash
git clone https://github.com/Solpadoin/master-server.git
cd master-server
cp .env.example .env
```

### 2. Start Docker Containers

```bash
docker-compose up -d
```

### 3. Install Dependencies

```bash
# PHP dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Node dependencies (for admin panel)
npm install
npm run dev
```

### 4. Access the Application

- **API**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin

## Architecture

```
app/
├── Http/
│   ├── Controllers/Api/V1/     # API Controllers
│   ├── Middleware/             # Custom middleware
│   ├── Requests/               # Form validation
│   └── Resources/              # API Resources
├── Services/
│   ├── Contracts/              # Service interfaces
│   └── Implementations/        # Service implementations
├── Repositories/
│   ├── Contracts/              # Repository interfaces
│   └── Implementations/        # Repository implementations
├── DTOs/                       # Data Transfer Objects
├── Models/                     # Eloquent models
└── Enums/                      # PHP enums
```

## API Endpoints

### Client API (Read-Only)

```
GET /api/v1/games/{steamAppId}/servers          # List servers
GET /api/v1/games/{steamAppId}/servers/{id}     # Get server details
```

Query parameters:
- `max_ping` - Filter by maximum ping
- `region` - Filter by region
- `type` - Filter by server type (listen/dedicated)
- `not_full` - Only show servers with available slots

### Server API (Authenticated)

```
POST   /api/v1/servers/register     # Register server
POST   /api/v1/servers/heartbeat    # Send heartbeat
DELETE /api/v1/servers/{id}         # Unregister server
```

Headers required:
- `X-API-Key`: Your API key
- `X-Timestamp`: Unix timestamp
- `X-Signature`: HMAC-SHA256 signature

### Admin API (Sanctum Auth)

```
GET/POST       /api/v1/admin/games              # List/Create games
GET/PUT/DELETE /api/v1/admin/games/{id}         # Game CRUD
POST           /api/v1/admin/games/{id}/api-keys # Generate API key
GET/POST       /api/v1/admin/instances          # List/Create instances
PUT            /api/v1/admin/instances/{id}/schema # Update schema
```

## Server Authentication

Game servers authenticate using HMAC signatures:

```
Signature = HMAC-SHA256(
    METHOD + "\n" + PATH + "\n" + TIMESTAMP + "\n" + BODY,
    API_SECRET
)
```

## Redis Data Structure

Data is isolated per game using key prefixes:

```
game:{steam_app_id}:servers     # Hash of all servers
game:{steam_app_id}:heartbeats  # Hash of heartbeat timestamps
```

## Development

### AI Development

See `docs/AI/README.md` for coding guidelines when using AI assistants.

### Code Style

```bash
# Run Laravel Pint
docker-compose exec app ./vendor/bin/pint
```

### Testing

```bash
docker-compose exec app php artisan test
```

## Environment Variables

| Variable | Description |
|----------|-------------|
| `STEAM_API_KEY` | Steam Web API key |
| `SERVER_AUTH_SECRET` | Secret for server HMAC signing |
| `REDIS_HOST` | Redis host (default: redis) |

## License

Proprietary - Ghostbe Studio
