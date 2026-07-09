# Development Performance Hygiene Guide

## Status

Active team operating rule.

## Purpose

This document defines the minimum environment-validation rules required before judging UI responsiveness in development.

Its purpose is to prevent false performance diagnosis caused by invalid development conditions.

---

## Core Principle

Application performance must not be judged from a broken or contaminated development profile.

Before describing a UI flow as “slow,” verify that the local runtime conditions are suitable for measurement.

---

## Rule Language

The terms below are normative:

- **Must** = mandatory rule
- **Should** = recommended practice
- **May** = optional action

---

## Mandatory Rules

### 1. Frontend assets must be valid

Required frontend assets **must** exist and resolve correctly before evaluating full-page responsiveness.

CSS/JS asset 404s **must not** be treated as evidence of application-level slowness.

#### Hard stop

Do not assess page-load responsiveness while frontend assets are missing, failing, or unresolved.

---

### 2. Xdebug must be off for normal UI responsiveness checks

Xdebug **must** be disabled during routine UI responsiveness validation.

#### Exception

Xdebug **may** be enabled only when:

- debugging a specific issue
- intentionally inspecting runtime behavior
- performing deliberate debugging/profiling work

Xdebug-enabled measurements **must not** be treated as default evidence of product responsiveness.

---

### 3. Telescope must be off for normal UI responsiveness checks

Telescope **must** be disabled during routine UI responsiveness validation.

#### Exception

Telescope **may** be enabled only for:

- intentional instrumentation
- diagnostics
- explicit inspection sessions

Telescope-on observations **must not** be treated as neutral baseline measurements.

---

### 4. Full-page issues must be separated from warm-path behavior

The following **must** be evaluated separately:

- full-page delivery behavior
- frontend asset delivery behavior
- development-tooling overhead
- application read/write path cost
- database time

These categories **must not** be collapsed into a single diagnosis without evidence.

---

### 5. Broken dev baselines must not trigger application optimization

No performance refactor, query rewrite, component change, or architecture adjustment **may** be authorized until:

- the measurement profile is valid
- the slowdown is reproducible
- environment-induced causes have been checked
- evidence supports an application-level bottleneck

---

## Invalid Measurement Examples

The following are invalid bases for application-level optimization decisions:

- judging full-page load while CSS/JS assets are 404ing
- judging UI responsiveness with Xdebug enabled by default
- judging UI responsiveness with Telescope active by default
- opening a performance track from a single anomalous run
- treating user perception alone as sufficient implementation evidence
- diagnosing database slowness without isolating DB timings
- attributing full-page delay to feature code before verifying asset delivery

---

## Recommended Validation Checklist

Before evaluating UI slowness, the investigator **should** verify:

- [ ] frontend assets are present
- [ ] CSS/JS asset delivery is valid
- [ ] no severe asset 404s are occurring
- [ ] Xdebug is disabled unless intentionally needed
- [ ] Telescope is disabled unless intentionally needed
- [ ] the measurement scenario is repeatable
- [ ] warm-path behavior is distinguished from cold/full-page behavior
- [ ] user perception is compared against timing evidence
- [ ] current environment state is documented

---

## When a Performance Investigation Is Appropriate

A formal performance investigation **should** be opened when:

- slowness remains reproducible after environment cleanup
- valid frontend assets are present
- development-tooling overhead is not contaminating the result
- repeated measurements indicate a stable bottleneck
- the issue affects product confidence or delivery planning

---

## When a Performance Feature Contract Is Appropriate

A dedicated performance feature contract **may** be opened only when all of the following are true:

1. a clean baseline exists
2. the slowdown is reproducible
3. the bottleneck is application-level
4. likely optimization targets are identifiable
5. change scope can be governed explicitly

If these conditions are not met, implementation work **must not** begin.

---

## Practical Working Mode for DormSys

For normal UI validation work, the team **should** use this default posture:

- keep frontend assets built and available
- keep Xdebug off by default
- keep Telescope off by default
- re-enable tooling intentionally, not passively
- document the measurement profile when raising performance concerns

---

## Escalation Rule

If slowness persists after hygiene rules are satisfied, the investigator **must**:

1. capture a reproducible scenario
2. collect timings
3. document environment state
4. create a technical baseline
5. determine whether a separate performance contract is justified

---

## Summary

The required order is:

1. verify environment
2. measure cleanly
3. diagnose
4. authorize changes only if justified

The team must avoid:

- optimizing from broken dev baselines
- confusing asset failures with application bottlenecks
- treating instrumentation overhead as product behavior
- escalating to implementation without evidence
