# Spec04 Phase 4 Integration Implementation Authorization

## 1. Decision Status

| Field | Value |
| ----- | ----- |
| **STATUS** | `AUTHORIZED_FOR_PHASE_4_IMPLEMENTATION` |
| **Spec** | `004-accommodation-resource` |
| **Phase** | Spec04 Backend Implementation Phase 4 – Integration Implementation |
| **Authorization scope** | Request → Dormitory existence validation only |
| **Self-executing?** | **No** — implementation may begin only after this artifact is reviewed and accepted by the operator |
| **Decision date** | 2026-07-11 |

This artifact authorizes **thin Phase 4 integration implementation** for the Request Module consumer only. It does **not** implement code, reopen Phases 1–3C, authorize Allocation/CheckIn wiring, or claim Spec04 Backend Closure.

---

## 2. Purpose

Issue Phase 4 implementation authorization for a single, evidence-backed integration slice:

**Request Module → Dormitory Module** dormitory existence validation via an integration adapter mapping only.

Authorized work must follow the accepted Phase 4:

- Integration Contract
- Implementation Lock
- Execution Prompt

without expanding their scope.

---

## 3. Preconditions Verification

| Precondition | Evidence | Satisfied? |
| ------------ | -------- | ---------- |
| Phase 1 Domain accepted | `spec04-domain-layer-implementation-review.md` | Yes |
| Phase 2 Persistence accepted | `spec04-persistence-implementation-review.md` | Yes |
| Phase 3A Application Read Core accepted | `spec04-application-read-layer-review.md` | Yes |
| Phase 3B Application Read Remaining accepted | `spec04-application-read-layer-remaining-review.md` | Yes |
| Phase 3C Application Mutation accepted | `spec04-application-mutation-layer-implementation-review.md` | Yes |
| Phase 4 Governance Package created/reviewed | Contract, Lock, Execution Prompt + `spec04-integration-implementation-governance-review.md` (`ACCEPTED_WITH_NOTES`) | Yes |
| OQ-4-001 resolved | `spec04-integration-implementation-oq-resolution.md` → `RESOLVED_FOR_PHASE_4` (Request Module) | Yes |
| OQ-4-003 resolved | Same OQ artifact → `RESOLVED_FOR_PHASE_4` (Request ↔ `getDormitoryDetail` mapping) | Yes |
| Readiness | Same OQ artifact → `READY_FOR_IMPLEMENTATION_AUTHORIZATION_REVIEW` | Yes |

**Verdict:** All required preconditions for Phase 4 implementation authorization are satisfied.

---

## 4. Authorization Decision

**Authorization status:** `AUTHORIZED_FOR_PHASE_4_IMPLEMENTATION`

Authorization is **strictly limited** to thin integration implementation for:

**Request Module → Dormitory Module**  
for **dormitory existence validation only**.

| Field | Value |
| ----- | ----- |
| Authorized consumer | Request Module |
| Authorized mapping | `Request.siteExists(id)` → `Dormitory.getDormitoryDetail(id) !== null` |
| Authorization scope | Request-to-Dormitory existence validation only |

---

## 5. Authorized Scope

When the operator accepts this artifact, implementation may include **only**:

1. Create or update the integration adapter required for Request’s `DormitoryReadContract`.
2. Map:

   `Request\Application\Contracts\DormitoryReadContract::siteExists(string $dormitorySiteId): bool`

   to

   `DormitoryStructureReadContract::getDormitoryDetail(string $dormitoryId): ?DormitoryDetailData`

3. Mapping rule:
   - `getDormitoryDetail(id)` returns `DormitoryDetailData` → `siteExists(id)` returns `true`
   - `getDormitoryDetail(id)` returns `null` → `siteExists(id)` returns `false`
4. Wire Request’s contract to this adapter only if required by the existing project integration pattern (replace Null binding where that is the established pattern).
5. Add narrowly scoped tests only for this integration mapping if required by the project test pattern.

---

## 6. Governing Artifacts (must not expand)

Authorized implementation **must** follow without scope expansion:

