# Product Authorization — employee-auth-ui

- **Status:** AUTHORIZED
- **Phase:** F2
- **Boundary:** employee-auth-ui (independent UI boundary per DG-01)
- **Guard:** `auth:identity` (provider `identity` → `App\Modules\Identity\Infrastructure\Persistence\Models\UserModel`, table `identity_users`)
- **Authorized scope:** employee-facing authentication UI (login, session, role-gated routes via IdentityRoleGuard post-BL-04 migration)
- **Explicitly out of scope:** password broker / reset flow (no broker configured for `identity` provider — requires separate decision before L3 spec W-03); Employee↔UserModel Eloquent relationship design (L3 spec W-02).
- **Supersedes-note:** `product-authorization-next-ui-feature.md` excluded "Employee UI" from ITS scope only; this record is a NEW independent authorization and does not amend that exclusion.
- **Cross-refs:** DG-01, DG-03, BL-04, `roadmap.md` (F2)
