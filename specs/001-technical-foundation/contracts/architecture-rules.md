# Contract: Architecture Layer Boundaries

**Version**: 1.0.0 | **Spec**: Spec01 Foundation

## Purpose

Machine-enforceable rules protecting Clean Architecture and modular monolith boundaries. Validated by Pest architecture tests.

## Layer Dependency Rules

```text
Presentation → Application → Domain ← Infrastructure
```

| Rule ID | Enforcement | Description |
|---------|-------------|-------------|
| ARCH-01 | `arch()->expect('App\Modules\*\Domain')->not->toUse('Illuminate\Database\*')` | Domain has no Eloquent |
| ARCH-02 | `arch()->expect('App\Modules\*\Domain')->not->toUse('App\Modules\*\Infrastructure')` | Domain independent of Infrastructure |
| ARCH-03 | `arch()->expect('App\Modules\*\Domain')->not->toUse('App\Modules\*\Presentation')` | Domain independent of Presentation |
| ARCH-04 | `arch()->expect('App\Modules\*\Infrastructure')->not->toUse('App\Modules\*\Presentation')` | Infrastructure does not depend on Presentation |
| ARCH-05 | Custom: no `App\Modules\{A}\*` imports `App\Modules\{B}\Infrastructure` where A ≠ B | No cross-module infrastructure imports |
| ARCH-06 | `App\Shared\Domain` MUST NOT depend on any `App\Modules\*` namespace | Shared kernel is inward-facing |

## Module Isolation

Cross-module communication permitted only via:
- Application Services (sync)
- Domain Events dispatched through Application layer (async)

**Prohibited**:
- Direct Eloquent queries across module model namespaces
- Cross-module foreign keys (Constitution AP-04)

## Test Location

```
tests/Architecture/
├── LayerDependencyTest.php
└── ModuleBoundaryTest.php
```

## Failure Mode

Architecture test failures block CI merge — same severity as PHPStan errors.
