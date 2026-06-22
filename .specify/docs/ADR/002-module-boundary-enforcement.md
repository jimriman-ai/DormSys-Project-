ADR-002: Module Boundary Enforcement
File: docs/adr/002-module-boundary-enforcement.md

Key Points to Cover
Context:

DDD Lite architecture requires strict module boundaries
Modules must communicate only through public contracts
Need automated enforcement (can’t rely on code review)
Must catch violations before production
Decision:

Pest Architecture Tests for runtime boundaries
PHPStan custom rules for static analysis
CI pipeline blocks merges on violations
Rationale:

Fail-fast: Violations caught in CI, not production
Zero runtime overhead: Static analysis only
Self-documenting: Tests show allowed/forbidden patterns
Incremental enforcement: Can add rules progressively
Alternatives:

Runtime reflection + exceptions

Pros: Guaranteed enforcement
Cons: Performance cost, production errors
Rejected: F01-026 forbids runtime overhead
Manual code review only

Pros: Flexible, no tooling needed
Cons: Human error, inconsistent
Rejected: Not scalable
Deptrac (PHP architecture tool)

Pros: Mature tool, visual graphs
Cons: Requires separate config, hard to customize
Rejected: Pest tests more maintainable
Consequences:

Positive: Compile-time safety, clear violations
Negative: Tests must be updated when adding modules
Neutral: Adds ~30s to CI pipeline
Implementation:

php
// tests/Architecture/ModuleBoundariesTest.php
test('modules cannot directly depend on other modules')
    ->expect('Modules\*\Domain')
    ->not->toUse([
        'Modules\*\Domain',
        'Modules\*\Application',
    ])
    ->ignoreUse([
        'Modules\Shared\*', // Shared kernel allowed
    ]);

test('application layer cannot use infrastructure')
    ->expect('Modules\*\Application')
    ->not->toUse('Modules\*\Infrastructure');
References:

Constitution F01-026 to F01-038: Module boundaries
Spec01 Section 4.1: Architecture Testing