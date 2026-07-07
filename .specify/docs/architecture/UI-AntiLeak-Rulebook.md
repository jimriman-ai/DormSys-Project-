# DormSys UI Anti-Leak Architecture

# Contract (MPEP-Aligned)

Document Version: 1.0.0

Scope

Applies to all Presentation Layer implementations
(Blade, Livewire, Alpine.js and future UI technologies)

Target Stack: Laravel 13, Livewire 3, Alpine.js, Tailwind CSS

## 1. Core Philosophy: The "Dumb Screen" Principle

The UI layer (Blade templates, Livewire components, and Alpine.js scripts) is
strictly a delivery and visibility mechanism. It has no authority to make decisions,
infer states, calculate rules, or orchestrate workflows.

[User Action] ──(Direct Trigger)──> [Livewire Component] ──(No Logic /
Delegation)──> [Backend Service / Command] │ [UI Re-render] <──(Raw State/
DTO)── [Livewire View] <──(Pass Raw Result)

◄─────────────────────┘

## 2. Rules of Interaction & Mutation (Human-Initiated vs. System-Driven)

### Rule 2.1: Human Input Surfaces (PR-N6 - Requests)

• Form Binding: Livewire properties ( wire:model ) must map directly to
request parameters. No conversion of formats or semantic manipulation is
allowed in the component.
• Validation: Frontend constraints are limited to raw input formatting (e.g.,
HTML5 validation types). All business validation and lifecycle checks must
be delegated to the backend validators or DTO instantiation.

• Errors: Error rendering must loop through or display the raw validator
response array returned from the HTTP/Livewire exception handler. No
error-code-to-human-meaning mapping in PHP components.

### Rule 2.2: System Execution Surfaces (PR-N7 - Lottery

& Allocation)

• Execution Visibility: The UI only displays snapshots and status flags
computed by the backend. It must never calculate things like "Lottery
Readiness" (e.g., evaluating if date bounds are met).
• Action Triggering: Triggering a system execution (e.g., executing a lottery
dry-run or committing allocations) must be implemented as a fire-and-
forget request execution token.
• Zero Input Generation: The UI does not construct input parameters for
execution; it merely calls execute() or commit() on pre-configured
backend targets.

### Rule 2.3: Terminal State Surfaces (PR-N8 - Check-In)

• Consumption Only: The UI displays the final state of an allocation. Actions
here (e.g., "Confirm Arrival") are terminal transitions. The UI does not
determine if a user can check-in; it queries the backend's capability flags
for that specific target.

## 3. Strict Code Constraints for Livewire

Components

Forbidden Patterns (Anti-Patterns)

1. The "Calculated Status" Smell:
2. FORBIDDEN: Implementing business logic or conditional labels directly
within the component (e.g., checking timestamps to determine status).

3. ALLOWED: Calling a backend presenter/service method that returns the
finalized, domain-validated status label.
4. The "Authorization Mirroring" Smell:
5. FORBIDDEN: Replicating role/permission checks inside the component
logic (e.g., Auth::user()->hasRole(...) ).
6. ALLOWED: Delegating all authorization to the backend via
Gate::allows(...) or Policy-based checks.
7. The "Workflow Orchestration" Smell:
8. FORBIDDEN: Executing database transactions or multiple service calls
inside the Livewire component method.
9. ALLOWED: Passing the request to a single Command Handler or Service,
keeping the UI method limited to triggering a unit of work.

## 4. Test Verification Requirements (Regression

Prevention)

Every UI PR must deliver tests verifying these architectural boundaries: *Access
Control: Guest/Unauthorized users are redirected before rendering.* Zero-
Leakage Rendering: The component correctly maps domain DTO fields to the
view without mutations. *Error Propagation: Validation/Execution exception
messages are surfaced verbatim in the UI.* Component Surface Verification:
Assert that Livewire action methods contain no DB transactions, raw queries, or
domain state alterations.
