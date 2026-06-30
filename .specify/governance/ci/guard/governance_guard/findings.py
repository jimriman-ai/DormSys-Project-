"""Finding model and stable sort order for governance guard output."""

from __future__ import annotations

from dataclasses import dataclass, field
from typing import Any

SEVERITY_ORDER = {"CRITICAL": 0, "MAJOR": 1, "MINOR": 2}

FAILURE_TYPES = frozenset(
    {
        "AUTHORITY_DRIFT",
        "CASE_C_MISCLASSIFICATION",
        "NOMINATION_AUTHORITY_LEAKAGE",
        "PRECEDENCE_MISMATCH",
        "CATALOG_MAP_VIOLATION",
        "ENGINE_ERROR",
        "EVAL_AMBIGUOUS",
    }
)


@dataclass
class Finding:
    finding_id: str
    rule_id: str
    failure_type: str
    severity: str
    file: str
    message: str
    affected_sections: list[str] = field(default_factory=list)
    line_start: int | None = None
    line_end: int | None = None
    description: str = ""
    remediation_hint: str = ""
    related_files: list[str] = field(default_factory=list)

    def __post_init__(self) -> None:
        if not self.description:
            self.description = self.message

    def to_dict(self) -> dict[str, Any]:
        payload: dict[str, Any] = {
            "finding_id": self.finding_id,
            "rule_id": self.rule_id,
            "failure_type": self.failure_type,
            "severity": self.severity,
            "file": self.file,
            "affected_sections": self.affected_sections,
            "message": self.message,
            "description": self.description,
        }
        if self.line_start is not None:
            payload["line_start"] = self.line_start
        if self.line_end is not None:
            payload["line_end"] = self.line_end
        if self.remediation_hint:
            payload["remediation_hint"] = self.remediation_hint
        if self.related_files:
            payload["related_files"] = self.related_files
        return payload


def sort_findings(findings: list[Finding]) -> list[Finding]:
    return sorted(
        findings,
        key=lambda f: (
            SEVERITY_ORDER.get(f.severity, 99),
            f.rule_id,
            f.file,
            f.line_start or 0,
        ),
    )
