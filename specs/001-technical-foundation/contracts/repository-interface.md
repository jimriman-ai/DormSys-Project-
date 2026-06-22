# Contract: Repository Interface

**Version**: 1.0.0 | **Spec**: Spec01 Foundation

## Purpose

Standard persistence contract for all module repositories. Domain layer depends on this interface; Infrastructure provides Eloquent-backed implementations in future specs.

## Interface

```php
namespace App\Shared\Domain\Contracts;

interface BaseRepository
{
    /**
     * @param object $entity Module-specific domain entity
     */
    public function save(object $entity): void;

    /**
     * @return object|null Module-specific domain entity
     */
    public function findById(string $id): ?object;

    /**
     * @param object $entity Module-specific domain entity
     */
    public function delete(object $entity): void;
}
```

## Implementation Rules

| Rule | Description |
|------|-------------|
| REP-01 | Interface defined in `App\Shared\Domain\Contracts` |
| REP-02 | Module-specific interfaces extend `BaseRepository` (e.g., `EmployeeRepository`) |
| REP-03 | Eloquent models MUST NOT leak into Domain layer |
| REP-04 | Repositories are the only Infrastructure entry point for persistence |
| REP-05 | Cross-module data access via Application Services, not foreign repositories |

## Mapping Contract (Future)

Each repository implementation MUST provide:
- `toDomain(Model $model): Entity` — infrastructure → domain
- `toModel(Entity $entity): Model` — domain → infrastructure

Not required in Spec01; documented for implementation phase consistency.
