# PA-03 Review — UI-M2 Implementation Authorization

**Mode:** REVIEW artifact (this file only).  
**Date:** 1405/04/28 \| 2026-07-19  
**Subject:** UI-M2 (`docs/features/ui-m2/l3-spec.md`) — readiness for SB-D7 / Implementation Lock  
**Context cited:** SB-D6 ACCEPTED; SB-D7 not issued; F-W07-04 Wave 1 COMPLETED; C-1/C-2/G-3 applied (WP-01 rev-4)

> **Non-authority:** This review does **not** issue SB-D7 or create an Implementation Lock. Lead issues SB-D7 only after accepting this recommendation.

---

## R1 — Pre-Authorization Checklist

| # | Check | Result | Evidence |
|---|-------|--------|----------|
| 1 | `l3-spec.md` status comment = `L3 ACCEPTED — SB-D6` | **PASS** | `docs/features/ui-m2/l3-spec.md:1` — `<!-- STATUS: L3 ACCEPTED — SB-D6 -->` |
| 2 | C-1 (FR-4 empty copy) applied | **PASS** | FR-4 quotes «اتاقی به شما اختصاص داده نشده است.» (`l3-spec.md:44`) |
| 3 | C-2 (lifecycle / Document control) applied | **PASS** | Lifecycle `:4` + Document control Status `:141` = **L3 ACCEPTED (SB-D6) — L8-MAPPABLE**; no “awaiting Lead L3 review” |
| 4 | G-3 (roadmap catalog entry) applied | **PASS** | `docs/governance/roadmap.md:40` — **L3 ACCEPTED (SB-D6)**; L6+ NOT authorized — Implementation Lock required (Sprint B backlog `:53` aligned) |
| 5 | `open-decisions.md`: SB-D6 = DECIDED(A); SB-D7 absent | **PASS** | SB-D6 gate row `:57` **DECIDED (A)**; grep SB-D7 = no hits in `open-decisions.md` / `governance-log.md` |
| 6 | No BLOCKER gaps remaining from PA-02 | **PASS** | PA-02 had **zero BLOCKERs** (PASS-with-fixes; G-1/G-2/G-3 were MINOR). C-1/C-2/G-3 applied under SB-D6 |

**R1 verdict:** all checklist items **PASS**.

---

## R2 — Scope & Auth Boundary

### Non-overlap with F-W07-04 (`stage1-approver-console`)

| Surface | Route | Role | Purpose |
|---------|-------|------|---------|
| **UI-M2** | `GET /dormitory-admin/unit` | `dormitory-unit-manager` | Assignment-scoped **room** occupancy |
| **F-W07-04** | `/approvals/stage1/*` | `dormitory-manager` (DGAP-13) | Stage-1 approve/reject console |

UI-M2 §7 explicitly outs-of-scopes Request / Stage-1 / Allocation mutation and UI-M1 (`dormitory-manager` / `/dormitory-admin`). **No scope overlap** with Wave 1 COMPLETED Stage-1 console.

### Role gates (spec truth vs prompt CONTEXT)

| Prompt R2 wording | Spec actual | Assessment |
|-------------------|-------------|------------|
| “All routes/gates … reference **dormitory-manager** only” | Spec gates use **`dormitory-unit-manager`** only (`:15`, `:43–45`, `:58–59`, AC-2) | Prompt CONTEXT conflates **DGAP-13** Stage-1 role with UI-M2. Spec is **correct**. `dormitory-manager` appears only as **OOS** sibling (UI-M1, `:107`). |

**Boundary verdict:** **PASS** on actual UI-M2 auth boundary. Do **not** treat the prompt’s `dormitory-manager`-only wording as a spec defect. When issuing SB-D7, Lock auth_gate must be `dormitory-unit-manager` (identity), not `dormitory-manager`.

### UUID IDs

| Spec location | Rule |
|---------------|------|
| §5 Data constraints | Identity IDs = UUID strings (`identity_users.id`) (`:83`) |
| §4 Dual-Principal | Assignment FK `user_id` = Identity UUID (`:72`) |
| FR-4 / FR-5 | Principal / assignment keyed by UUID |

**UUID verdict:** **PASS**.

**R2 verdict:** **PASS** (scope clean; role = unit-manager; UUIDs stated).

---

## R3 — Implementation Readiness

### Sufficient for Implementation Lock creation?

**Yes.** The L3 artifact contains:

- Boundary statement (§0)
- Preconditions vs deliverables (§1) — baseline wiring already present
- Testable FR-1…FR-10 (§2) with file:line evidence
- Auth / SEC-G requirements (§3)
- Dual-principal rules (§4)
- Data constraints + DGAP-09 RE-FROZEN (§5)
- L8-mappable AC-1…AC-7 (§6)
- Explicit OOS including Stage-1 / schema / L6+ without separate IA (§7)
- Next gate: Implementation Lock; L6+ not authorized (§8 / Document control)

Peer pattern available: `docs/features/stage1-approver-console/implementation-lock.md` (fields: status, scope, forbidden_changes, auth_gate, basis).

### BLOCKER-level gaps (prevent Lock)?

**None.**

### MINOR notes (do not block Lock)

| ID | Note |
|----|------|
| M-1 | Implementation **baseline already wired** (component + route + tests). Lock should state whether authorized work is **verify/align-to-L3** / test polish only vs net-new L6 — avoid implying greenfield build. |
| M-2 | Roadmap “Current wave” prose (`roadmap.md:75`) still mentions “UI-M2 (L3 review)” — residual DOC lag outside L3; not a Lock blocker. |
| M-3 | SB-D7 / Lock must pin auth_gate = `dormitory-unit-manager` (see R2 CONTEXT correction). |

**R3 verdict:** sufficient detail; **zero BLOCKERs**.

---

## R4 — Recommendation: **PASS**

| Question | Answer |
|----------|--------|
| May SB-D7 be issued? | **Yes** (Lead decision — this review does not issue it) |
| May Implementation Lock proceed? | **Yes**, after SB-D7 |
| FAIL BLOCKERs? | **None** |

### Post-PA-03 path (for Lead — not executed by this review)

1. If Lead accepts **PASS** → issue **SB-D7** (L6+ authorization scope as Lead defines) **and** create Implementation Lock (auth_gate = `dormitory-unit-manager`; L6+ only within Lock).
2. If Lead rejects → treat as FAIL path; no SB-D7 until BLOCKERs named by Lead are cleared.

**Constraint reminder:** SB-D6 did **not** authorize L6+. SB-D7 remains the gate for L6+ / Lock execute authority.
