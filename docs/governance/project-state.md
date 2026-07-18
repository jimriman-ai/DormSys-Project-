# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/27 | 2026-07-18 | Sprint: F3-AUTH / F2-Production-Hardening | Session: IMPL-PERMIT-01 APPROVED — migration + route scaffold started_

**Authority note:** IMPL-PERMIT-01 **APPROVED**; L5/L6 **OPEN** for §2 only. Scaffold: snapshot migration, dual routes, IdentityRoleSeeder roles, Stage1 console stub. Prefix `[PERMIT-ID: IMPL-PERMIT-01]`.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| IMPL-PERMIT-01 | docs/governance | Lead **APPROVED**; L5/L6 **OPEN** | FINAL LEAD AUDIT |
| Migration | `2026_07_18_000001_add_assigned_stage1...` | **added** | §2.1 |
| Routes | `web.php` + employee/stage1 route files | **scaffolded** | §2.2 |
| Seeder | IdentityRoleSeeder | `employee` + `DeptMgr` identity | §2.3 |
| UI stub | Stage1ApproverConsolePage | **scaffolded** | §2.5 |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🔀 **Lifecycle — L6 under IMPL-PERMIT-01** (migration + route scaffold in progress; snapshot write / approve UI next)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| IMPL-PERMIT-01 | Limited Spec04 impl | **APPROVED** | L5/L6 OPEN §2 | — |
| Snapshot migration | assigned_stage1_approver_identity_id | added | run migrate | env |
| Dual routes | employee.requests + approvals.stage1 | scaffolded | wire logic | — |

---

## 6. Lifecycle Matrix

| Feature | Status |
|---------|--------|
| Spec04 Auth | ✅ |
| Spec04 FC | ✅ 1.0.0-READY |
| IMPL-PERMIT-01 | ✅ APPROVED |
| Spec04 Impl | ⏳ in progress (§2) |

---

## 7. Next Step

**Action:** Persist snapshot on submit; harden employee List/Show/Create/Cancel under identity; wire Stage-1 approve/reject Application Actions on console.  
**Owner:** Agent under IMPL-PERMIT-01  
**Gate:** Permit §2 only  
**Done when:** Permit DoD met  
**Blocker:** none for §2  

**Suggested user prompt:**
> Continue IMPL-PERMIT-01: wire Stage-1 snapshot on submit + Approver Console approve/reject via Application Actions.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| IMPL-PERMIT-01 | APPROVED | coding authorized §2 |
| Outside §2 | NOT AUTHORIZED | — |

**W2 acceptance:** W2-CLOSE COMPLETE.
