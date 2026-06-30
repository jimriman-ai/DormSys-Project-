"""Report generation for governance guard."""

from __future__ import annotations

import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from governance_guard.findings import Finding, sort_findings


def build_report(
    *,
    findings: list[Finding],
    commit_sha: str,
    hard_guard_mode: bool,
    tests_passed: list[str],
    tests_failed: list[str],
    status: str,
) -> dict[str, Any]:
    ordered = sort_findings(findings)
    critical = sum(1 for f in ordered if f.severity == "CRITICAL")
    major = sum(1 for f in ordered if f.severity == "MAJOR")
    minor = sum(1 for f in ordered if f.severity == "MINOR")

    return {
        "schema_version": "1.0.0",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "commit_sha": commit_sha,
        "status": status,
        "hard_guard_mode": hard_guard_mode,
        "summary": {
            "critical": critical,
            "major": major,
            "minor": minor,
            "tests_passed": sorted(tests_passed),
            "tests_failed": sorted(tests_failed),
        },
        "findings": [finding.to_dict() for finding in ordered],
    }


def write_json_report(report: dict[str, Any], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")


def write_markdown_summary(report: dict[str, Any], output_path: Path) -> None:
    summary = report["summary"]
    findings: list[dict[str, Any]] = report["findings"]

    lines = [
        "# Governance Guard Summary",
        "",
        "| Field | Value |",
        "| --- | --- |",
        f"| Status | {report['status']} |",
        f"| Commit | `{report['commit_sha']}` |",
        f"| HARD_GUARD_MODE | {str(report['hard_guard_mode']).lower()} |",
        f"| Critical | {summary['critical']} |",
        f"| Major | {summary['major']} |",
        f"| Minor | {summary['minor']} |",
        "",
    ]

    if findings:
        lines.extend(
            [
                "## Failed rules",
                "",
                "| Rule | Severity | File | Description |",
                "| --- | --- | --- | --- |",
            ]
        )
        for finding in findings:
            lines.append(
                f"| {finding['rule_id']} | {finding['severity']} | "
                f"{finding['file']} | {finding['message']} |"
            )
        lines.append("")
    else:
        lines.extend(["## Failed rules", "", "_None._", ""])

    lines.extend(
        [
            "## Tests",
            "",
            f"- Passed: {', '.join(summary['tests_passed']) if summary['tests_passed'] else '(none)'}",
            f"- Failed: {', '.join(summary['tests_failed']) if summary['tests_failed'] else '(none)'}",
            "",
            "## Remediation",
            "",
        ]
    )

    if findings:
        for index, finding in enumerate(findings[:10], start=1):
            hint = finding.get("remediation_hint") or finding["message"]
            lines.append(f"{index}. **{finding['rule_id']}** ({finding['severity']}): {hint}")
    else:
        lines.append("No governance drift detected. Guard enforces consistency only; it does not grant authority.")

    lines.append("")
    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text("\n".join(lines), encoding="utf-8")
