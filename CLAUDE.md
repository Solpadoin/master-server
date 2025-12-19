# Claude Code Instructions

## IMPORTANT: Read First

Before making any changes to this repository, **ALWAYS** read and follow the guidelines in:

**`docs/AI/README.md`**

This document contains:
- Project architecture overview
- SOLID principles requirements
- Code organization patterns
- Service, DTO, Resource patterns
- N+1 prevention guidelines
- Redis cache strategy
- API security patterns
- Coding standards

## Quick Rules

1. **Services**: All business logic in services, not controllers
2. **DTOs**: Use for data transfer between layers
3. **Resources**: Always use API Resources for responses
4. **Repositories**: Abstract all data access
5. **N+1**: Always eager load relations
6. **Redis**: Prefix all keys with game ID for isolation
7. **Types**: Use PHP 8.1+ strict typing everywhere
8. **Tests**: Write tests for all business logic

## Project Context

Master Server for Unreal Engine 5 game servers:
- Server registry with status tracking
- Listen-server and dedicated-server support
- Admin-defined custom data structures per game
- Steam API integration (games have Steam App IDs)
- Redis cache for fast server data
- Secure API with separate auth for servers/clients
