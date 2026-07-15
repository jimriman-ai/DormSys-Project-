# Product Authorization — employee-auth-ui

- **Status:** AUTHORIZED
- **Phase:** F2
- **Boundary:** employee-auth-ui (independent UI boundary per DG-01)
- **Guard:** `auth:identity` (provider `identity` → `App\Modules\Identity\Infrastructure\Persistence\Models\UserModel`, table `identity_users`)
- **Authorized scope:** employee-facing authentication UI (login, session, role-gated routes via IdentityRoleGuard / Shared Kernel — BL-04 / W-06 delivered)
- **Explicitly out of scope:** password broker / reset flow (W-03 RESOLVED — NO ACTION; no broker for `identity` provider). Employee↔UserModel Eloquent relationship — **not required**; DGAP-07 Decision A (`docs/governance/open-decisions.md`): existing `identity_id` UUID value-reference is sufficient (W-02 CLOSED).
- **Supersedes-note:** `product-authorization-next-ui-feature.md` excluded "Employee UI" from ITS scope only; this record is a NEW independent authorization and does not amend that exclusion.
- **Cross-refs:** DG-01, DG-03, DGAP-07, BL-04, `roadmap.md` (F2), `docs/features/employee-auth-ui/work-breakdown.md`
