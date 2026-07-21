# SESSION HANDOFF — Wave 1 (partial)

**Date:** 2026-07-21  
**Status:** PARTIAL — T1 + T4 done; T2/T3 PARKED; exit gate not claimed

## Completed

| Task | Result |
|------|--------|
| Baseline | Documented known-fail (43/1912) — `docs/audit/wave1-baseline-known-fail.md` |
| T1 | KEEP + allowlist on `SessionAuthenticator` + `SessionAuthUserResolver` (Resolver **not** moved to identity — pairing break). Allowlist: `docs/audit/auth-accessor-allowlist.md` |
| T4 | `docs/audit/uuid-inventory.md` |
| Auth slice tests | 7 passed (`Login`/`Logout`/`GetCurrentAuthUser`) |
| Ledger | HD-W1-Q1/Q2/Q3 RESOLVED rows + changelog |

## Parked / blocked

| Task | Reason |
|------|--------|
| T2 | Await T1 allowlist stable + Lead `BEGIN` for T2 |
| T3 | Await allowlist (now defined) + Lead `BEGIN` for T3 |
| Dual-guard `Auth::guard('api'\|'identity')` | DEBT-DISCOVERY / DBT-3 — not fixed |

## DEBT-DISCOVERY-01

- `EstablishApiSessionFromCredentialLoginAction` + Auth session controllers still use `Auth::guard(...)` (DBT-3 adjacent).
- Full-suite Request/WF/Arch failures — out of Wave 1 (see known-fail doc).

## Next Lead action

1. Review T1 allowlist (esp. Resolver KEEP rationale).  
2. Authorize **T3** (guardrail consuming allowlist) and/or **T2** (test bare `auth()`).  
3. Do **not** treat full suite green as Wave 1 requirement until Option A exclusions formalized.

## FROZEN touched

none
