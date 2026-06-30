#!/usr/bin/env python3
"""CLI entry point for the DormSys governance guard."""

from __future__ import annotations

import argparse
import os
import sys
from pathlib import Path

GUARD_ROOT = Path(__file__).resolve().parent
if str(GUARD_ROOT) not in sys.path:
    sys.path.insert(0, str(GUARD_ROOT))

from governance_guard.engine import GovernanceGuardEngine  # noqa: E402


def _env_bool(name: str, default: bool) -> bool:
    raw = os.environ.get(name)
    if raw is None:
        return default
    return raw.strip().lower() in {"1", "true", "yes", "on"}


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Read-only governance drift detection for DormSys core governance files."
    )
    parser.add_argument(
        "--repo-root",
        type=Path,
        default=Path.cwd(),
        help="Repository root (default: current working directory)",
    )
    parser.add_argument(
        "--output-dir",
        type=Path,
        default=Path(".specify/governance/ci/guard/output"),
        help="Directory for governance-drift-report.json and governance-drift-summary.md",
    )
    parser.add_argument(
        "--commit-sha",
        default=None,
        help="Commit SHA for report metadata (default: GITHUB_SHA or git HEAD)",
    )
    parser.add_argument(
        "--hard-guard-mode",
        choices=("true", "false"),
        default=None,
        help="Override HARD_GUARD_MODE environment variable",
    )
    parser.add_argument(
        "--strict-literals",
        choices=("true", "false"),
        default=None,
        help="Override GOVERNANCE_GUARD_STRICT_LITERALS (default: true)",
    )
    args = parser.parse_args()

    repo_root = args.repo_root.resolve()
    hard_guard_mode = (
        args.hard_guard_mode == "true"
        if args.hard_guard_mode is not None
        else _env_bool("HARD_GUARD_MODE", True)
    )
    strict_literals = (
        args.strict_literals == "true"
        if args.strict_literals is not None
        else _env_bool("GOVERNANCE_GUARD_STRICT_LITERALS", True)
    )

    output_dir = args.output_dir
    if not output_dir.is_absolute():
        output_dir = (repo_root / output_dir).resolve()

    engine = GovernanceGuardEngine(
        repo_root,
        hard_guard_mode=hard_guard_mode,
        strict_literals=strict_literals,
    )
    result = engine.run(output_dir=output_dir, commit_sha=args.commit_sha)

    print(f"Governance guard status: {result.status} (exit {result.exit_code})")
    print(f"Report: {output_dir / 'governance-drift-report.json'}")
    print(f"Summary: {output_dir / 'governance-drift-summary.md'}")
    if result.findings:
        print(f"Findings: {len(result.findings)}")
        for finding in result.findings[:5]:
            print(f"  - [{finding.severity}] {finding.rule_id}: {finding.message}")

    return result.exit_code


if __name__ == "__main__":
    raise SystemExit(main())
