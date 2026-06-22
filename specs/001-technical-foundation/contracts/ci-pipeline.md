# Contract: CI Pipeline

**Version**: 1.0.0 | **Spec**: Spec01 Foundation

## Purpose

Defines the baseline GitHub Actions CI workflow contract for foundation validation.

## Trigger

| Event | Branches |
|-------|----------|
| `push` | `main`, `develop`, `001-*` feature branches |
| `pull_request` | All branches |

## Job: `quality`

### Environment

| Component | Version |
|-----------|---------|
| Runner | `ubuntu-latest` |
| PHP | 8.4 |
| PostgreSQL service | 17 |
| Redis service | 7 |

### Environment Variables

```yaml
DB_CONNECTION: pgsql
DB_HOST: 127.0.0.1
DB_PORT: 5432
DB_DATABASE: dormsys_test
DB_USERNAME: postgres
DB_PASSWORD: postgres
CACHE_STORE: redis
QUEUE_CONNECTION: redis
REDIS_HOST: 127.0.0.1
REDIS_PORT: 6379
```

### Steps (Ordered)

| Step | Command | Fail Condition |
|------|---------|----------------|
| 1. Checkout | `actions/checkout@v4` | — |
| 2. Setup PHP | `shivammathur/setup-php@v2` with `php-version: 8.4`, extensions: `pgsql, redis, mbstring, xml, curl, zip` | — |
| 3. Install Composer deps | `composer install --no-interaction --prefer-dist` | Non-zero exit |
| 4. Copy env | `cp .env.example .env` + `php artisan key:generate` | Non-zero exit |
| 5. Wait for PostgreSQL | `php artisan migrate --force` | Migration failure |
| 6. Code style | `composer run pint -- --test` | Formatting violations |
| 7. Static analysis | `composer run phpstan` | Any PHPStan error |
| 8. Tests | `php artisan test` | Any test failure |

### Success Criteria

- Total pipeline duration target: **< 3 minutes** (SC-007)
- All steps green

## Local Parity

Developers SHOULD run steps 6–8 via Sail before push (see [quickstart.md](../quickstart.md)).

## Deferred CI Steps (Out of Spec01 Scope)

- Docker image build
- Security vulnerability scan (Composer audit — recommended add in next spec)
- Code coverage upload
- Deployment
