# ARCHITECTURE

## DormSys — Enterprise Workflow System

This document defines the top-level architectural operating model for DormSys.

It is the primary entry point for governing architecture contracts.

---

## 1. Architectural Position

DormSys is a governed enterprise workflow system built on explicit architectural boundaries.

The system is designed for:

- domain-aligned workflow execution
- backend-authoritative business semantics
- interaction-capable Rich  UI
- operational clarity
- production-safe evolution
- governance by enforceable contracts

DormSys is not UI-driven in business meaning.  
It is backend-authoritative with a flexible presentation surface.

---

## 2. Core Principle

## Backend Authority, UI Interaction Freedom

DormSys follows a strict authority model.

### Backend owns

- business rules
- workflow transitions
- lifecycle meaning
- authorization meaning
- execution readiness
- terminal capability
- validation outcomes

### UI owns

- input capture
- rendering structure
- interaction behavior
- local view state
- presentational formatting
- non-authoritative UX responsiveness

The architectural boundary is not “no UI logic”.  
The real boundary is “no business-authoritative UI logic”.

---

## 3. Governing Contracts

The following are governing architecture contracts.

### Active

- `docs/architecture/ui/UI-ANTI-LEAK-CONTRACT.md`

All presentation-layer work must comply with active governing contracts.

Future governing contracts may define:

- application service boundary rules
- authorization authority rules
- read-model and presenter contracts
- workflow execution contracts
- integration boundary contracts

---

## 4. Layer Responsibilities

### Domain

Owns:

- invariants
- core rules
- domain behavior
- domain-level truth

### Application

Owns:

- use-case execution
- orchestration
- transaction boundaries
- coordination of authorization, validation, and domain work
- command/query handling
- DTO, ViewModel, and capability output shaping where appropriate

### Infrastructure

Owns:

- persistence
- framework integration
- messaging and queue integration
- external adapters
- technical implementations

### UI / Presentation

Owns:

- interaction flow
- rendering
- local screen state
- UX behavior
- transport of user intent
- presentational transformation without business reinterpretation

UI does not own:

- business semantics
- lifecycle interpretation
- capability inference as authority
- workflow orchestration
- domain mutation authority

---

## 5. UI Governance

The UI layer is governed by:

`docs/architecture/ui/UI-ANTI-LEAK-CONTRACT.md`

This contract protects backend authority while preserving healthy UI flexibility.

UI code becomes non-compliant when it turns presentation concerns into business-authoritative interpretation.

---

## 6. Capability-Oriented Delivery

Where UI action availability matters, backend should prefer explicit capability delivery.

Examples:

- `can_submit`
- `can_cancel`
- `can_approve`
- `can_check_in`
- `status_code`
- `status_label`

This reduces leakage, improves clarity, and keeps UI implementation fast without making it authoritative.

---

## 7. Delivery Direction

Current delivery direction emphasizes:

- hardened backend truth
- explicit contracts
- thin but capable UI
- visibility-first workflow execution
- scalable enforcement against architecture drift

UI should remain easy to evolve, but must not become a second authority source.

---

## 8. Enforcement Model

Architecture is enforced through:

- governing documents
- contributor rules
- code review
- PR checklists
- regression tests
- static analysis
- CI gates

The goal is sustainable architectural behavior, not documentation theater.

---

## 9. Contribution Rule

Before placing logic into Blade, Livewire, controllers, UI helpers, or Alpine code, ask:

### A. Is this interaction or presentation logic?

If yes, UI is a valid place.

### B. Is this defining business meaning, capability, readiness, or workflow legality?

If yes, it belongs in backend authority.

This question is mandatory for all presentation-layer changes.

---

## 10. Non-Negotiables

- Backend remains the sole source of business truth
- UI remains interaction-capable but non-authoritative in business meaning
- Capability should be explicit where action availability matters
- Architectural leakage is treated as a defect
- Governance must be enforceable, not merely documented

---

## 11. Reading Order

Recommended contributor reading order:

1. `ARCHITECTURE.md`
2. `docs/architecture/ui/UI-ANTI-LEAK-CONTRACT.md`

If implementation convenience conflicts with a governing contract, the governing contract wins.