- [`spec04-integration-implementation-contract.md`](spec04-integration-implementation-contract.md)
- [`spec04-integration-implementation-lock.md`](spec04-integration-implementation-lock.md)
- [`spec04-integration-implementation-execution-prompt.md`](spec04-integration-implementation-execution-prompt.md)
- [`spec04-integration-implementation-oq-resolution.md`](spec04-integration-implementation-oq-resolution.md)

---

## 7. Explicitly Forbidden Scope

Authorized implementers must **not**:

- Change Dormitory Domain behavior
- Change Dormitory Persistence behavior
- Change Dormitory Application Read contracts
- Change Dormitory Application Mutation contracts
- Add a new Dormitory existence Application method
- Reopen Phase 3A, 3B, or 3C
- Implement Allocation integration
- Implement CheckIn integration
- Introduce workflow ownership
- Add hierarchy / capacity / bed / status semantics to Request `siteExists`
- Use `listDormitories` as the existence mechanism
- Use hierarchy list methods as the existence mechanism
- Add broad architectural refactors
- Add unrelated tests or modify unrelated modules beyond the Request binding required for this adapter
- Add authorization, HTTP/API/routes/controllers, UI, migrations/schema, events/jobs/listeners, or external adapters

---

## 8. Implementation Constraints

- Implementation must be an **integration-level adapter only**.
- The adapter must **not** invent new Application behavior.
- The adapter must **not** change accepted contracts.
- The adapter must preserve Request’s boolean port shape (`siteExists(...): bool`).
- The adapter must preserve Dormitory’s accepted null-vs-detail read semantics.
- Missing dormitory must map to `false`.
- Existing dormitory detail must map to `true`.
- Any exception behavior must follow existing project conventions; do not introduce new exception policy unless already required by existing patterns.
- Occupancy remains Dormitory state recording only (unchanged; out of this slice).
- Synchronous service-call only (OQ-4-002).

---

## 9. Evidence

| Item | Reference / value |
| ---- | ----------------- |
| OQ resolution artifact | [`spec04-integration-implementation-oq-resolution.md`](spec04-integration-implementation-oq-resolution.md) |
| OQ-4-001 | `RESOLVED_FOR_PHASE_4` |
| OQ-4-003 | `RESOLVED_FOR_PHASE_4` |
| Selected consumer | Request Module |
| Capability mapping | `siteExists` → `getDormitoryDetail != null` |
| Capability-fit basis | Accepted Application Read null-vs-detail semantics; no Application contract change required |
| Business reason (OQ-4-001) | Request is the earliest business entry point for accommodation requests |

---

## 10. Final Authorization Record

| Field | Value |
| ----- | ----- |
| **Authorization status** | `AUTHORIZED_FOR_PHASE_4_IMPLEMENTATION` |
| **Authorized consumer** | Request Module |
| **Authorized mapping** | `Request.siteExists(id)` → `Dormitory.getDormitoryDetail(id) !== null` |
| **Authorization scope** | Request-to-Dormitory existence validation only |
| **Unmet preconditions** | None |

**Implementation may begin only after this artifact is reviewed and accepted by the operator.**

---

## 11. Stop Boundary

This artifact:

- Does **not** perform implementation
- Does **not** authorize Allocation, CheckIn, Workflow, Authorization, UI, or API work
- Does **not** reopen Domain / Persistence / Application Read / Application Mutation layers
- Does **not** claim Spec04 Backend Closure

---

## References

- [`spec04-integration-implementation-oq-resolution.md`](spec04-integration-implementation-oq-resolution.md)
- [`spec04-integration-implementation-contract.md`](spec04-integration-implementation-contract.md)
- [`spec04-integration-implementation-lock.md`](spec04-integration-implementation-lock.md)
- [`spec04-integration-implementation-execution-prompt.md`](spec04-integration-implementation-execution-prompt.md)
- [`spec04-integration-implementation-governance-review.md`](spec04-integration-implementation-governance-review.md)
- [`spec04-application-read-layer-review.md`](spec04-application-read-layer-review.md)
- [`spec04-application-read-layer-remaining-review.md`](spec04-application-read-layer-remaining-review.md)
- [`spec04-application-mutation-layer-implementation-review.md`](spec04-application-mutation-layer-implementation-review.md)
