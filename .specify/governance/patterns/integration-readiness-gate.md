# Integration Readiness Gate

## Status

Governance Pattern

## Purpose

This pattern defines the mandatory readiness gate before any cross-module integration implementation authorization.

The purpose is to prevent implementation from starting when any of the following are unclear:

* the consuming module
* the required capability
* the accepted Application contract providing that capability
* the adapter mapping between consumer and provider

Integration implementation must not begin based on assumptions, architectural intention, future plans, stub adapters, or likely consumers.

The integration boundary must be proven before authorization.

---

# Core Rule

No Integration Implementation Authorization may be issued unless the following chain is explicitly verified:

```text
Consumer
    ↓
Required Capability
    ↓
Accepted Application Contract
    ↓
Thin Adapter Mapping
```

Every link must be:

* identified
* evidence-backed
* compatible with accepted architecture
* free from invented business behavior

If any link is missing, ambiguous, or requires a new Application capability, integration authorization is blocked.

---

# Integration Readiness Questions

Before requesting implementation authorization, the governance artifact must answer the following questions.

---

## 1. Who is the Consumer?

The consumer must be an explicitly identified module or internal application component.

Valid examples:

* Request Module
* Allocation Module
* CheckIn Module
* Reporting Module

Invalid examples:

* Other modules
* Backend consumers
* Future integration
* System integration

The consumer cannot be selected only because it appears architecturally possible.

It must have an evidenced business or technical dependency.

---

## 2. What Exact Capability Does the Consumer Require?

The required capability must be expressed as a precise behavior.

Valid examples:

```text
siteExists(id): bool
```

```text
getDormitoryDetail(id): ?DormitoryDetailData
```

```text
isBedAssignable(id): bool
```

Invalid descriptions:

```text
Needs dormitory data
```

```text
Needs integration
```

```text
Needs access to another module
```

The capability must describe the exact question the consumer needs answered.

---

## 3. Is the Capability Already Supported by an Accepted Application Contract?

The provider capability must exist in an accepted Application contract.

The mapping may be:

* direct
* semantically equivalent

but it must be explicitly documented.

Example:

Consumer requirement:

```text
Request\Application\Contracts\DormitoryReadContract::siteExists(id): bool
```

Accepted provider:

```text
DormitoryStructureReadContract::getDormitoryDetail(id): ?DormitoryDetailData
```

Approved mapping:

```text
detail exists  -> true
detail is null -> false
```

This mapping is allowed because it translates existing Application behavior.

It does not:

* add a new Application method
* modify an accepted contract
* create new Domain behavior

---

## 4. Does the Adapter Only Translate Existing Behavior?

The adapter must remain thin.

Allowed:

* calling accepted Application contracts
* translating return values
* mapping null/non-null semantics
* adapting consumer/provider naming differences
* applying existing integration conventions

Forbidden:

* creating business rules
* calculating eligibility
* deciding workflow ownership
* interpreting occupancy lifecycle
* adding authorization rules
* adding capacity logic
* adding allocation rules
* accessing persistence directly
* bypassing Application contracts

---

# Authorization Outcomes

## Ready

When all required questions are answered:

```text
READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION
```

Meaning:

* consumer identified
* capability identified
* accepted provider contract exists
* adapter mapping is thin
* no new Application behavior required

---

## Blocked

When any requirement is missing:

```text
INTEGRATION_AUTHORIZATION_BLOCKED
```

Blocking reason must identify the missing element:

* missing consumer
* missing capability definition
* missing accepted Application contract
* contract mismatch
* adapter would introduce business behavior
* scope exceeds integration boundary

---

# Mandatory Authorization Artifact Section

Every future Integration Authorization artifact must include:

```text
## Integration Readiness Gate

Consumer:

Required Capability:

Accepted Provider Contract:

Mapping:

Adapter Type:

Behavior Invented:

Authorization Result:
```

Example:

```text
Consumer:
Request Module

Required Capability:
Dormitory existence validation

Accepted Provider Contract:
DormitoryStructureReadContract::getDormitoryDetail(id)

Mapping:
non-null detail = exists
null detail = missing

Adapter Type:
Thin integration adapter

Behavior Invented:
None

Authorization Result:
READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION
```

---

# Mandatory Stop Conditions

Integration implementation must stop if:

* consumer is unknown
* capability is described generally instead of behaviorally
* no accepted Application contract exists
* implementation requires adding a new Application method
* implementation requires changing an accepted contract
* adapter contains business decisions
* workflow ownership is introduced without approval
* allocation ownership is introduced without approval
* occupancy semantics are expanded beyond approved boundaries
* persistence is accessed directly
* integration scope expands into another feature area

---

# Relationship To Specifications

This gate applies to all specifications containing cross-module integration.

Mandatory before:

* Integration Implementation Authorization
* cross-module adapter creation
* replacing Null/Stub adapters with live implementations
* provider-consumer Application bindings

This pattern does not authorize implementation.

It only determines whether implementation authorization may be requested.

---

# Governance Lesson Captured

This pattern was created after Spec04 Phase 4 demonstrated that the main integration risk was not coding complexity.

The actual risk was authorizing implementation before proving:

```text
Consumer
→ Capability
→ Accepted Contract
→ Thin Adapter
```

Future integration phases must resolve this chain before implementation authorization.

---

# Scope Protection

This pattern does not allow:

* reopening completed specifications
* changing locked Application contracts
* redesigning modules through integration work
* using integration as a method to bypass governance gates

Any required Application capability change must go through its own approved change process.
