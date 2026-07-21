# Wave 3 — WP-WF-04 / Request transition known-risk

**Registered:** 2026-07-21 · **Wave:** Completion Wave 3 (W3-B)  
**Classification:** **KNOWN-RISK** — not a Wave 3 implementation blocker

---

## Source

`docs/audit/wave1-baseline-known-fail.md` — cluster **Request transition** (`InvalidRequestTransitionException` in Request HTTP / Mutation / Stage1 / Production hardening).

Related governance: WP-WF-04 Request cutover (optional dual-run; final STOP/GO) under HD-WF-01.

---

## Why not a block for W3-B

| Factor | Note |
|--------|------|
| W3-B scope | OA-05-03 Spatie states + `RequestLifecycleCommandAdapter` only |
| Approval path | Still Workflow → `RequestApprovalCommandBridge` + string `withStatus` |
| Baseline red suite | Remains known-fail for full suite; Wave 3 does not re-baseline to green |
| Risk | Concurrent WF cutover work may still surface transition exceptions unrelated to OA-05-03 |

---

## Mitigation

- Scoped tests: `RequestPostApprovalLifecycleTest`, `RequestTransitionMatrixTest` (OA-05-03 rows), `RequestLifecycleHandoffTest` (adapter persistence)
- Do not expand W3-B into WP-WF-04 cutover without Lead `STOP/GO`
