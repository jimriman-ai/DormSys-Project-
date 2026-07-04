# spec11 Reporting Module — Rollback Checklist

**Artifact ID:** `spec11-reporting-rollback-checklist`  
**Revision:** 1.0.0  
**Recorded:** 2026-07-04  
**Scope:** spec11 Reporting module (`app/Modules/Reporting/`, reporting API transport, reporting migrations, reporting tests)  
**Related task:** T-GL-E05 (release-input preparation)

---

## 1. Rollback owner / authority

| Field | Value |
| ----- | ----- |
| **Primary owner** | Release Engineering (operational execution) |
| **Technical authority** | Product / Tech governance (spec11 implementation authorization holder) |
| **Escalation** | Governance Review per `implementation-authorization-decision.md` |
| **Out of scope for this artifact** | Production rollout authorization (remains NOT authorized until separate governance record) |

---

## 2. Rollback trigger conditions

Initiate rollback when **any** of the following occur after a Reporting module release candidate is deployed to a target environment:

| ID | Trigger |
| -- | ------- |
| **RT-01** | Sustained HTTP 5xx rate on `api/reporting/*` above environment SLO threshold |
| **RT-02** | Confirmed authorization bypass (data returned without valid `audit.read` gate) |
| **RT-03** | Confirmed archive visibility bypass (`includeArchived=true` honored without permission) |
| **RT-04** | Provenance envelope missing mandatory fields (`sourceTier`, `includeArchived`, `filterHash`) on compiled RU endpoints |
| **RT-05** | Cross-module boundary violation detected (Reporting imports `App\Modules\Audit\Infrastructure\*` or direct `audit_logs` access) |
| **RT-06** | Governance directive to halt spec11 Reporting exposure |

---

## 3. Rollback procedure

### 3.1 Pre-rollback

1. Record incident ID, environment, deployed revision (git SHA), and triggering condition (RT-0X).
2. Notify Release Engineering owner and governance escalation contact.
3. Capture evidence: failing request samples (redacted), test failures, error rates.

### 3.2 Application rollback

1. Redeploy **previous known-good application revision** (git tag/SHA documented in release record).
2. If route exposure must be disabled immediately without full redeploy:
   - Remove or disable `api/reporting/*` route group registration in `routes/api.php` at previous revision, **or**
   - Apply upstream gateway deny rule for `/api/reporting/*` (environment-specific; document in incident record).
3. Do **not** mutate spec10 Audit module code, `audit_logs`, or frozen `AuditHistoryReadContract` during Reporting rollback.

### 3.3 Database / projection rollback

Reporting-owned migrations live under `database/migrations/modules/reporting/`.

| Scenario | Action |
| -------- | ------ |
| **Schema incompatible with previous app** | Roll back Reporting migrations to last compatible batch (`php artisan migrate:rollback --path=database/migrations/modules/reporting` per environment runbook) |
| **Schema compatible; data-only issue** | Pause projection refresh jobs; do not delete `audit_logs`; optional truncate of Reporting projection tables only if governance approves |
| **No migration change in release** | Skip migration rollback; application rollback sufficient |

### 3.4 Cache / queue

1. Flush application cache if Reporting response cache keys include stale provenance (`filterHash` / `includeArchived` boundaries).
2. Stop Reporting projection refresh workers if running separately from main queue consumer.

---

## 4. Post-rollback verification

Execute in target environment after rollback completes:

| # | Verification | Pass condition |
| - | ------------ | -------------- |
| **V-01** | `api/reporting/*` unreachable or serves previous revision behavior per decision in §3.2 | No new-regression traffic accepted |
| **V-02** | spec10 audit read paths unaffected | Existing audit history tests pass |
| **V-03** | `php artisan test tests/Architecture/ReportingBoundaryTest.php` on rolled-back revision | PASS |
| **V-04** | If Reporting routes remain enabled on rolled-back revision: run `tests/Feature/Modules/Reporting/ReportingApiTest.php` | PASS |
| **V-05** | PHPStan level 8 on `app/Modules/Reporting/` at rolled-back revision | 0 errors |
| **V-06** | Incident record updated with rollback SHA, timestamp (UTC), owner sign-off | Record closed |

---

## 5. Traceability

| Field | Value |
| ----- | ----- |
| **Artifact version** | 1.0.0 |
| **Created** | 2026-07-04 |
| **Authoring context** | spec11 task graph execution validation — T-GL-E05 closure |
| **Binding references** | `implementation-authorization-decision.md`, `spec11-system-truth-model.md`, `tasks.md` |
| **Change control** | Revision increment required for material procedure changes; no runtime behavior change implied by this document alone |

---

**End of rollback checklist. Release-engine input artifact only. Does not authorize deployment or rollout.**
