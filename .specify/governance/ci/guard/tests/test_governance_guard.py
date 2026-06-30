"""Tests for the governance guard engine."""

from __future__ import annotations

import json
import sys
import tempfile
import unittest
from pathlib import Path

GUARD_ROOT = Path(__file__).resolve().parents[1]
if str(GUARD_ROOT) not in sys.path:
    sys.path.insert(0, str(GUARD_ROOT))

from governance_guard.engine import CORE_FILE_PATHS, GovernanceGuardEngine  # noqa: E402

REPO_ROOT = Path(__file__).resolve().parents[5]
FIXTURES = Path(__file__).resolve().parent / "fixtures"


class GovernanceGuardTests(unittest.TestCase):
    def test_passes_on_current_repository(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            engine = GovernanceGuardEngine(REPO_ROOT, hard_guard_mode=True)
            result = engine.run(output_dir=Path(tmp), commit_sha="test-pass")
            self.assertEqual(result.status, "PASS", result.report["findings"])
            self.assertEqual(result.exit_code, 0)
            self.assertEqual(result.report["summary"]["critical"], 0)
            self.assertEqual(result.report["summary"]["major"], 0)

    def test_detects_intentional_authority_map_drift(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            tmp_path = Path(tmp)
            overrides: dict[str, Path] = {}
            for key, relative in CORE_FILE_PATHS.items():
                overrides[key] = REPO_ROOT / relative

            drift_catalog = (FIXTURES / "catalog-fourth-row.md").read_text(encoding="utf-8")
            drift_file = tmp_path / "catalog-decisions.md"
            drift_file.write_text(drift_catalog, encoding="utf-8")
            overrides["catalog"] = drift_file

            engine = GovernanceGuardEngine(
                REPO_ROOT,
                hard_guard_mode=True,
                file_overrides=overrides,
            )
            result = engine.run(output_dir=tmp_path / "out", commit_sha="test-drift")
            self.assertEqual(result.status, "FAIL")
            self.assertEqual(result.exit_code, 1)
            self.assertGreater(result.report["summary"]["critical"], 0)
            rule_ids = {finding.rule_id for finding in result.findings}
            self.assertTrue(
                rule_ids & {"D1", "DRIFT-05", "CATALOG_MAP_VIOLATION"},
                msg=f"unexpected rules: {rule_ids}",
            )

    def test_engine_error_on_missing_file(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            tmp_path = Path(tmp)
            missing_root = tmp_path / "empty-repo"
            missing_root.mkdir()
            engine = GovernanceGuardEngine(missing_root, hard_guard_mode=True)
            result = engine.run(output_dir=tmp_path / "out")
            self.assertEqual(result.exit_code, 2)
            self.assertEqual(result.findings[0].failure_type, "ENGINE_ERROR")

    def test_guard_is_read_only(self) -> None:
        authority_path = REPO_ROOT / CORE_FILE_PATHS["authority_model"]
        before = authority_path.read_bytes()
        with tempfile.TemporaryDirectory() as tmp:
            engine = GovernanceGuardEngine(REPO_ROOT, hard_guard_mode=True)
            engine.run(output_dir=Path(tmp))
        after = authority_path.read_bytes()
        self.assertEqual(before, after)

    def test_report_schema_fields(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            engine = GovernanceGuardEngine(REPO_ROOT, hard_guard_mode=True)
            result = engine.run(output_dir=Path(tmp))
            report = result.report
            self.assertEqual(report["schema_version"], "1.0.0")
            self.assertIn("findings", report)
            for finding in report["findings"]:
                for field in (
                    "finding_id",
                    "rule_id",
                    "severity",
                    "failure_type",
                    "file",
                    "affected_sections",
                    "message",
                ):
                    self.assertIn(field, finding)

            json_path = Path(tmp) / "governance-drift-report.json"
            self.assertTrue(json_path.is_file())
            loaded = json.loads(json_path.read_text(encoding="utf-8"))
            self.assertEqual(loaded["status"], result.status)


if __name__ == "__main__":
    unittest.main()
