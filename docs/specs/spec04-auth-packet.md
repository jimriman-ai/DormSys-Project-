# Spec04 Auth Packet — employee-request-self-service

| Field | Value |
|-------|-------|
| **Status** | **GOVERNANCE-ACCEPTED** — OQ-AUTH-05 **Option A**; design artifact accepted; **implementation NOT authorized** |
| **Permit** | PERMIT-F3A-SPEC04 (documentation artifact only — **not** an L5/L6 permit) |
| **Created** | 1405/04/27 \| 2026-07-18 |
| **Revised** | 1405/04/27 \| 2026-07-18 — OQ-AUTH-05 **DECIDED A** (governance acceptance) |
| **Canonical path** | `docs/specs/spec04-auth-packet.md` |
| **Business Owner (DGAP-08)** | HR Business Owner / Human Resources Department |
| **BO scope** | Business requirements, product-surface scope, accountable business authority |
| **Excluded from BO** | Technical implementation, architecture, permission enforcement, code approval |
| **Lifecycle posture** | Governance-accepted design only — **L5 / L6 not authorized**; next gate = separate implementation authorization |

---

## 0. Explicit non-authorization

This artifact does **not**:

- resolve, close, or unpark unrelated gaps (DGAP-03 / SGAP-05 remain as in `open-decisions.md`; Spec04-specific gates DGAP-05/06 and OQ-AUTH-01…03/05 are DECIDED)
- authorize Spec04 / Request self-service implementation, migrations, routes, seeders, middleware registration, or Livewire
- authorize L5 Auth gate execution or L6 implementation
- reopen settled DGAP-05 / DGAP-06 decisions

**Reminder:** Governance acceptance (OQ-AUTH-05 A) ≠ implementation authorization. **NO CODE, MIGRATION, ROUTE, OR UI IMPLEMENTATION IS PERMITTED** until a separate implementation authorization process (after required contracts and execution approvals).

---

## 1. Surface / Scope

**Product surface:** `employee-request-self-service`

**In scope for this packet (analysis / design decisions only):**

- Identity-guard access for an employee principal to create/view own accommodation requests
- Auth boundary alignment with Dual-Principal model (`identity` vs `web`)
- Stage-1 approver ownership, visibility, UI boundary (DGAP-05/06) and technical selections (OQ-AUTH-01…03)

**Out of scope:**

- Spec06 Lottery authority (SGAP-05 remaining gate)
- Spec04 physical dormitory Admin UI / structure PEP beyond self-service
- Password broker / credential topology changes
- Cross-module FK work
- Approver-console **implementation** (U2 is a boundary decision — separate permit required)

---

## 2. Governance state (verified 2026-07-18)

Source: `docs/governance/open-decisions.md`

| ID | Status | Notes (verified) |
|----|--------|------------------|
| **DGAP-08** | **RESOLVED** | BO = HR Business Owner / Human Resources Department |
| **DGAP-03** | **OPEN / PARKED** | Structural Department↔Dormitory link still parked |
| **DGAP-05** | **DECIDED** | **A** — Department line manager = Stage-1 approver |
| **DGAP-06** | **DECIDED** | **V1** + **U2** |
| **OQ-AUTH-01** | **DECIDED** | **B** — `employee` + `dormitory-manager` |
| **OQ-AUTH-02** | **DECIDED** | **B** — dual role stacks + middleware V1 |
| **OQ-AUTH-03** | **DECIDED** | **B** — snapshot Stage-1 identity at submit |
| **OQ-AUTH-05** | **DECIDED** | **A** — Spec04 Auth Packet accepted as governance-ready; impl unauthorized |
| **SGAP-05** | **PARKED** | Spec06 GOVERNANCE_OPEN remaining — does **not** block Spec04 acceptance |

---

## 3. Auth Boundary Analysis

### 3.1 Principal type + Role

| Option | Principal | Role / gate | Pros | Cons |
|--------|-----------|-------------|------|------|
| **A** | `identity` Authenticatable (`identity_users`) | Spatie role on `guard_name = identity` | Matches Dual-Principal; `IdentityRoleGuard` | Requires role catalog |
| **B** | `web` / `App\Models\User` | Default guard roles | Familiar Laravel default | Conflicts with Dual-Principal / SEC-G-01 |

**Selected path:** Principal = **`identity`**. Canonical role **names** per **OQ-AUTH-01 B**: `employee` (self-service) + `dormitory-manager` (Stage-1 / U2 console).

### 3.2 Where the gate applies in UI

**Selected — OQ-AUTH-02 B:**

- Dual route stacks: `auth:identity` + `identity.role:employee` (self-service); `auth:identity` + `identity.role:dormitory-manager` (approver console)
- Thicker middleware: on request-scoped routes, abort unless principal is **subject** OR **assigned Stage-1 approver** (V1)
- Livewire / Application may re-assert via `IdentityRoleGuard` (defense-in-depth)
- Approve/reject routes **only** under `dormitory-manager` console stack (U2)

