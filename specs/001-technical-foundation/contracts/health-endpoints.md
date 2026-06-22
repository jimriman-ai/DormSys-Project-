# Contract: Health & Readiness Endpoints

**Version**: 1.0.0 | **Spec**: Spec01 Foundation

## Purpose

Defines baseline HTTP contracts for operational health verification.

---

## GET `/up`

Laravel 12 built-in health route.

| Field | Value |
|-------|-------|
| Method | GET |
| Auth | None |
| Success | HTTP 200 |
| Failure | HTTP 500 |

**Use**: Load balancer liveness probe.

---

## GET `/api/health` (Foundation Stub)

| Field | Value |
|-------|-------|
| Method | GET |
| Auth | None (restrict in production via middleware in later spec) |
| Content-Type | `application/json` |

### Response 200

```json
{
  "status": "ok",
  "timestamp": "2026-06-22T12:00:00Z",
  "checks": {
    "database": "ok",
    "redis": "ok"
  }
}
```

### Response 503

```json
{
  "status": "degraded",
  "timestamp": "2026-06-22T12:00:00Z",
  "checks": {
    "database": "failed",
    "redis": "ok"
  }
}
```

## Check Definitions

| Check | Pass Condition |
|-------|----------------|
| `database` | `DB::connection()->getPdo()` succeeds |
| `redis` | `Redis::ping()` returns truthy |

**Maps to**: Constitution NFR-05, quickstart Section 8
