# spec05 — request-management — Batch Map
# Execution order = batch number order (B1→B9).
# B6/B7 (Wave 1A) intentionally run before B8/B9 (1B/1C): MVP gate (T052) closes Wave 1A first.

B1  SCHEMA             T008-T012   Wave1A
B2  FOUNDATION         T013-T020   Wave1A
B3  FEATURE/US1        T021-T026   Wave1A
B4  FEATURE/US2        T027-T030   Wave1A
B5  FEATURE/US3        T031-T036   Wave1A
B6  INTEGRATION        T045-T049   Wave1A
B7  POLISH             T050-T052   Wave1A   (MVP gate)
B8  FEATURE/US4        T037-T039   Wave1B   (gate: spec03 US3 authorized)
B9  FEATURE/US5+US6    T040-T044   Wave1C

# Done before B1: T001-T007 (Setup + Foundational prereqs), all [x].
# Phases 2–10 map to batches as above; batch order respects wave gates, not phase sequence.
