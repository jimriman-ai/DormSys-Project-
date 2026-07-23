# ARCH-FAILURE-TRIAGE-01

> Discovery + Decision Gate. **Not a Fix wave.**  
> Suite evidence: REGRESSION-FIX-01 full Pest → **45 failed** / 1961 passed (Architecture only).  
> Captured: 1405/05/01 (2026-07-23).  
> CLOSED decisions not reinterpreted (A2, DP-XMOD-BELONGS, DP-ALLOC-ITEM-BED-FK, DP-BED-SIGNAL-OWNERSHIP).

---

## 1. Inventory (45)

| # | Test class | Assertion theme (message gist) | Cluster |
|---|------------|--------------------------------|---------|
| 1–6 | `AllocationBoundaryTest` | Allocation ↛ Request/Dormitory/Employee Infra + Persistence | **C1** |
| 7 | `CheckInBoundaryTest` | CheckIn ↛ Allocation Persistence | **C1** |
| 8–13 | `LotterySupplierBoundaryTest` | Lottery ↛ Request/Employee/Dormitory Infra + Persistence | **C1** |
| 14–16 | `RequestConsumerBoundaryTest` | Request ↛ Employee/Dormitory Infra + Persistence | **C1** |
| 17–18 | `VoucherBoundaryTest` | Voucher ↛ Request Infra + Persistence | **C1** |
| 19 | `ReportingBoundaryTest` | Reporting ↛ Audit Infra | **C1** |
| 20–38 | `ModuleBoundaryTest` (19 of 20) | Infra layers ↛ foreign modules (Identity/Employee/Request/Dormitory/Allocation/Audit/…) | **C1** |
| 39 | `ModuleBoundaryTest` | Request **Application** ↛ Workflow **Domain** | **C2** |
| 40 | `ForbiddenImportsScanTest` | `arch:scan` exit≠0 — MATRIX Request→Workflow Domain exceptions **and** unregistered adapter | **C2+C3** (single test, two findings) |
| 41 | `ModuleInventoryParityTest` | Same Request→Workflow Domain exception set | **C2** |
| 42 | `CrossModuleAdapterLocationTest` | `RequestLifecycleCommandAdapter` not in legacy adapter allowlist | **C3** |
| 43 | `IntegrationCompositionRootTest` | `IntegrationServiceProvider` declares `boot()` | **C4** |
| 44–45 | `MutationAuthorizationBoundaryTest` ×2 | Workflow mutation actions unaccounted / business-mutation list | **C5** |

