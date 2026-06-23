# Decision Freeze Log — DormSys

> Purpose: Record frozen decisions and open questions so they are not forgotten.
> This is NOT a final ADR; it is a working record.

## General Status
- Recorded on: 1405/04/02 (2026-06-23)
- Source of Truth: CONSTITUTION (active version in the repo)
- Governing principle: Until an explicit user decision is made, only
  document alignment is permitted — not architectural surgery.

## 🔒 Frozen Items (Do Not Touch)
1. **Workflow / Approval**
   - Workflow Module is not removed or relocated.
   - Services (ApprovalWorkflowService, etc.) are not modified.
   - Ownership of `RequestApproval` does not change.
2. **Constitution**
   - Remains unchanged.
3. **Internal contradiction in `سند معماری و استک فنی.md`**
   - Line 367 (RequestApproval under Request) vs.
     Line 377 (RequestApproval under Workflow) — currently untouched.

## ❓ Open Questions (Require User Decision)
- [ ] Final ownership of `RequestApproval`: under Request or Workflow?
- [ ] Resolve the internal contradiction in `سند معماری و استک فنی.md`.

## ✅ Identified Contradictions (Not Necessarily Resolved)
- Laravel version: Documents aligned to Laravel 13.
- PHP version: Constitution → PHP 8.4 | Tech Stack → PHP 8.3+ (not yet aligned).
- Module list: Requires drift review.

## Important Note for Any Model/Assistant
> Ownership of `RequestApproval` in the Constitution is **not contradictory**;
> the Constitution is silent on it. The real contradiction exists only inside
> `سند معماری و استک فنی.md`.