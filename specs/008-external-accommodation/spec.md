# Feature Specification: External Accommodation (spec08)

**Feature Branch**: `008-external-accommodation`

**Created**: 2026-07-01

**Status**: **Draft — Planning specification** (planning authorized; implementation **not** authorized)

**Catalog**: spec08 — External Accommodation (`spec-catalog.md` v1.0.11)

**Depends on**: spec01 Foundation; spec05 Request Management; spec06 Lottery Selection

**Optional reference**: spec04 Accommodation Resource (external dormitory catalog classification); spec07 Allocation & Occupancy (**closed** — may supply trigger facts only; no reopening)

**Input**: Establish the **Voucher** bounded context: voucher eligibility evaluation and issuance lifecycle for **external dormitory** accommodation outcomes — when internal physical assignment is not applicable — per **CD-016** and **context-map.md** **R8** (Lottery / Allocation → Voucher).

**Normative boundaries**: [`../../.specify/docs/catalog-decisions.md`](../../.specify/docs/catalog-decisions.md) **CD-016**; [`../../.specify/docs/context-map.md`](../../.specify/docs/context-map.md) Voucher row, **R8**; constitution **BR-09**, **BR-10**, **BR-12**, **BR-13**.

**Governance**: Planning authorized per nomination and authorization pathway; **not** Design Approval; **not** Implementation Authorization. spec07 remains **CLOSED**.

---

## Purpose

When accommodation demand is satisfied through **external dormitories** (catalog-only sites with no physical bed inventory), the system must issue and govern **vouchers** as the authoritative accommodation outcome. Voucher owns eligibility evaluation and the full issuance lifecycle. Upstream contexts (Lottery, Allocation) may supply **triggering facts** only — they do not own voucher policy or issuance decisions.

This specification defines problem scope, users, constraints, functional requirements, and acceptance-oriented outcomes suitable for subsequent design and planning phases. It does **not** authorize implementation.

---

## Scope

| In scope | Out of scope (see § Out of Scope) |
| -------- | ----------------------------------- |
| **Voucher** bounded context — eligibility evaluation and issuance lifecycle | Internal dormitory room/bed assignment (spec07 / Allocation) |
| External dormitory accommodation outcomes (voucher codes/references) | Check-in / check-out operational transitions (spec07 / CheckIn) |
| Consumption of upstream **trigger facts** from Lottery and Allocation (R8) | Physical bed inventory, occupancy markers (spec04 / Dormitory) |
| Voucher lifecycle and evaluation outcomes, issuance rules, and audit-relevant transitions | Lottery scoring, draw execution, program lifecycle (spec06) |
| Read access for downstream consumers (Reporting, employee inquiry) | Reporting projection implementation (spec11) |
| Reserve promotion voucher path for external dormitories (BR-10) | Notification delivery implementation (spec09) |
| Idempotent handling of duplicate upstream triggers (BR-14) | Workflow engine |
| Open planning item **UD-03** — upstream trigger fact bundle shape (documented, not resolved here) | Reopening or modifying spec07 closed program |

**Program note:** spec08 is the sole owner of voucher eligibility and issuance. External dormitory winners interact with the system through vouchers only (BR-13); no physical allocation or occupancy tracking applies (BR-12).

---

## Governing Decisions

### CD-016 — Voucher Eligibility Ownership

| Outcome | spec08 implication |
| ------- | ------------------ |
| Voucher owns eligibility evaluation within domain rules | All eligibility logic lives in Voucher; upstream supplies facts, not policy |
| Voucher owns issuance lifecycle | State transitions, code generation rules, expiration, and archival are Voucher responsibilities |
| Lottery may trigger downstream action | Lottery supplies draw outcomes / external-winner facts; does not decide voucher issuance |
| Allocation may trigger downstream action | Allocation may supply trigger facts; does not decide voucher issuance |

### R8 — Lottery / Allocation → Voucher

| Field | Value |
| ----- | ----- |
| Direction | Upstream triggers initiate voucher **evaluation** |
| Integration | Upstream contexts supply facts only; Voucher retains decision authority; no reverse ownership of upstream lifecycle |
| Producer(s) | Lottery (spec06), Allocation (spec07) — **facts only** |
| Consumer | Voucher (spec08) — **decision authority** |
| Open item | **UD-03** — exact upstream trigger fact bundle shape not yet specified |

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Voucher Eligibility Evaluation (Priority: P1)

As the system responsible for external accommodation vouchers, I need to evaluate eligibility when upstream contexts supply trigger facts so that only valid external-accommodation outcomes proceed to issuance.

**Why this priority**: Eligibility is the gate for all voucher flows; CD-016 assigns this authority exclusively to Voucher.

**Independent Test**: Supply trigger facts representing an external-dormitory lottery winner and confirm eligibility evaluation completes with an explicit outcome (`Eligible`, `Ineligible`, or `Deferred`) — without upstream contexts applying voucher policy.

