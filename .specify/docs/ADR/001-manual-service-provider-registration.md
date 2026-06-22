ADR-001: Service Provider Registration
File: docs/adr/001-manual-service-provider-registration.md

Key Points to Cover
Context:

Laravel 11+ auto-discovers service providers by default
DormSys uses modular architecture with 10 modules
Each module has its own service provider
Need explicit control over module loading order
Decision:

Manual registration in bootstrap/providers.php
No package auto-discovery for module providers
Explicit array order determines initialization sequence
Rationale:

Explicit dependencies: Clear module load order
IDE support: Auto-completion works properly
Testability: Can load subsets of modules in tests
Fail-fast: Typos caught at boot, not at runtime
Alternatives:

Auto-discovery (Laravel default)

Pros: Zero configuration
Cons: Hidden dependencies, hard to debug
Rejected: Violates F01-072 (explicit dependencies)
Package-based registration (composer.json)

Pros: Standard Laravel approach
Cons: Tight coupling, can’t control order
Rejected: Prevents test isolation
Consequences:

Positive: Compile-time safety, clear architecture
Negative: Developer must manually register (intentional friction)
Neutral: One extra step when creating modules
Implementation:

php
// bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,
    
    // Core modules (no dependencies)
    Modules\Identity\Infrastructure\IdentityServiceProvider::class,
    Modules\Employee\Infrastructure\EmployeeServiceProvider::class,
    
    // Dependent modules
    Modules\Request\Infrastructure\RequestServiceProvider::class,
    // ... rest in dependency order
];
References:

Constitution F01-072: Explicit module dependencies
Spec01 Section 3.2: Module Registration Strategy