### 3.3 Fit with IdentityRoleGuard + Dual-Principal

`IdentityRoleGuard` is a **service class**, not middleware. Middleware (e.g. `EnsureIdentityRole`) **consumes** `IdentityRoleGuard::userHasIdentityRole(...)`.

| Method | Signature | Behavior |
|--------|-----------|----------|
| `userHasIdentityRole` | `(Authenticatable $user, string ...$roles): bool` | Spatie roles with `guard_name = 'identity'` |
| `assertIdentityRole` | `(string $role): void` | `auth('identity')->user()`; `abort(403)` if missing |

---

## 4. Approved approver model and UI / visibility boundaries

### 4.1 Stage-1 approver ownership (DGAP-05 — DECIDED A)

| Field | Value |
|-------|-------|
| **Stage-1 approver ownership** | **Department line manager** |
| **Downstream** | **Dormitory operations** — not Stage-1 |
| **Authority** | Lead Decision — 1405/04/27 (2026-07-18) |

### 4.2 Visibility model (DGAP-06 — DECIDED V1)

| Field | Value |
|-------|-------|
| **Visibility** | **V1** — employee (subject) + assigned Stage-1 approver only |

### 4.3 Approval surface boundary (DGAP-06 — DECIDED U2)

| Field | Value |
|-------|-------|
| **UI boundary** | **U2** — separate approver console; approval **not** embedded in employee self-service |

---

## 5. Gap Disposition (aligned to open-decisions.md)

| ID | Status | Selected |
|----|--------|----------|
| DGAP-05 | **DECIDED** | A — dept line manager |
| DGAP-06 | **DECIDED** | V1 + U2 |
| OQ-AUTH-01 | **DECIDED** | B — `employee` + `dormitory-manager` |
| OQ-AUTH-02 | **DECIDED** | B — middleware V1 bridge |
| OQ-AUTH-03 | **DECIDED** | B — snapshot at submit |
| OQ-AUTH-05 | **DECIDED** | **A** — governance-accepted; impl unauthorized |
| DGAP-03 | **OPEN/PARKED** | unchanged — does not block Spec04 acceptance |
| SGAP-05 | **PARKED** | Spec06 — unchanged; does not block Spec04 acceptance |

---

## 6. Open Questions — status after technical selection

| OQ ID | Status | Resolution |
|-------|--------|------------|
| **OQ-AUTH-01** | **DECIDED** | Option **B** — see §8.1 |
| **OQ-AUTH-02** | **DECIDED** | Option **B** — see §8.2 |
| **OQ-AUTH-03** | **DECIDED** | Option **B** — see §8.3 |
| **OQ-AUTH-04** | **Resolved via Governance** | DGAP-05 A |
| **OQ-AUTH-05** | **DECIDED** | Option **A** — Spec04 accepted as governance-ready artifact. Does **not** authorize implementation. |

---

## 7. Suggested next steps (roadmap — non-authorizing)

1. Spec04 Auth Packet is **governance-accepted** (OQ-AUTH-05 A). Design work for this packet is closed.
2. **Next gate:** separate implementation authorization process after required contracts and execution approvals.
3. Do **not** start code, migrations, seeders, routes, or UI from this file alone.
4. Do **not** treat this acceptance as L5 or L6 authorization.
5. Leave DGAP-03 and SGAP-05 unchanged unless Lead separately directs.

---

## 8. Technical Brief — OQ-AUTH-01 / 02 / 03 (**DECIDED**)

> **Authority:** Lead Decision 1405/04/27 (2026-07-18). Registered in `docs/governance/open-decisions.md`.  
> **Implementation:** **NOT AUTHORIZED.**  
> **Compliance baseline:** DGAP-05 **A**; DGAP-06 **V1** + **U2**.

### 8.1 OQ-AUTH-01 — Spatie role naming — **DECIDED B**

| Field | Value |
|-------|-------|
| **Selected** | **B** — `employee` + `dormitory-manager` (`guard_name = identity`) |
| **Rationale** | Aligns Stage-1 role with existing workflow abbreviations (`HRMgr` / `DormMgr`) while keeping a distinct `employee` role for self-service; supports U2 without reusing `dormitory-manager` roles (DGAP-05 A). |
| **Impl** | **Not authorized** (no seeder/route changes) |

Historical options A/C remain on record as non-selected.

### 8.2 OQ-AUTH-02 — L5 middleware / V1 bridge — **DECIDED B**

| Field | Value |
|-------|-------|
| **Selected** | **B** — Dual `identity.role:*` stacks + thicker middleware: principal must be **subject** OR **assigned Stage-1 approver**; optional Application re-assert |
| **Rationale** | Early reject at the HTTP edge for principals outside V1; dual stacks keep approve/reject off employee self-service (U2). |
| **Impl** | **Not authorized** (no middleware registration / routes) |

### 8.3 OQ-AUTH-03 — Dept manager → identity — **DECIDED B**

