# CI Stability Guard (Engineering Guardrail)

## Purpose

This guardrail reduces CI instability risk for structural changes by enforcing
an audit-first workflow before code modification.

It governs **how to change safely**, not **what architecture is allowed**.

## Scope (In Scope)

This guardrail applies to structural changes, including:

- namespace migrations
- class relocation
- dependency boundary shifts
- service container/binding impact
- PSR-4/autoload-affecting changes
- changes to `composer.json` autoload configuration

## Out of Scope

This guardrail does not apply to:

- normal bug fixes
- test-only changes
- documentation updates
- formatting-only changes
- isolated business logic updates without structural impact

## Core Principle: Evidence Before Change

No code changes until:

1. failing CI gate is identified
2. exact root cause is confirmed
3. minimal correction plan is defined

## Operating Model

### Phase 1 — Audit

Required checks:

- Git tracking integrity (move/rename staged correctly)
- Autoload/PSR-4 integrity
- stale reference search
- PHPStan blocker classification
- architecture boundary verification
- local vs CI environment delta review

### Phase 2 — Apply Patch

Only after audit approval.
Fixes must be minimal and surgical.
No suppression-based green CI.

## Pre-Push Verification (Structural Changes)

Run:

```bash
git status
composer dump-autoload --strict-psr
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test

## Expected Outcome
Risk of CI failures caused by structural changes and environment deltas becomes
a process that is identifiable, reviewable, and controllable.


---

## 3) گام‌های اجرای دقیق (Operational Steps)

1. ایجاد دو فایل بالا در مسیرهای مشخص  
2. Commit جداگانه برای Policy (شفاف و مستقل)  
3. از این پس، فقط هنگام Structural Change این فرمان آغازین را به Cursor بدهید:

```text
Apply DormSys CI Stability Guard.
This is a structural change.
Start in AUDIT MODE.
Do not modify code until audit report is approved.
Wait for: APPLY PATCH.
