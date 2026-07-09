# Request UI Performance Baseline

## Status

Closed — canonical baseline for the Requests module UI performance investigation.  
Completed: 2026-07-09.

## Purpose

Record whether perceived Request UI slowness reflects an application problem or a contaminated local measurement profile.

This document is diagnostic evidence only. It does not authorize implementation work.

## Scope

- Request List (`/requests`)
- Request Show
- List ↔ show navigation
- Local development runtime (Docker / Sail on Windows host)

---

## Summary

Perceived slowness was **real** in the measured local profile. Evidence does **not** support a confirmed application-level bottleneck in Request read paths.

Warm application cost remained bounded:

| Path | Warm latency |
|---|---|
| `refreshList` (list) | ~90–170 ms |
| Show `mount` | ~76–95 ms |
| Database (warm) | ~12–48 ms |

Primary cause: **development-environment degradation** — especially missing frontend assets, with additional overhead from Xdebug and Telescope in local.

After temporary environment cleanup (assets built; Xdebug and Telescope disabled for comparison), asset latency improved dramatically. Warm application and database timings did not change materially.

**Diagnosis: development-environment induced slowdown.**

---

## Findings

1. **Perceived slowness was reproducible** in the local development profile.

2. **Request read paths were not the primary bottleneck.** Warm list and show operations stayed within the ranges above.

3. **Database cost was not the primary bottleneck.** Warm query time remained ~12–48 ms.

4. **No N+1 query pattern was confirmed** on measured list and show read paths.

5. **Missing frontend assets caused severe full-page latency** before cleanup. After valid asset delivery, asset fetch time dropped from multi-second 404 responses to sub-100 ms successful responses.

6. **Xdebug and Telescope were enabled locally** and contaminated performance measurements. They must be treated as non-neutral dev conditions, not product baselines.

7. **Host-level page timing remained multi-second** even after environment cleanup (~9.7 s → ~8.9 s mean for `GET /requests`), with high run-to-run variance. Residual gap is not explained by warm application or database cost.

---

## Non-Findings

The investigation did **not** confirm:

- an N+1 query problem
- database cost as the dominant factor
- authorization or session lookup as the dominant factor
- oversized payloads as the dominant factor
- a Request-module read-path regression requiring code intervention

---

## Governance Outcome

| Question | Decision |
|---|---|
| Optimization code changes authorized? | **No** |
| Separate performance feature contract required? | **No** |
| Feature delivery blocked? | **No** |
| Priority vs next product feature | **Lower** |

**Decision:** Proceed to the next product feature. This investigation is closed as a diagnostic track.

### Not authorized by this baseline

- performance refactors or query rewrites
- Livewire lifecycle or loading-strategy changes
- navigation or Blade/UI changes for performance
- architecture changes
- scope expansion based on local dev latency alone

---

## Reopen Conditions

Reopen only if slowness is **reproducible under a clean measurement profile**:

- built frontend assets present and resolving (no asset 404s)
- Xdebug disabled
- Telescope disabled unless intentionally profiling
- stable local or staging-like runtime

A reopen request must include the reproducible scenario, measured timings, and confirmation that the profile above was satisfied. Anecdotal perception alone is insufficient.

---

## Measurement Profile (reference)

Original local profile: `APP_ENV=local`, `APP_DEBUG=true`, Xdebug enabled, Telescope active, built frontend assets missing, Vite dev server unavailable.

Verification (no application source changes): temporary Xdebug/Telescope disable, `npm run build`, repeated scenarios, environment restored afterward. Built assets remain under `public/build/assets/`.

For ongoing dev measurement rules, see `docs/development/dev-performance-hygiene.md`.

---

## Conclusion

Request UI slowness in the measured environment was real but **environment-caused**, not application-caused. Warm read paths and database cost stayed low; no optimization work is authorized from this baseline.
