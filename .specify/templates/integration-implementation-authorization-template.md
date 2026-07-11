# Integration Implementation Authorization Template

Use this template when preparing a **cross-module Integration Implementation Authorization** handoff instance (for example `.specify/docs/handoff/<spec>-integration-implementation-authorization.md`).

This template does **not** authorize implementation by itself. Fill it only after applying the Integration Readiness Gate.

## Canonical pattern (mandatory)

Before issuing Integration Implementation Authorization, apply:

`.specify/governance/patterns/integration-readiness-gate.md`

Authorization must be blocked unless the following chain is proven:

```text
Consumer -> Required Capability -> Accepted Application Contract -> Thin Adapter Mapping
```

The gate itself does **not** authorize implementation. It only determines whether implementation authorization may be requested or issued.

Allowed readiness outcomes (only):

- `READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION`
- `INTEGRATION_AUTHORIZATION_BLOCKED`

Apply this gate **before**:

- Integration Implementation Authorization
- cross-module adapter creation
- replacing Null/Stub adapters with live implementations
- provider-consumer Application bindings

Do not paste the full pattern into this artifact. Reference it and complete the required section below.

---

## Header (fill per instance)

| Field | Value |
| ----- | ----- |
| **STATUS** | `PREPARED` \| `AUTHORIZED_FOR_INTEGRATION_IMPLEMENTATION` \| `BLOCKED` |
| **Spec** | `<spec-id>` |
| **Phase** | Integration Implementation |
| **Authorization scope** | `<consumer> → <provider> — <capability only>` |
| **Self-executing?** | **No** |
| **Decision date** | `<YYYY-MM-DD>` |

---

## Integration Readiness Gate

Consumer:

Required Capability:

Accepted Provider Contract:

Mapping:

Adapter Type:

Behavior Invented:

Authorization Result:

<!-- Authorization Result must be exactly one of:
     READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION
     INTEGRATION_AUTHORIZATION_BLOCKED
-->

---

## Authorization Decision

Record only if Authorization Result is `READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION`.

| Field | Value |
| ----- | ----- |
| **Authorization status** | `AUTHORIZED_FOR_INTEGRATION_IMPLEMENTATION` |
| **Authorized consumer** | |
| **Authorized mapping** | |
| **Authorization scope** | |

If Authorization Result is `INTEGRATION_AUTHORIZATION_BLOCKED`, do **not** issue implementation authorization. Record the blocking reason and required next gate instead.

---

## Explicitly Forbidden Scope (baseline)

Do not authorize via this artifact:

- Domain / Persistence redesign
- New Application capability invention without a separate accepted Application gate
- Allocation / CheckIn / Workflow ownership through “integration”
- Auth / HTTP / UI / migrations unless separately authorized
- Events / external adapters unless separately approved
- Broad refactors unrelated to the proven thin adapter mapping

---

## References

- Canonical pattern: `.specify/governance/patterns/integration-readiness-gate.md`
- Pattern index: `.specify/governance/patterns/README.md`
- Authority map: `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`
