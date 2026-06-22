# Development Environment

## Requirements

- Docker Desktop 4.x+
- Docker Compose 2.x+
- Git
- Composer (for initial dependency install on host)

## Services

| Service | Image | Host Port | Container Hostname |
|---------|-------|-----------|-------------------|
| Laravel app | Sail PHP 8.5 runtime | 80 | `laravel.test` |
| PostgreSQL 17 | `postgres:17` | 5432 | `pgsql` |
| Redis 7 | `redis:7` | 6379 | `redis` |

Laravel Sail wraps Docker Compose commands for local development.

## Quick Start

```bash
# Clone repository
git clone <repository-url>
cd "DormSys Project"

# Install PHP dependencies (host)
composer install

# Copy environment file
cp .env.example .env
php artisan key:generate

# Start all services
./vendor/bin/sail up -d

# Wait until services are healthy
docker ps

# Run migrations
./vendor/bin/sail artisan migrate

# Run foundation tests
./vendor/bin/sail artisan test --filter=Foundation
```

### Optional Shell Alias

```bash
# Linux/macOS (bash)
echo "alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'" >> ~/.bashrc

# Linux/macOS (zsh)
echo "alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'" >> ~/.zshrc

# Windows PowerShell (session)
function sail { if (Test-Path sail) { sh sail @args } else { sh vendor/bin/sail @args } }
```

## Common Commands

```bash
# Start services
sail up -d

# Stop services
sail down

# View logs
sail logs -f

# Container shell
sail shell

# Artisan
sail artisan <command>

# Tests
sail artisan test
```

## Service Names in `.env`

Inside Docker, use **service names** — not `localhost` or `127.0.0.1`:

| Variable | Value |
|----------|-------|
| `DB_HOST` | `pgsql` |
| `REDIS_HOST` | `redis` |
| `REDIS_PASSWORD` | *(empty)* |
| `QUEUE_CONNECTION` | `redis` |
| `CACHE_STORE` | `redis` |

## Health Checks

PostgreSQL and Redis include Docker health checks. Verify:

```bash
docker ps
# STATUS column should show "(healthy)" for pgsql and redis
```

## Connectivity Verification

```bash
# Database
sail artisan migrate

# Redis cache (Tinker)
sail artisan tinker
>>> Cache::put('health-check', 'ok', 60);
>>> Cache::get('health-check');
>>> Redis::ping();

# Queue
>>> dispatch(function () { logger('Queue health check passed'); });
>>> exit
sail artisan queue:work redis --once
sail logs
```

## Troubleshooting

### Port already in use

```env
FORWARD_DB_PORT=5433
FORWARD_REDIS_PORT=6380
```

Then `sail down` and `sail up -d`.

### Database connection refused

1. Run `docker ps` and wait for `pgsql` to be **healthy**
2. Confirm `DB_HOST=pgsql` in `.env`
3. Confirm `DB_DATABASE=dormsys` matches `POSTGRES_DB`

### Redis connection timeout

1. Confirm `REDIS_HOST=redis`
2. Confirm `REDIS_PASSWORD=` is empty (not `null`)
3. Test: `sail artisan tinker` → `Redis::ping()`

### Queue jobs not processing

1. Confirm `QUEUE_CONNECTION=redis`
2. Run worker: `sail artisan queue:work redis --once`
3. Check logs: `sail logs -f`
