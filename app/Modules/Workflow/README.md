# Workflow Module

**Posture (HD-WF-01 Option B / CD-010-A1 — 1405/04/30 \| 2026-07-21):** **ACTIVATED** for Request approval orchestration.

- Ownership split (CD-010 retained): Request owns `RequestApproval` state/history; Workflow owns transition rules, chain, routing.
- Implementation authorized under **WP-WF-01…05** (after WP-WF-00 docs registration).
- First instance: Request approval chain. Second instance: deferred, non-blocking.
- This directory remains a scaffold until WP-WF-02+ deliver domain/application/infrastructure.

See: `docs/governance/open-decisions.md` (HD-WF-01, CD-010-A1); `.specify/docs/catalog-decisions.md` (CD-010 / CD-010-A1).
