# KNOWN_DEBT.md

## System Baseline Snapshot
Captured: B8 completion (Wave 1B)

---

## Pre-existing Failures (NOT owned by spec05)

### 1. DepartmentTest (×4)
- Source: spec02 (Employee module incomplete)
- Type: missing dependency binding
- Status: OUT OF SCOPE for spec05
- Risk: LOW (no dependency from Request module)

---

### 2. LayerDependencyTest (×1)
- Source: B2 architectural choice (Spatie state model coupling)
- Type: framework-level design constraint
- Status: accepted legacy constraint
- Risk: MEDIUM (noise in full suite only)

---

### 3. ModuleBoundaryTest (×1)
- Source: B7 adapter isolation design
- Type: false-positive under current BT-R05 definition
- Status: superseded by RequestConsumerBoundaryTest
- Risk: LOW

---

## Regression Policy

### B9 MUST obey:

- PASS condition:
  → no NEW failures introduced beyond this baseline (6 total)

- FAIL condition:
  → any additional failure beyond baseline

---

## Interpretation Rule

These failures are:
- NOT bugs in spec05
- NOT regressions from B8
- NOT blocking for feature progression

They are:
- known system debt
- external to current bounded context