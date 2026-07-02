# Governance Decision Tree
Status: Operational Routing Control
Purpose: Prevent stage invention and route work using explicit governance checkpoints only
Scope: Spec lifecycle from planning artifacts to authorized execution

## 1. Control Principle

This decision tree is a routing mechanism.
It is not a source of authority.
It must be used only with canonical authority verification.

## 2. Decision Tree
```text
START
|
|-- Is the subject clearly identified?
|    |-- No -> HALT: Undefined subject
|    |-- Yes
|
|-- Do planning artifacts exist?
|    |-- No -> Continue planning work only
|    |-- Yes -> Continue
|
|-- Has Design Approval been explicitly granted by the canonical authority source?
|    |-- No -> HALT: Design not approved
|    |-- Yes -> Continue
|
|-- Has Implementation Authorization been explicitly granted by the canonical authority source?
|    |-- No -> HALT: Implementation not authorized
|    |-- Yes -> Continue
|
|-- Is authorization full or partial?
|    |-- Full -> Continue to Batch Entry Check
|    |-- Partial -> Verify blocked-scope, blocking-reason, owner, exit condition
|          |-- Any missing -> HALT: Invalid partial authorization record
|          |-- Valid -> Continue only within authorized-scope
|
|-- Batch Entry Check
|    |-- Is next batch permitted by the execution policy?
|    |-- No -> HALT: Batch not permitted
|    |-- Yes -> Start Batch
|
|-- End of Batch
|    |-- Execute defined Review Gate
|    |-- Human approval present?
|          |-- No -> HALT: Next batch blocked pending human approval
|          |-- Yes -> Continue to next permitted batch
|
END

## 3. Prohibited Insertions

The following may not be inserted into the routing path unless canonically adopted:
- readiness stages
- re-approval stages
- dependency classification phases
- freeze-readiness checks
- AI-suggested governance states
- ad hoc architecture checkpoints

## 4. Interpretation Rule

If a proposed routing node is not found in canonical governance, it is non-canonical and must not be used.


**6. `governance-drift-control-model.md`**
```md
# Governance Drift Control Model
Status: Cross-Layer Control Framework
Purpose: Detect and stop stage drift, authority drift, and review-loop drift
Scope: All governance-affecting interactions across Program, Design, Authorization, and Execution

## 1. Drift Types

### D-001: Stage Drift
Use of unofficial lifecycle stages, invented readiness states, or repeated non-canonical labels

### D-002: Authority Drift
Use of descriptive artifacts, local notes, mirror docs, or AI summaries as implied authority

### D-003: Layer Drift
Reintroduction of upstream governance logic into downstream execution without formal trigger

### D-004: Review Drift
Treating review activity as if it were automatically a gate or approval mechanism

### D-005: Blocker Drift
Naming blockers without explicit record, owner, blocked scope, or exit condition

### D-006: Rule Drift
Using proposed guidance as if it were adopted governance

## 2. Detection Triggers

Flag drift immediately if any of the following appears:
- a new phase name not found in canonical governance
- execution permission justified by presence of files
- scope inferred from task structure
- review used as stop/go logic without gate definition
- blocker named without explicit record fields
- multiple competing authority sources
- AI-generated term reused as governance label
- proposal used without classification level

## 3. Response Model

When drift is detected:

1. Stop authority-dependent progression
2. Classify drift type
3. Run reality check
4. Identify canonical authority source
5. Mark unsupported claims as not grounded
6. Route valid findings into retrospective if needed
7. Extract rules only after incident is recorded
8. Require human adoption before operationalizing any new rule

## 4. Severity Levels

- S1: Advisory Drift
  - no authority effect yet
- S2: Routing Drift
  - influences sequencing or readiness logic
- S3: Authority Drift
  - affects approval, permission, or blocker treatment
- S4: Governance Integrity Drift
  - creates or mutates governance behavior without authority

## 5. Required Output

Each drift case must record:
- Drift ID
- Drift Type
- Severity
- Trigger Observed
- Canonical Evidence
- Unsupported Claim
- Immediate Control Action
- Follow-up Record Link
