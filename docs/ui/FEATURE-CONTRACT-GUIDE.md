# FEATURE-CONTRACT-GUIDE

Version: 1.0.0
Status: FROZEN (v1)
Owner: DormSys Engineering

## 1. Structure Requirements

Every Feature Contract MUST adhere to this structure:

### A. Metadata

- `feature_name`, `module`, `version`.

### B. View Model (Read Model)

Defines the data contract for the UI projection.

- `fields`: {name, type, source}
- `filters`: {key, type}
- `sortable`: [list_of_keys]

### C. Mutations & Backend Mapping

Explicit link between UI action and Backend service.

- `actions`: List of public methods.
- `mapping`: {action_name: "App\\Path\\To\\BackendAction"}

### D. State Separation

- **UI States:** (e.g., loading, empty, ready, error).
- **Domain/Workflow States:** (e.g., draft, submitted, approved).

### E. Permission Semantics

- `view_permission`, `action_permissions` (mapped to Gates).

### F. UX & Feedback

- `loading_behavior`, `success_message`, `error_handling_strategy`.

### G. Open Questions

- Any unresolved ambiguity that requires Human Intervention.

## 2. Example Template (YAML)

```yaml
feature_name: request_list
view_model:
  columns: [id, title, status, created_at]
  filters: [status, user_id]
actions:
  refresh:
mapping: "App\Actions\Request\ListRequestsAction"
permission: "view-requests"
states:
  ui: [loading, idle, error]
  workflow: [draft, submitted, approved]
ux:
  loading: "skeleton_table"
  error: "toast_notification"
open_questions:
  - "Should we allow bulk export in v1?"

## 3. Validation Checklist
- [ ] No Business logic in UI?
- [ ] All states defined?
- [ ] Backend actions mapped?
- [ ] Permissions explicit?
- [ ] Open Questions addressed?