**Resolution chain used at submit (existing columns; snapshot target TBD under future migrate permit):**

```text
request.employee_id
  → employee_employees.department_id
  → employee_departments.manager_id
  → manager employee_employees.identity_id
  → stored as assigned Stage-1 identity UUID on request (value-ref)
```

| Field | Value |
|-------|-------|
| **Selected** | **B** — Snapshot at submit |
| **Rationale** | Literal fit to DGAP-06 V1 “assigned approver”; accountability frozen at submit; later manager changes do not silently reassign visibility/approval. |
| **Impl** | **Not authorized** (no migrations) |

### 8.4 Selection matrix (final)

| OQ | Selected | Status |
|----|----------|--------|
| OQ-AUTH-01 | **B** — `employee` + `dormitory-manager` | **DECIDED** |
| OQ-AUTH-02 | **B** — middleware V1 bridge | **DECIDED** |
| OQ-AUTH-03 | **B** — snapshot at submit | **DECIDED** |

**Not authorized by this section:** code, routes, seeders, migrations, L5/L6 permits.

---

## 9. Exit Criteria & OQ-AUTH-05 — **DECIDED A**

> **Purpose:** Spec04 Auth Packet DRAFT → governance acceptance.  
> **Selected:** Option **A** (Lead Decision 1405/04/27 \| 2026-07-18).  
> **Implementation:** **NOT AUTHORIZED.** No L5/L6 authorization. No code, migration, route, or UI implementation permitted.

### 9.1 Explicit exit criteria (met)

| # | Criterion | Evidence | Met? |
|---|-----------|----------|------|
| 1 | DGAP-05 / 06 documented | §4–5; `open-decisions.md` | ✅ |
| 2 | OQ-AUTH-01 / 02 / 03 selected & documented | §8; `open-decisions.md` | ✅ |
| 3 | V1 + U2 formally mapped | §4.2–4.3 | ✅ |
| 4 | IdentityRoleGuard + Middleware documented | §3.2–3.3, §8.2 | ✅ |
| 5 | Human Gate: Lead approval | OQ-AUTH-05 **Option A** | ✅ |

### 9.2 External dependencies (non-blocking for Spec04 acceptance)

| Dependency | Status | Blocks Spec04 acceptance? |
|------------|--------|---------------------------|
| **DGAP-03** | OPEN / PARKED | **No** — unrelated; unchanged |
| **SGAP-05** / Spec06 | PARKED | **No** — out of packet scope; unchanged |
| Workflow (CD-010 / HD-04) | Deferred | **No** |
| L5 / L6 permits | Not issued | N/A — separate next gate |
| U2 console build | Boundary only | **No** — design boundary accepted; build not authorized |

### 9.3 OQ-AUTH-05 disposition

| Field | Value |
|-------|-------|
| **Status** | **DECIDED** |
| **Selected** | **A** — Accept Spec04 as governance-ready artifact |
| **Authority / Date** | Lead Decision — 1405/04/27 (2026-07-18) |
| **Rationale** | All Spec04-specific business and authentication decisions resolved (DGAP-05, DGAP-06, OQ-AUTH-01, OQ-AUTH-02, OQ-AUTH-03). Remaining unrelated dependencies do not block Spec04 governance acceptance. |
| **Constraints** | No implementation authorization; no L5/L6 authorization; no code, migration, route, or UI implementation permitted |
| **Next gate** | Separate implementation authorization process after required contracts and execution approvals |

### 9.4 Historical options (audit)

| Option | Meaning | Outcome |
|--------|---------|---------|
| **A** | Accept after recorded/validated decisions (impl unauthorized) | **SELECTED** |
| **B** | Keep DRAFT until named external deps resolved | Not selected |

### Selected path summary

| Layer | Decision |
|-------|----------|
| Business actor | Dept line manager = Stage-1 (DGAP-05 A) |
| Visibility | Subject + assigned approver (DGAP-06 V1) |
| UI | Separate approver console (DGAP-06 U2) |
| Roles | `employee` + `dormitory-manager` on `identity` (OQ-AUTH-01 B) |
| L5 gate | Dual role stacks + middleware V1 check (OQ-AUTH-02 B) |
| Binding | Snapshot Stage-1 identity at request submit (OQ-AUTH-03 B) |
| Packet | **GOVERNANCE-ACCEPTED** (OQ-AUTH-05 A) |

**NO IMPLEMENTATION IS AUTHORIZED. NEXT GATE = SEPARATE IMPLEMENTATION AUTHORIZATION.**

---

## Document control

| Field | Value |
|-------|-------|
| Version | **0.5.0-GOVERNANCE-ACCEPTED** |
| Prior | 0.4.0-DRAFT (OQ-AUTH-05 AWAITING HUMAN DECISION) |
| Authority | Lead Decision OQ-AUTH-05 A — documentation acceptance only |
| Reviewers | Lead; HR Business Owner (business scope) |
| Implementation | **NOT AUTHORIZED** — no L5/L6 |
