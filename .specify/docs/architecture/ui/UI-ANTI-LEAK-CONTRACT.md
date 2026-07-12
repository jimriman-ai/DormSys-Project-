# UI Anti-Leak Contract

## DormSys — Enterprise Workflow System

**Status:** Governing Contract  
**Authority Level:** Mandatory  
**Applies To:** Blade, Livewire Components, Alpine.js interactions, UI-facing DTO/ViewModel consumption  
**Owner:** Architecture  
**Scope:** PR-N6 Request Surface, PR-N7 Execution Visibility, PR-N8 Terminal State Surfaces

---

## 1. Purpose

This contract defines the architectural boundary between the DormSys presentation layer and backend authority layers.

Its purpose is to ensure that:

- business meaning remains authoritative in backend layers
- workflow semantics do not leak into UI code
- authorization meaning is not reconstructed in the presentation layer
- the UI remains flexible for interaction and rendering without becoming a second source of truth

DormSys uses a **backend-authoritative, interaction-capable UI model**.

---

## 2. Core Principle

## Backend Authority, UI Interaction Freedom

The backend is the sole authority for:

- business rules
- workflow transitions
- lifecycle semantics
- authorization decisions
- execution readiness
- terminal capability
- validation outcomes
- state meaning

The UI is responsible for:

- input capture
- rendering structure
- local interaction state
- UX behavior
- transport of user intent
- visual feedback
- presentational formatting that does not alter business meaning

This contract does **not** forbid UI logic.  
It forbids **business-authoritative UI logic**.

---

## 3. Architectural Rule

## UI May Interact Freely, But Must Not Define Business Meaning

UI code may be rich in interaction, but must remain non-authoritative in business semantics.

### Allowed UI responsibilities

- binding user input
- managing local screen state
- modals, tabs, toggles, filters, sorting controls
- loading and progress indicators
- rendering backend-provided labels, statuses, and capabilities
- forwarding user intent to backend actions/services
- surfacing validation and execution outcomes
- presentational formatting and view-only transformation

### Forbidden UI responsibilities

- determining what a domain state means
- deriving lifecycle or workflow status from raw fields
- reconstructing action capability from partial domain data
- deciding execution readiness
- mirroring authorization rules as a source of truth
- orchestrating business workflows
- mutating domain state directly
- acting as an alternate interpreter of backend truth

---

## 4. Operating Model

All interactive flows should follow this model:

**User Action**  
→ **UI Adapter (Blade / Livewire / Alpine interaction)**  
→ **Application Service / Action / Command / Query**  
→ **Backend Authorization / Validation / Domain Logic**  
→ **DTO / ViewModel / Capability Payload / Result**  
→ **UI Render / Refresh**

The UI owns the experience of interaction.  
The backend owns the meaning of state and action.

---

## 5. Capability-First Contract

Where the UI needs to know whether an action is available, the preferred architecture is a backend-provided capability contract.

Example:

