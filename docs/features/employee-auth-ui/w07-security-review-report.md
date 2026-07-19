# W-07 Security Review Report — employee-auth-ui

| Field | Value |
|-------|-------|
| **Executed** | 1405/04/24 (2026/07/15) |
| **Scope** | [w07-security-review-scope.md](./w07-security-review-scope.md) (YES with AMEND A-1/A-2/A-3) |
| **W-08 verification** | ACCEPTED — W08-C-01/C-02 PASS ([w08-scope.md](./w08-scope.md)) |
| **Verdict** | **CLOSED** (Lead Acceptance, 1405/04/24) |

---

## Findings — final disposition

| Finding ID | Final Status | Notes |
|------------|--------------|-------|
| F-W07-01 | **CLOSED** — Fixed and verified | `RateLimiter` in `EmployeeLogin::login`; `EmployeeLoginRateLimitTest` + W08-D |
| F-W07-02 | **CLOSED** — Risk acceptance verified | W08-C-01 / W08-C-02 PASS; no logout code change |
| F-W07-03 | **NOTE** — intentional dual principal | `web` + `api`/`identity` after success |
| F-W07-04 | **Wave 1 ✅ COMPLETED** — slug `stage1-approver-console` (**F-W07-04-D1**); FC ACCEPTED / F2 **PASS** (**D2**); Wave 1 impl closure (**D3**). Prior CARRIED FORWARD → F3 Sprint B superseded for Wave 1. Canonical ID retained. See `governance-log.md:15–17`; `open-decisions.md` § F-W07-04. | Stage-1 approver console Wave 1 (list/filter); UX tests deferred |
| F-W07-05 | **NOTE** — canonical guards only | No `employee` guard |
| F-W07-06 | **CLOSED** — password broker negative check | W-03 NO ACTION; no reset routes/UI |
| F-W07-07 | **NOTE** | C-5 / IdentityRoleGuard pin OK |

**Blockers:** none. **W-07:** CLOSED.

---

## Checklist outcomes (historical)

| ID | Result | Notes |
|----|--------|-------|
| A1–A4 | PASS | C-5 call chain |
| B1–B3 / B/A-1 | PASS / NOTE | Routes + guard matrix |
| C1–C3 | PASS | IdentityRoleGuard pin |
| D1–D3 | PASS | Credential hygiene |
| D/A-2 throttle | FIXED → CLOSED (F-W07-01) | RateLimiter |
| D/A-2 logout | COND → CLOSED via W08-C (F-W07-02) | Risk accepted + verified |
| E1–E/A-3 | PASS / NOTE | DGAP-07 A; broker negative check |

---

## Guard / provider matrix (A-1)

| Guard | Provider | Model | Role in F2 |
|-------|----------|-------|------------|
| `web` (default) | `users` | `App\Models\User` | Credential `Auth::attempt` |
| `api` | `identity` | `UserModel` | Establish bind; `requests.*` |
| `identity` | `identity` | `UserModel` | Establish bind; dormitory-admin + IdentityRoleGuard |
| *(no `employee` guard)* | — | — | Boundary name only |

---

**Closeout constraint:** No UI authorization, no new feature slug, no Spec04/DGAP/BL-B1-01 reopen from this closeout.
