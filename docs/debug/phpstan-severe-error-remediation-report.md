# PHPStan severe-error remediation report

**Date:** 2026-07-11  
**Command under test:** `php vendor/bin/phpstan analyse --no-progress`  
**Status:** Severe (incomplete) analysis failures removed; normal level-8 findings remain.

---

## Root cause

Two interacting analysis-time failures made PHPStan results incomplete:

### 1. Spatie Ray container resolution during Larastan worker boot / crash

- `spatie/ray` `helpers.php` registers `register_shutdown_function(fn () => ray()->throwExceptions())`.
- `spatie/laravel-ray` `RayServiceProvider::boot()` → `setProjectName()` calls `ray()->project(...)` whenever `config('app.name') !== 'Laravel'` (this app uses `DormSys`).
- Larastan boots the Laravel application inside parallel workers. `ray()` resolves `Spatie\LaravelRay\Ray` through the container.
- Under worker memory pressure, that boot/shutdown container work contributed to fatal worker exits (exit 255) and incomplete analysis. No application code calls `ray()`; the package is `require-dev` and auto-discovered.

### 2. Parallel worker resource exhaustion (Windows)

- Bare `analyse` without `--memory-limit` left workers near PHP’s default ceiling (~256–512M).
- Multiple concurrent Larastan boots also hit Windows `VirtualAlloc` / Fiber stack failures (`paging file is too small`), reported as PHPStan `general_errors` / child process exit 255.

### Secondary pitfall (during remediation)

- `parameters.memoryLimit` is **not** a valid PHPStan 2.x neon key. Adding it caused `Invalid configuration: Unexpected item 'parameters › memoryLimit'` and silent config load failure. Memory must stay on the CLI (`--memory-limit=1G`, already used by `composer run phpstan` / CI).

Package removal, global ignore, or production Ray disable were rejected: Ray is a legitimate local debug dependency; the failure is analysis-scoped.

---

## Files changed

| File | Change |
|------|--------|
| `phpstan-bootstrap.php` | **Added.** Sets `RAY_ENABLED=false`, `SEND_EXCEPTIONS_TO_RAY=false`, and `\Spatie\Ray\Ray::$projectName = 'phpstan'` before Larastan boots so `setProjectName()` skips `ray()`. Also `ini_set('memory_limit', '1G')` for the parent process. |
| `phpstan.neon` | Registers `bootstrapFiles: [phpstan-bootstrap.php]`; caps `parallel.maximumNumberOfProcesses` to `2`. Does **not** set invalid `memoryLimit`. |

No production app code, `ray.php`, providers, or Composer packages were changed.

---

## Why the fix is correct

1. **Analysis-scoped:** Bootstrap runs only when PHPStan loads config; runtime Sail/HTTP behavior is unchanged.
2. **Targets the Ray trigger:** Pre-setting `Ray::$projectName` makes `setProjectName()` skip the boot-time `ray()` call (see `RayServiceProvider::setProjectName()`). Env flags disable transmission / exception forwarding without removing the package.
3. **Addresses incomplete parallel runs:** Lower process cap reduces Windows commit-charge / Fiber failures; Composer/CI already pass `--memory-limit=1G` for worker ceilings (`ini_set` alone does not control PHPStan worker `-d memory_limit`).
4. **Minimal:** No broad refactors, no suppressions of real type errors, no tooling disablement.

---

## Verification result

Exact command:

```text
php vendor/bin/phpstan analyse --no-progress
```

Also verified with:

```text
php vendor/phpstan/phpstan/phpstan.phar analyse --no-progress --memory-limit=1G
```

| Check | Result |
|-------|--------|
| Config loads | Pass (no `Unexpected item` / empty fatal) |
| `general_errors` (severe / incomplete) | **0** |
| Child process / Ray / OOM incomplete exits | **Absent** |
| Normal level-8 findings | **16** (exit code 1 expected) |

Severe bootstrap/worker incompleteness is resolved. Remaining output is ordinary PHPStan level-8 diagnostics.

---

## Remaining non-severe PHPStan findings (16)

Reported separately; not fixed in this remediation (out of severe-error scope).

### Infrastructure repositories (6)

- `DormitoryStructureReadRepository`: `list*` methods declared `list<…>` but return `array<int, …>` (lines 30, 63, 82, 100, 120).
- `DormitoryStructureWriteRepository::listBedsByRoomId()`: same `list` vs `array<int, …>` mismatch (line 201).

### Feature / unit tests (10)

- `DormitoryStructureMutationTest`: missing iterable value type on `seedMutationHierarchy()`; Eloquent `create()` union property access (lines 29, 81–83, 117, 119).
- `DormitoryReadIntegrationTest`: redundant `toBeInstanceOf()` (line 12).
- `DormitoryTest`: `method_exists()` always true/false (lines 65–67).

---

## Notes for operators

- Prefer `composer run phpstan` or add `--memory-limit=1G` on large runs; worker memory is driven by that CLI flag.
- On constrained Windows hosts, keep `parallel.maximumNumberOfProcesses` low if `VirtualAlloc` / Fiber errors return under load.
- Do not reintroduce `parameters.memoryLimit` into `phpstan.neon` (invalid on PHPStan 2.x).