**Acceptance Scenarios**:

1. **Given** valid trigger facts from an external-dormitory lottery outcome, **When** eligibility is evaluated, **Then** outcome is `Eligible` and evaluation rationale is recorded
2. **Given** trigger facts referencing a non-external dormitory or missing required references, **When** evaluation runs, **Then** outcome is `Ineligible` with stable reason codes
3. **Given** trigger facts with duplicate correlation identifier (same upstream trigger retried), **When** evaluation runs, **Then** processing is idempotent — no duplicate issuance (BR-14)
4. **Given** trigger facts indicating unfulfilled internal accommodation for an approved request, **When** evaluation runs, **Then** Voucher applies its own eligibility rules — upstream facts do not bypass evaluation
5. **Given** any evaluation, **When** inspected, **Then** employee, dormitory, and upstream correlation references are recorded as immutable identifiers without cross-context ownership

---

### User Story 2 - Voucher Issuance Lifecycle (Priority: P1)

As an employee who won external dormitory accommodation, I need a unique voucher code issued and tracked through its lifecycle so that I have an authoritative external accommodation credential.

**Why this priority**: BR-09 requires voucher codes to be generated and archived for external dormitories; this is the core deliverable of spec08.

**Independent Test**: Progress a record with `Eligible` outcome through issuance and confirm a unique voucher code, issuance timestamp, and lifecycle state — without internal physical assignment or check-in/out involvement.

**Acceptance Scenarios**:

1. **Given** an `Eligible` evaluation for an external dormitory, **When** issuance is initiated, **Then** a unique voucher code is generated and the voucher enters `Issued` state
2. **Given** an issued voucher, **When** lifecycle is inspected, **Then** voucher references employee, external dormitory site, effective stay period, and upstream source (lottery outcome or allocation-related trigger)
3. **Given** voucher code generation, **When** collision is detected, **Then** regeneration occurs until uniqueness is confirmed (constitution voucher collision rule)
4. **Given** an issued voucher past its configured validity window, **When** expiration policy applies, **Then** voucher transitions to `Expired` with audit record
5. **Given** a terminal voucher state (`Expired`, `Cancelled`, `Superseded`), **When** re-issuance is attempted without a new `Eligible` evaluation, **Then** operation is rejected

---

### User Story 3 - External Lottery Winner Path (Priority: P1)

As the system, I need lottery external-dormitory winners to receive vouchers automatically after draw completion so that BR-09 external auto-accommodation is satisfied without physical bed assignment.

**Why this priority**: Constitution BR-09 explicitly pairs external dormitories with voucher generation; Lottery defers this path to spec08 (OA-06-05).

**Independent Test**: Supply external-winner trigger facts after lottery draw completion for an external dormitory program and confirm voucher evaluation and issuance — without physical bed assignment.

**Acceptance Scenarios**:

1. **Given** a completed lottery draw for an **external** dormitory program, **When** winner trigger facts are received, **Then** Voucher evaluates and issues vouchers for winners up to program capacity
2. **Given** external lottery results, **When** processing completes, **Then** no room/bed identifiers are required or stored (BR-12)
3. **Given** lottery results for an **internal** dormitory program, **When** trigger facts are received, **Then** Voucher rejects or ignores them — internal path is Allocation-owned (spec07)
4. **Given** draw completion facts, **When** Voucher processes winners, **Then** Lottery supplies facts only and performs no issuance logic (CD-016)

---

### User Story 4 - Reserve Promotion Voucher (Priority: P2)

As the system, I need the next reserve on an external dormitory lottery to receive a voucher when a winner declines or becomes ineligible so that BR-10 reserve promotion is honored for external programs.

**Why this priority**: BR-10 distinguishes internal (automatic allocation) from external (voucher generation) promotion paths.

**Independent Test**: Issue voucher to winner, record winner decline or ineligibility, supply reserve promotion trigger facts, and confirm reserve receives new voucher evaluation and issuance.

**Acceptance Scenarios**:

1. **Given** an issued voucher for external lottery winner who declines, **When** reserve promotion trigger facts arrive, **Then** next reserve is evaluated and may receive a new voucher
2. **Given** reserve promotion, **When** original winner's voucher is still active, **Then** original voucher is `Cancelled` or `Superseded` per domain rules before reserve issuance
3. **Given** no remaining eligible reserves, **When** promotion trigger received, **Then** promotion completes with explicit no-issuance outcome and audit record

---

### User Story 5 - Voucher Read Access (Priority: P2)

As an employee or authorized operator, I need to view issued voucher status so that external accommodation credentials can be verified without changing lifecycle state.

**Why this priority**: Downstream consumers (Reporting spec11, operators) require read-only access; employees need visibility of their own vouchers.

