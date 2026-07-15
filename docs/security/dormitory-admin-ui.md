# dormitory-admin-ui — Security notes

## Remediated (2026-07-15)

| ID | Decision | Note |
|----|----------|------|
| SEC-G-01 | REMEDIATED (H-01a) | `identity.role` middleware + `IdentityRoleGuard` require Spatie `guard_name = identity`. |
| SEC-G-02 | REMEDIATED (H-02b) | Both dashboards re-assert identity role in `render()` via `IdentityRoleGuard`. |
| SEC-G-03 | REMEDIATED (H-03a) | Query-derived collections are no longer public Livewire state; built in `render()` and passed to the view. |

## Accepted Risks

### SEC-G-04 — Internal UUIDs in data attributes (H-04b)

- **Description:** Authorized dashboard markup may expose dormitory/room UUIDs in `data-*-id` attributes.
- **Rationale:** Only authenticated identity users with the matching identity-guard role can load the page; assignment scoping (pivot + `user_id`) closes IDOR. UUID exposure is reconnaissance-only for already-authorized managers.
- **Decision:** Accepted risk — no code change.
- **Date:** 2026-07-15
- **Decision ref:** H-04b / D-G-13
- **Owner:** Tech Lead
- **Review cadence:** every 6 months, or at the start of a major phase
- **Authorization:** L5-G-CANONICAL-01 (`.specify/memory/dormitory-admin-ui-authorization.md`)
