ADR-003: Migration Template Standards
File: docs/adr/003-migration-template-standards.md

Key Points to Cover
Context:

All tables must use UUIDv7 primary keys (F01-054)
All timestamps must be timezone-aware (F01-055)
Soft deletes with deleted_by tracking required (F01-058)
Audit fields (created_by) mandatory (F01-059)
Laravel’s default timestamps() uses datetime (wrong type)
Decision:

Override Laravel’s migration stub
Provide stubs/migration.stub with correct defaults
Force developers to use correct column types
Rationale:

Prevent mistakes: Default timestamps() is wrong for us
Consistency: All tables follow same pattern
Discoverability: New devs see correct pattern immediately
Constitution compliance: Enforces F01-054, F01-055, F01-058, F01-059
Alternatives:

Code review enforcement

Pros: Flexible, no tooling
Cons: Human error, inconsistent
Rejected: Already happened in past projects
Helper methods (e.g., $table->auditColumns())

Pros: Reusable, less boilerplate
Cons: Hidden behavior, hard to customize
Rejected: Migrations should be explicit
Custom Migration base class

Pros: Can add validation
Cons: Non-standard Laravel approach
Rejected: Overcomplicates simple problem
Consequences:

Positive: Zero wrong migrations, self-documenting
Negative: Slightly more verbose migrations
Neutral: One-time setup cost
Implementation:

php
// stubs/migration.stub (excerpt)
Schema::create('<?php echo $table; ?>', function (Blueprint $table) {
    // Primary key (UUIDv7)
    $table->uuid('id')->primary();
    
    // Timezone-aware timestamps (not timestamps()!)
    $table->timestampTz('created_at')->nullable();
    $table->timestampTz('updated_at')->nullable();
    
    // Audit fields
    $table->uuid('created_by')->nullable();
    $table->foreign('created_by')
          ->references('id')
          ->on('users')
          ->nullOnDelete();
    
    // Soft deletes with audit
    $table->timestampTz('deleted_at')->nullable();
    $table->uuid('deleted_by')->nullable();
    $table->foreign('deleted_by')
          ->references('id')
          ->on('users')
          ->nullOnDelete();
});
Migration Publishing Command:

bash
php artisan stub:publish
# Then edit stubs/migration.stub
References:

Constitution F01-054: UUIDv7 primary keys
Constitution F01-055: Timezone-aware timestamps
Constitution F01-058: Soft deletes with tracking
Constitution F01-059: Audit fields
Research.md Decision D15: UUIDv7 implementation