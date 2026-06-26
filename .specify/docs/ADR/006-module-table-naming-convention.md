# ADR-006: Module-Scoped Table Naming Convention

**Status:** Accepted  
**Date:** 2026-06-26  
**Deciders:** Product Owner & Tech Lead  
**Related:** Constitution §11, spec01 ADR-003, spec02 `identity_users`, spec03 `employee_*`

---

## Context

Early foundation drafts and Constitution v1.2 referenced a global `tbl_{name}` prefix for all application tables. During Wave 1A implementation (spec02 Identity), tables were created as `{module_prefix}_{entity}` under `database/migrations/modules/{module}/` (e.g. `identity_users`).

Without a formal decision, spec03 and later modules risk mixing conventions, breaking migration tests, and conflicting with agent documentation.

---

## Decision

**Adopt `{module_prefix}_{entity}` (snake_case) for all module-owned application tables.**

| Rule | Detail |
|------|--------|
| Pattern | `{module}_{entity}` — e.g. `employee_employees`, `employee_departments` |
| Location | `database/migrations/modules/{module}/` |
| Cross-module FK | **Prohibited** — UUID references only (AP-04, CD-012) |
| Intra-module FK | Allowed within same bounded context |
| Package tables | Spatie and vendor tables keep package names (`permissions`, `activity_log`, etc.) |
| Foundation | Legacy `tbl_settings` (if present) remains; new modules use module prefix |

The Constitution §11 module table list is illustrative logical names; physical names follow this ADR.

---

## Rationale

1. **Module ownership clarity** — table name encodes bounded context at a glance.
2. **Already implemented** — spec02 `identity_users` is live; renaming would be costly.
3. **Migration path organization** — aligns with per-module migration folders from spec01.
4. **PostgreSQL practicality** — snake_case matches Laravel/Eloquent defaults.

---

## Alternatives Considered

| Option | Verdict |
|--------|---------|
| Global `tbl_` prefix | Rejected — superseded by implemented module prefix pattern |
| Unprefixed short names (`users`, `employees`) | Rejected — collision risk if auth package added later |
| Schema-per-module | Rejected — out of scope for shared-database monolith |

---

## Consequences

- **Positive:** Single naming truth for spec03+; agent docs and Constitution align with code.
- **Negative:** Constitution v1.3.0 §11 `tbl_` wording is superseded — corrected in same governance pass.
- **Action:** spec01 migration naming tests should accept `{module}_*` pattern (update when spec01 tasks resume).

---

## Compliance

- [x] `identity_users` — spec02
- [ ] `employee_departments`, `employee_employees` — spec03 MVP
- [ ] Future modules follow `{module}_{entity}`
