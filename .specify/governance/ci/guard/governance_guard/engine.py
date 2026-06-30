"""Governance Drift Detection Engine — read-only validator."""

from __future__ import annotations

import os
import subprocess
from dataclasses import dataclass
from pathlib import Path

from governance_guard.checks import GovernanceChecks
from governance_guard.findings import Finding, sort_findings
from governance_guard.report import build_report, write_json_report, write_markdown_summary

CORE_FILE_PATHS = {
    "authority_model": ".specify/governance/_meta/authority-model.md",
    "execution_policy": ".specify/governance/execution-policy.md",
    "enforcer": ".specify/governance/governance-enforcer.md",
    "catalog": ".specify/docs/catalog-decisions.md",
}


@dataclass
class GuardResult:
    exit_code: int
    status: str
    report: dict
    findings: list[Finding]


class GovernanceGuardEngine:
    """Stateless read-only governance consistency validator."""

    def __init__(
        self,
        repo_root: Path,
        *,
        hard_guard_mode: bool = True,
        strict_literals: bool = True,
        file_overrides: dict[str, Path] | None = None,
    ) -> None:
        self.repo_root = repo_root.resolve()
        self.hard_guard_mode = hard_guard_mode
        self.strict_literals = strict_literals
        self.file_overrides = file_overrides or {}

    def run(self, output_dir: Path, commit_sha: str | None = None) -> GuardResult:
        try:
            loaded = self._load_files()
        except FileNotFoundError as exc:
            finding = Finding(
                finding_id="f-0001",
                rule_id="ENGINE_ERROR",
                failure_type="ENGINE_ERROR",
                severity="CRITICAL",
                file=str(exc.filename or ""),
                message=str(exc),
                affected_sections=[],
            )
            report = build_report(
                findings=[finding],
                commit_sha=commit_sha or self._resolve_commit_sha(),
                hard_guard_mode=self.hard_guard_mode,
                tests_passed=[],
                tests_failed=[],
                status="FAIL",
            )
            self._write_outputs(report, output_dir)
            return GuardResult(exit_code=2, status="FAIL", report=report, findings=[finding])

        checker = GovernanceChecks(loaded, strict_literals=self.strict_literals)
        findings = checker.run_all()
        findings = self._dedupe_findings(findings)
        findings = sort_findings(findings)

        status = self._resolve_status(findings)
        exit_code = self._resolve_exit_code(status, findings)

        report = build_report(
            findings=findings,
            commit_sha=commit_sha or self._resolve_commit_sha(),
            hard_guard_mode=self.hard_guard_mode,
            tests_passed=sorted(checker.tests_passed),
            tests_failed=sorted(checker.tests_failed),
            status=status,
        )
        self._write_outputs(report, output_dir)
        return GuardResult(exit_code=exit_code, status=status, report=report, findings=findings)

    def _load_files(self) -> dict[str, dict[str, str]]:
        loaded: dict[str, dict[str, str]] = {}
        for key, relative in CORE_FILE_PATHS.items():
            path = self.file_overrides.get(key, self.repo_root / relative)
            if not path.is_file():
                raise FileNotFoundError(f"Required governance file missing: {path}", str(path))
            loaded[key] = {
                "path": self._relative_path(path),
                "content": path.read_text(encoding="utf-8"),
            }
        return loaded

    def _relative_path(self, path: Path) -> str:
        try:
            return path.relative_to(self.repo_root).as_posix()
        except ValueError:
            return path.as_posix()

    def _resolve_commit_sha(self) -> str:
        env_sha = os.environ.get("GITHUB_SHA") or os.environ.get("COMMIT_SHA")
        if env_sha:
            return env_sha
        try:
            completed = subprocess.run(
                ["git", "rev-parse", "HEAD"],
                cwd=self.repo_root,
                check=True,
                capture_output=True,
                text=True,
            )
            return completed.stdout.strip()
        except (OSError, subprocess.CalledProcessError):
            return "unknown"

    def _dedupe_findings(self, findings: list[Finding]) -> list[Finding]:
        seen: set[tuple[str, str, str, int | None]] = set()
        unique: list[Finding] = []
        for finding in findings:
            key = (finding.rule_id, finding.file, finding.message, finding.line_start)
            if key in seen:
                continue
            seen.add(key)
            unique.append(finding)
        return unique

    def _resolve_status(self, findings: list[Finding]) -> str:
        if any(f.severity == "CRITICAL" for f in findings):
            return "FAIL"
        if self.hard_guard_mode and any(f.severity == "MAJOR" for f in findings):
            return "FAIL"
        if not self.hard_guard_mode and any(f.severity == "MAJOR" for f in findings):
            allow_major = os.environ.get("ALLOW_MAJOR", "false").lower() == "true"
            if not allow_major:
                return "FAIL"
        if findings and all(f.severity == "MINOR" for f in findings):
            allow_minor = os.environ.get("ALLOW_MINOR", "false").lower() == "true"
            return "WARN" if allow_minor and not self.hard_guard_mode else "PASS"
        if findings:
            return "WARN" if not self.hard_guard_mode else "FAIL"
        return "PASS"

    def _resolve_exit_code(self, status: str, findings: list[Finding]) -> int:
        if any(f.failure_type == "ENGINE_ERROR" for f in findings):
            return 2
        if status == "PASS" or status == "SKIPPED":
            return 0
        if status == "FAIL":
            return 1
        if status == "WARN":
            return 0 if not self.hard_guard_mode else 1
        return 1

    def _write_outputs(self, report: dict, output_dir: Path) -> None:
        output_dir.mkdir(parents=True, exist_ok=True)
        write_json_report(report, output_dir / "governance-drift-report.json")
        write_markdown_summary(report, output_dir / "governance-drift-summary.md")
