# Mutation Security Baseline Freeze

Effective: 2026-07-06. This note is final. It is not a roadmap and must not be extended.

## Baseline Declaration

DormSys mutation enforcement is performed through the existing implemented application/runtime model: authoritative enforcement at adopted mutation action entry via MPEP and domain-local gates; mutation execution requires an approved principal context (explicit user principal or explicit approved system actor) or fails closed; system actor usage is limited to explicitly approved runtime paths (`MutationPrincipalContext::runJobAsSystem` on lottery background jobs). No additional mutation-security architecture is required for the project's actual operating context. Future mutation-security changes are permitted only for a confirmed defect, confirmed bypass, confirmed operational ambiguity, or a deployment-context change that materially alters threat assumptions.

## Threat Context

DormSys is an internal organizational application operated in a controlled intranet context. It is not a public SaaS platform, not a hostile multi-tenant environment, and not a high-assurance target. Mutation security is sufficient when it reliably prevents accidental internal misuse, unauthorized mutation through incorrect application flow, principal ambiguity in runtime execution, programmer-introduced mutation bypass, and uncontrolled background mutation execution.

## Freeze Rule

No new mutation-security capability, layer, rule-set, or architectural control may be added based on theoretical concern alone. Any future change must be triggered by a confirmed defect, confirmed exposure, concrete operational ambiguity, or a material change in deployment threat assumptions. This document is not a development plan.
