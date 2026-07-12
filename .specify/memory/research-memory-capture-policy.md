# Research Memory Capture Policy

## Execution Directive

Capture is triggered strictly AFTER a governed execution ends.

## Capture Checklist (Post-Execution)

1. **Observation Value:** Is this log worth someone's time during a Retrospective? If no, stop.
2. **Template Validation:** Populate `prompt_id`, `work_item`, `stage`, `tags`, and `summary`.
3. **Isolation Check:** Ensure summary contains factual observation, not instructions for the next prompt.
4. **Tag Compliance:** Verify selected tags exist in `tags.md`.

## Constraints

- Forbidden: Referencing previous logs as instructions for current prompt.
- Forbidden: Over-tagging for the sake of categorization.
- Forbidden: Automating knowledge promotion based on log content.

## Error Handling

Logging is a side-effect. Errors in the logging process must not prevent the completion or acceptance of the core development task.