**Count check:** C1 = 38 · C2 exclusive = 2 (+ share of #40) · C3 exclusive = 1 (+ share of #40) · C4 = 1 · C5 = 2 · **Total assertion failures = 45**.

---

## 2. Cluster table

| ID | Cause (what it is) | Count | Owner | CLOSED decision governs? | Lead decision vs mechanical under CLOSED | Blocking baseline? |
|----|-------------------|-------|-------|--------------------------|------------------------------------------|-------------------|
| **C1** | Architecture Pest forbids cross-module **Infrastructure / Persistence** imports while disk has Persistence `belongsTo` (and related Infra type refs) | **38** | Architecture / Guard maintainers + Lead | **Partially — DP-XMOD-BELONGS Option C CLOSED** allows Persistence read `belongsTo`; forbids Eloquent in workflow/auth/mutation. Option C **next-wave** text already names Architecture Guard allowlist alignment. Blanket “no Infra imports” tests may be **stricter than** Option C. | **Lead AUTH required** to start Option C next-wave (guard allowlist scope). Not a new Option A/B/C choice if Lead affirms “execute stated next-wave.” Anything removing Persistence relations or reopening Option C → **new Lead decision**. | **Yes** (majority of 45) |
| **C2** | Request **Application** imports Workflow **Domain** exceptions (`ApproveRequestStageAction`, `RejectRequestAction`) — surfaced by ModuleBoundary, `arch:scan`, ModuleInventoryParity | **3 tests** (findings shared) | Architecture (Request↔Workflow boundary) | **No CLOSED decision** covers Application→foreign Domain exception imports. DP-XMOD is Eloquent/belongsTo-scoped. | **Lead decision required** (Architecture / ownership of exception types or allowlist exception). Not mechanical under Option C. | **Yes** |
| **C3** | Unregistered cross-module adapter: `Allocation\…\RequestLifecycleCommandAdapter` → `RequestRepositoryContract` | **2 surfaces** (CrossModuleAdapterLocation + `arch:scan` UNREGISTERED line) | Architecture (adapter registry / Integrations) | **No** CLOSED decision registers this adapter. | **Lead decision required** (legacy allowlist vs move to Integrations vs redesign). Listing in `architectureLegacyCrossModuleAdapterPaths()` without AUTH would be acting past gate. | **Yes** |
| **C4** | `IntegrationServiceProvider` overrides `boot()`; test requires register-only | **1** | Architecture (composition root) | **No** CLOSED decision found for boot vs register | **Lead decision required** (relax test vs move bindings out of `boot`) | **Yes** |
| **C5** | Three Workflow Application services absent from mutation-authorization accountancy registries | **2** | Architecture / Mutation policy | **No** CLOSED decision in `.dormSys/open-decisions.md` for these three actions | **Lead decision required** (adopt/exempt/pending registry policy) | **Yes** |

---

## 3. Mechanically fixable under existing CLOSED text? (flags only)

| Cluster | Mechanical under CLOSED Option C next-wave? | Notes (descriptive) |
|---------|-----------------------------------------------|---------------------|
| C1 (Persistence `belongsTo` allowlist in guards only) | **Conditionally yes** — if Lead treats DECISION-CLOSE-01 next-wave (“Architecture Guard allowlist wording”) as sufficient AUTH | Still **STOP** until Lead explicitly opens that Fix wave. Scope must not unwind relations or reopen Option C. |
| C1 (broader Infra import bans beyond Persistence belongsTo) | **No** — exceeds Option C statement | Needs Lead scope call |
| C2–C5 | **No** | Need Architecture (or mutation-policy) Lead decisions |

**A2 / ALLOC-ITEM-FK / BED-SIGNAL:** none of the 45 map to those CLOSED items.

---

## 4. Recommended sequencing (for Lead — not executed)

1. **Decide C1 scope** — Affirm Option C next-wave guard alignment (Persistence read `belongsTo` only) vs broader Infra rewrite. Largest count (38); unblocks most of baseline.  
2. **Decide C2** — Request Application ↔ Workflow Domain exception imports (policy allowlist vs relocate types). Unblocks `arch:scan` MATRIX + inventory + one ModuleBoundary.  
3. **Decide C3** — Fate of `RequestLifecycleCommandAdapter` (legacy list vs Integrations). Unblocks adapter test + scan UNREGISTERED line.  
4. **Decide C4** — Integration composition-root `boot()` rule.  
5. **Decide C5** — Workflow mutation registry accountancy.  

Parallelization: C4/C5 independent of C1; C2/C3 couple through `arch:scan` (one test, two findings).

---

## 5. Advisor (classification / sequencing)

| | |
|--|--|
| **Current approach** | Five clusters by root cause; C1 tied to DP-XMOD Option C; C2–C5 as ungoverened Architecture |
| **Recommended approach** | Same clusters; sequence C1 → (C2∥C3 via scan) → C4 → C5 |
| **Reason** | C1 is 38/45; Option C already anticipates guard allowlist work; C2/C3 share `ForbiddenImportsScanTest` |
| **Risks / Trade-offs** | Treating all C1 as “Option C mechanical” may over-claim if failures are non-belongsTo Infra imports — Lead must define allowlist **width**. Sequencing C2 before C1 delays largest green delta. |

---

## 6. Guardrails confirmation

- Read/report only. No code, schema, migration, or CLOSED-decision edits.
- No Fix attempted.
- Suite state unchanged by this wave (descriptive report only).