```json
{
  "can_submit": true,
  "can_cancel": false,
  "can_check_in": false,
  "status_code": "open",
  "status_label": "Open"
}

UI should prefer consuming:

- capability flags
- backend-defined status codes
- backend-defined labels
- DTOs/ViewModels shaped for rendering
- execution snapshots
- backend-produced validation and error payloads

UI should avoid reconstructing meaning from raw persistence fields.

---

## 6. Input Surface Rules

### 6.1 Raw Input Ownership

UI owns user input capture and transport.

Allowed:

- form state
- syntactic normalization
- browser-friendly formatting
- UX-oriented client-side assistance

Forbidden:

- semantic derivation of business flags from input combinations
- converting temporary UI state into business interpretation
- embedding hidden rule decisions in UI methods

---

### 6.2 Validation Boundary

Frontend validation may assist the user with:

- required-field hints
- format checking
- input masks
- primitive type guidance
- immediate UX feedback

Backend remains authoritative for:

- eligibility
- lifecycle windows
- business invariants
- transition legality
- capacity and conflict checks
- policy outcomes

UI assistance must never become business authority.

---

### 6.3 Error Propagation

UI should surface backend validation and execution results clearly and consistently.

UI may:

- render messages
- group errors
- display notifications
- bind errors to fields

UI may not:

- reinterpret backend failure into a separate local rule system
- suppress backend meaning through semantic remapping

---

## 7. Execution Surfaces

Execution-facing screens, especially in PR-N7, are visibility and triggering surfaces.

UI may:

- display execution snapshots
- show backend-computed readiness or capability
- trigger dry-run or execute actions
- refresh and display outcomes
- provide non-semantic progress feedback

UI may not:

- compute execution readiness from timestamps or raw states
- infer whether execution is safe or valid
- build execution meaning from partial backend data
- locally reclassify execution outcomes

If execution availability matters, backend must express it explicitly.

---

## 8. Terminal State Surfaces

For PR-N8 and other terminal-state interfaces:

UI may:

- show final state
- show backend-provided action availability
- trigger terminal actions
- display returned outcomes

UI may not:

- infer terminal eligibility from raw state combinations
- compute whether a terminal action is allowed
- interpret terminal capability as local UI authority

Terminal capability should be explicit.

---

## 9. Livewire Component Contract

Livewire components are interaction adapters with rendering responsibilities.

They may:

- hold form state
- manage local UI state
- call backend actions/services
- request refresh
- emit UI-only events
- display DTO/ViewModel fields
- forward backend outcomes

They may not:

- own domain rules
- orchestrate business workflows across multiple steps
- define authorization meaning
- perform aggregate mutation directly
- create authoritative status semantics
- become a second business layer

Thin methods are preferred, but expressive interaction code is allowed when it remains non-authoritative.

---

## 10. Smells and Approved Alternatives

### 10.1 Calculated Status Smell

Forbidden:

php
public function getStatusLabelProperty(): string
{
if ($this->request->starts_at > now()) {
return 'Upcoming';
}

if ($this->request->ends_at < now()) {
return 'Closed';
}

return 'Open';
}

Why:
The UI is defining lifecycle meaning.

Preferred:
Receive `status_code` and `status_label` from backend.

---

### 10.2 Authorization Mirroring Smell

Risky / usually forbidden:

php
if (auth()->user()->hasRole('housing_manager')) {
$this->canApprove = true;
}

Why:
This recreates authority interpretation inside UI.

Preferred:
Use backend capability flags, or delegate checks through the proper backend boundary.

---

### 10.3 Workflow Orchestration Smell

Forbidden:

php
public function approve()
{
DB::transaction(function () {
$this->service->validate($this->id);
$this->service->reserveCapacity($this->id);
$this->service->approve($this->id);
});
}

Why:
The component is acting as an application service.

Preferred:
Delegate to a single backend action/command.

---

### 10.4 Derived Capability Smell

Forbidden:

php
$this->canCheckIn =
$allocation->status === 'assigned'
&& $allocation->start_date <= now()
&& $allocation->student !== null;

Why:
Capability is being reconstructed in UI.

Preferred:
Consume `can_check_in` from backend.

---

## 11. Allowed Presentational Logic

The following are explicitly allowed when they do not redefine business meaning:

- modal open/close state
- tab selection
- local panel state
- client-side filtering as a view convenience
- sort and search transport state
- pagination controls
- loading indicators
- focus management
- accessibility behavior
- responsive layout logic
- visual formatting
- non-authoritative derived display such as truncation, grouping, highlighting, or ordering

Presentational logic is allowed.  
Business-authoritative logic is not.

---

## 12. Data Shape Guidance

Preferred render payloads include:

- identifiers
- display labels
- backend-defined status codes
- backend-defined status labels
- capability flags
- action availability
- execution snapshots
- backend-prepared display fields where useful

Example:

php
[
'id' => 'REQ-1021',
'status_code' => 'open',
'status_label' => 'Open',
'can_submit' => true,
'can_withdraw' => false,
'can_check_in' => false,
]

A capability-first payload is preferred over forcing UI to infer from raw entities.

---

## 13. Testing Requirements

Every UI PR must include tests that protect both architectural boundaries and practical behavior.

### 13.1 Access Control
Verify:
- guest behavior
- unauthorized behavior
- authorized access

### 13.2 Render Integrity
Verify:
- DTO/ViewModel fields render correctly
- no unintended semantic mutation in rendering

### 13.3 Error Propagation
Verify:
- backend validation surfaces correctly
- backend execution failures surface clearly
- UI does not silently reinterpret failure meaning

### 13.4 Surface Discipline
Verify:
- no transactions in component methods
- no direct domain mutation in component methods
- no hidden workflow orchestration in UI
- no authoritative state derivation in UI methods/properties

---

## 14. Review Standard

A UI PR should be blocked if it causes the UI to become a second source of business truth.

Reviewers should ask:

- Is this UI code deciding business meaning?
- Is this capability inferred instead of provided?
- Is authorization being mirrored?
- Is workflow orchestration leaking outward?
- Is the UI remaining flexible without becoming authoritative?

The goal is not rigid UI.  
The goal is authoritative backend semantics with safe UI freedom.

---

## 15. Non-Negotiables

1. Backend remains the source of business truth.  
2. UI may be interaction-rich, but not business-authoritative.  
3. Capability should be explicit where action availability matters.  
4. Workflow orchestration belongs outside the UI layer.  
5. Architectural leakage is treated as a defect.  

---

## 16. Delivery Alignment

### PR-N6 — Request Surface
UI captures input, provides UX assistance, and forwards intent without embedding request semantics.

### PR-N7 — Execution Visibility
UI displays backend execution state and triggers backend actions without deciding execution meaning.

### PR-N8 — Terminal State Surfaces
UI consumes final-state truth and explicit capability without inferring terminal authority.

---

## 17. Final Rule

If UI code is answering:

**“What does this state mean in business terms?”**

it is in the wrong layer.

If UI code is answering:

**“How should this interaction feel and render?”**

it is probably in the right one.

---

## 18. Status

This contract is active immediately upon adoption and applies to all new UI work and all touched UI code during refactor or enhancement.
