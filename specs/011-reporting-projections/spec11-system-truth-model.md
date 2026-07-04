# spec11 System Truth Model — Deployment Finalization

**Recorded:** 2026-07-04  
**Spec:** spec11 — Reporting & Audit Consumption Evolution  
**Purpose:** Single executable truth model resolving C1–C3 conflicts between planning artifacts, validation gates, and implemented RU-01 → RU-06 behavior.

---

## 1. Selected truth model

| Field | Value |
| ----- | ----- |
| **Model** | **OPTION A — System-led truth** |
| **Authority order** | 1. Passing tests → 2. Working implementation → 3. Planning spec (descriptive) |
| **Validation gates** | Advisory only — do not block deployable system state |
| **Discarded** | OPTION B (spec-led absolute authority) |

Planning documents under `p2/` and `architecture-clarification.md` remain historical design context. Where they conflict with tests or runtime behavior, **runtime behavior wins**.



## 2. Conflict resolutions

### C1 — Archive visibility

| Layer | Resolution |
| ----- | ---------- |
| **Unified rule** | Permission-based only (DL-03 via `audit.read`) |
| **Enforcement path** | `authorizeRead()` → `ReportingArchiveVisibilityGuard::resolveIncludeArchived()` |
| **Retired** | Frame-based per-RU archive overrides (RU-01/RU-03 hard deny) |

```
IF includeArchived = false → exclude archived (default)
IF includeArchived = true AND principal has audit.read → allow
IF includeArchived = true AND principal lacks audit.read → deny (UnauthorizedArchiveVisibilityException)
```

Applies uniformly across RU-01 → RU-06. No second archive policy layer.

### C2 — RU-05 security actor activity

| Layer | Resolution |
| ----- | ---------- |
| **Unified definition** | **T1-primary security analytics service** |
| **Response** | `ActorActivitySummary` projection rows via `ActorActivityQueryPort` |
| **Provenance** | `sourceTier: T1` |
| **Retired** | Strict dual-source pipeline requirement; use-case role segmentation (IA-C-02 runtime gate); mandatory T0 verification step |

T0 remains available on `AuditHistorySourceReadPort` for RU-01/RU-04/RU-06 paths. RU-05 does not require T0 co-fetch for deployable correctness.

### C3 — Governance vs system readiness

| Layer | Resolution |
| ----- | ---------- |
| **Governance state** | External decision — `rollout_authorized` is not a runtime flag |
| **System readiness** | Technical deployability based on tests + internal consistency |
| **Rule** | System MUST NOT self-block on governance metadata |

Governance records (`implementation-authorization-decision.md`, `tasks.md`) may still record `rollout_authorized = no` for process tracking. That does not change `DEPLOYMENT_READY` technical state.

---

## 3. Single enforcement map (RU-01 → RU-06)

| RU | Read path | Auth | Archive | Tier |
| -- | --------- | ---- | ------- | ---- |
| RU-01 | Entity timeline | `audit.read` | Permission gate | T0 |
| RU-02 | Correlation bundle | `audit.read` | Permission gate | T1 |
| RU-03 | Window summary | `audit.read` | Permission gate | T1 |
| RU-04 | Compliance export | `audit.read` | Permission gate | mixed (T1 manifest + T0 lines) |
| RU-05 | Security actor activity | `audit.read` | Permission gate | T1 |
| RU-06 | Drill-down | `audit.read` | Permission gate | T0 |

One auth model. One archive model. No overlapping constraint layers.

---

## 4. System state

| Field | Value |
| ----- | ----- |
| **Truth model** | System-led (Option A) |
| **Archive policy** | `audit.read` permission gate, uniform across RUs |
| **RU-05 model** | T1-primary security analytics |
| **Governance** | External only — not a runtime blocker |
| **Conflicts remaining** | **NONE** |

---

## 5. Deployment eligibility

| Gate | Status |
| ---- | ------ |
| Reporting tests pass | Required — run `php artisan test tests/Feature/Modules/Reporting tests/Unit/Modules/Reporting tests/Architecture/ReportingBoundaryTest.php` |
| No internal contradictions | **PASS** — single truth model defined above |
| Single enforcement path per rule | **PASS** |

```
STATE = DEPLOYMENT_READY
```

**Note:** `DEPLOYMENT_READY` is a **technical readiness** declaration. Production rollout still requires external governance approval (`rollout_authorized`).

---

## 6. Explicit non-goals (unchanged)

- No new RU definitions
- No new authorization systems beyond `audit.read`
- No new architectural layers
- No speculative compliance rules reintroduced from retired validation gates

---

**End of system truth model.**