**Independent Test**: Issue a voucher, then view by employee or voucher code, and confirm read-only response with lifecycle state and metadata — no lifecycle change.

**Acceptance Scenarios**:

1. **Given** an employee with an issued voucher, **When** the employee views their vouchers, **Then** active and historical vouchers are returned with code, dormitory reference, validity period, and state
2. **Given** an authorized operator, **When** searching by voucher code, **Then** voucher details are returned for verification purposes
3. **Given** any read access request, **When** fulfilled, **Then** no lifecycle state mutation occurs
4. **Given** Reporting consumer (spec11), **When** reading voucher outcomes, **Then** consumer has no write authority (CD-017)

---

### Edge Cases

- Duplicate upstream trigger (same lottery result delivered twice)? Idempotent — single issuance (BR-14)
- External dormitory deactivated after trigger but before issuance? Evaluation returns `Ineligible` with reason
- Employee already holds active voucher for overlapping stay period? Reject or supersede per BR-02 one-allocation-per-person interpretation for external path — see OA-08-01
- Trigger facts missing dormitory reference or referencing internal dormitory? Reject at evaluation
- Allocation supplies trigger facts for successful internal assignment? Ignored — not a voucher path
- Concurrent issuance for same employee from lottery and allocation-related triggers? Single outcome — second trigger resolves via idempotency / conflict rules
- Voucher expiration before employee uses credential? Transition to `Expired`; re-issue requires new `Eligible` evaluation
- Direct access to upstream operational records? Forbidden — Voucher consumes trigger facts only; upstream contexts retain ownership of their data

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST treat **Voucher** as the authoritative record of external accommodation credentials within the Voucher bounded context (CD-016)
- **FR-002**: System MUST own **voucher eligibility evaluation** — upstream contexts MUST NOT implement eligibility policy (CD-016)
- **FR-003**: System MUST own the **voucher issuance lifecycle** including transitions from evaluation through terminal states
- **FR-004**: System MUST issue voucher codes for **external dormitory** accommodation outcomes per BR-09 and BR-12
- **FR-005**: System MUST **reject or ignore** voucher triggers intended for internal dormitory physical assignment paths (BR-13)
- **FR-006**: System MUST accept upstream trigger facts from **Lottery** (external draw outcomes) and **Allocation** (unfulfilled-accommodation triggers) only through defined cross-boundary integration — Voucher MUST NOT assume ownership of upstream operational data (R8)
- **FR-007**: System MUST record employee, dormitory, request, and upstream correlation references as **immutable identifiers** without cross-context ownership of upstream records
- **FR-008**: System MUST generate voucher codes with **cryptographic strength** and enforce **global uniqueness** (constitution voucher collision rule)
- **FR-009**: System MUST support **expiration tracking** for issued vouchers per configured validity policy
- **FR-010**: System MUST process **reserve promotion** triggers for external dormitories per BR-10
- **FR-011**: System MUST enforce **idempotent** processing for duplicate upstream triggers (BR-14)
- **FR-012**: System MUST record material voucher transitions in a form consumable by Audit and Notification downstream contexts
- **FR-013**: System MUST record audit-relevant voucher lifecycle actions (constitution AP-06)
- **FR-014**: System MUST provide **read-only** voucher inquiry for authorized employees and downstream consumers — no consumer may mutate voucher lifecycle (CD-016, CD-017)
- **FR-015**: System MUST archive issued voucher records for historical inquiry — vouchers are not silently deleted
- **FR-016**: System MUST confirm target dormitory is classified as **external** before issuance (BR-12)

### Key Entities

- **Voucher** — external accommodation credential; unique code; lifecycle state; employee and external dormitory references; effective stay period; upstream source correlation
- **VoucherEligibilityOutcome** — evaluation result (`Eligible`, `Ineligible`, `Deferred`) with reason codes and evaluated-at timestamp
- **VoucherIssuanceTrigger** — inbound fact bundle from upstream (lottery outcome, unfulfilled accommodation, reserve promotion) — correlation identifier for idempotency
- **VoucherValidityPeriod** — effective from / until dates governing credential validity

### Voucher States and Evaluation Outcomes (specification vocabulary)

**Evaluation outcomes** (result of eligibility evaluation; not issuance states):

| Outcome | Meaning |
| ------- | ------- |
| `Eligible` | Passed Voucher eligibility rules; may proceed to issuance |
| `Ineligible` | Failed eligibility; does not proceed to issuance |
| `Deferred` | Evaluation cannot complete; may be retried when additional facts are available |

**Lifecycle states** (issued voucher record):

| State | Meaning |
| ----- | ------- |
| `PendingEvaluation` | Trigger received; eligibility not yet determined |
| `Issued` | Unique code generated; active credential |
| `Expired` | Validity window ended |
| `Cancelled` | Voided before use (e.g., winner decline, administrative cancel) |
| `Superseded` | Replaced by reserve promotion or corrected re-issuance |

