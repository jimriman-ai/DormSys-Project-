# Cursor Prompt Protocol v1

Status: DECIDED (GOV-PP-01, 2026-07-18)
Authority: Lead (Human-only Decision Authority)
Scope: All Cursor interactions from Sprint C onward; applies to Sprint B Phase 3 closure.

Core Model:
1 Prompt = 1 Phase / Decision Cluster

Required Flow:
Discovery → Decision Register → Controlled Execution → Verification → Closure

Prompt Size Rule:

- One shared exit goal
- Normally maximum 5 dependent tasks
- Larger execution clusters require explicit Lead authorization
- File count is not the size metric; dependency and decision complexity are

Forbidden:

- git operations
- branch/history assumptions
- SHA inference
- architecture changes without authorization
- silent scope expansion
- re-litigating DECIDED items

Discovery:
Before changes:

- inspect real working tree
- collect evidence
- classify gaps
No evidence: NOT FOUND

Decision Gate:
If human decision required, Cursor must:

- explain conflict
- provide options
- recommend one option
Cursor must STOP. Cursor cannot decide.

Execution:
After authorization:

- whitelist files
- minimal changes
- no unrelated cleanup
- no migrations unless authorized

Verification:
Required:

- artifact verification
- regression verification
- scope verification
For modified security/negative tests — Mutation sanity check required:
- temporarily invert expected behavior
- confirm test fails
- restore original assertion

STOP Rule:
STOP means: report current state, wait for human review.
Cursor must not:

- create a second wave
- bypass missing decisions
- continue with alternative solutions

Evidence Rule:
Every final claim requires path:line format.
No narrative-only verification accepted.

Final Report:
STATUS: DONE / BLOCKED / PARTIAL
Changes: path:line + reason
Verification: tests + evidence
Remaining decisions: human actions only