*Transition rules between outcomes and lifecycle states are not fixed at specification level.*

---

## Success Criteria *(mandatory)*

- **SC-001**: External lottery winner trigger facts result in an issued voucher within 5 seconds for 95% of cases under normal operating conditions
- **SC-002**: 100% of duplicate upstream triggers for the same correlation identifier produce at most one issued voucher
- **SC-003**: 100% of issued voucher codes are globally unique across the system
- **SC-004**: Employee can view their active voucher status in a single inquiry without lifecycle mutation
- **SC-005**: 100% of voucher processing relies on upstream trigger facts only — Voucher does not depend on upstream operational data ownership
- **SC-006**: Reserve promotion for external dormitory completes with correct voucher supersession within 10 seconds for 95% of cases under normal operating conditions

---

## Assumptions

- **OA-08-01**: BR-02 one-allocation-per-person applies to external vouchers — an employee may hold at most one **active** voucher per overlapping stay period; supersession rules apply on promotion
- **OA-08-02**: External dormitory classification is obtained from the accommodation resource catalog (spec04) when available
- **OA-08-03**: Lottery external-winner trigger facts are supplied after draw completion; Lottery does not apply voucher policy (CD-016)
- **OA-08-04**: Allocation supplies trigger facts only; exact fact bundle shape remains open (UD-03)
- **OA-08-05**: Employee and operator presentation interfaces are deferred to a follow-on phase
- **OA-08-06**: Voucher validity period defaults to request/lottery stay dates when present in trigger facts; override policy not specified here
- **OA-08-07**: Unfulfilled approved accommodation requests may supply trigger facts when organizational policy routes demand to external accommodation — eligibility remains owned by Voucher

### Open Planning Items

| ID | Item | Status |
| -- | ---- | ------ |
| **UD-03** | Upstream trigger fact bundle shape (lottery winner facts, allocation-related triggers) | **Open** — not resolved at specification level |
| **UD-08** | Voucher expiration and renewal policy detail | **Open** — BR-12 requires tracking; exact policy not specified here |

---

## Dependencies

| Dependency | Relationship | Notes |
| ---------- | ------------ | ----- |
| **spec01** Foundation | Required | Core platform conventions |
| **spec05** Request Management | Upstream supplier | Approved request context may appear in trigger facts; Request lifecycle not owned by Voucher |
| **spec06** Lottery Selection | Upstream supplier | External draw outcomes trigger voucher evaluation; Lottery scoring/draw not in spec08 |
| **spec04** Accommodation Resource | Optional read | External dormitory catalog classification |
| **spec07** Allocation & Occupancy | Upstream producer (**closed**) | Trigger facts only; no reopening |
| **spec09** Notification | Downstream consumer | Transition notifications only; delivery not in spec08 |
| **spec10** Audit | Downstream consumer | Audit entries on transitions |
| **spec11** Reporting | Downstream consumer | Read-only consumption (CD-017) |

---

## Out of Scope (spec08)

- Internal dormitory room/bed assignment and overlap rules (**Allocation** — spec07; **CLOSED**)
- Check-in / check-out operational transitions (**CheckIn/CheckOut** — spec07; **CLOSED**)
- Physical bed inventory, buildings, rooms, occupancy markers (**Dormitory** — spec04)
- Lottery program lifecycle, scoring, draw execution (**Lottery** — spec06)
- Request submission, approval workflow, approval history (**Request** — spec05)
- Notification message delivery (**Notification** — spec09)
- Audit log storage implementation (**Audit** — spec10)
- Reporting projections and dashboards (**Reporting** — spec11)
- Workflow engine activation (**deferred**)
- Employee or operator presentation interfaces (follow-on)
- Third-party external dormitory operator integrations beyond voucher code issuance
- Payment, billing, or financial settlement for external stays
- Reopening or modifying the closed spec07 program

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `spec-catalog.md` spec08 | External Accommodation; Nominated for Authorization |
| `handoff/spec08-nomination-record.md` | Next-spec nomination; execution NOT AUTHORIZED |
| CD-016 | Voucher owns eligibility and issuance lifecycle |
| CD-017 | Reporting read-only; no write authority from consumers |
| `context-map.md` R8 | Lottery / Allocation → Voucher upstream triggers |
| `contract-stub-pack-spec07-spec11.md` | Informational cross-reference for integration identifiers (not specification requirements) |
| Constitution BR-09, BR-10, BR-12, BR-13, BR-14 | External voucher paths, reserve promotion, scope boundaries, idempotency |
| spec07 UD-03 | Upstream trigger fact bundle — carried forward as open |

**Planning authority:** Specification-definition only. Design Approval, architecture freeze, `plan.md`, `tasks.md`, and Implementation Authorization require separate governance records.
