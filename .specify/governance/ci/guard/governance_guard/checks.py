"""Deterministic governance consistency checks (INV, A–D, DRIFT)."""

from __future__ import annotations

import re
from typing import Callable

from governance_guard.findings import Finding

OPERATIONAL_AUTHORITY_TYPES = (
    "Design Approval",
    "Implementation Authorization",
    "Batch Execution Permission",
)

HALT_CASE_C_MESSAGE = (
    "Governance precondition failure: transition nomination record required."
)

PRECEDENCE_LITERAL = "Case C → Case A → Case B"

CANONICAL_MAP_HEADER = "| Decision | Canonical Authority Source | Owner |"

FORBIDDEN_MAP_DECISIONS = (
    "Case C",
    "Nomination Record",
    "Next Spec Transition Nomination",
    "Transition Nomination",
)

NEGATION_MARKERS = (
    "does not grant",
    "do not grant",
    "must not grant",
    "cannot grant",
    "cannot satisfy",
    "does not satisfy",
    "must not satisfy",
    "cannot substitute",
    "does not authorize",
    "do not authorize",
    "must not",
    "cannot",
    "**not**",
)


def _positive_authority_leakage(line: str) -> bool:
    lowered = line.lower()
    if "nomination record" not in lowered and "case c" not in lowered:
        return False
    if not any(
        token in lowered
        for token in ("grant", "satisfy", "permit", "authorize", "substitute")
    ):
        return False
    if not any(
        auth.lower() in lowered
        for auth in (
            "design approval",
            "implementation authorization",
            "batch execution permission",
        )
    ):
        return False
    if any(marker in lowered for marker in NEGATION_MARKERS):
        return False
    return True

DRIFT_10_PATTERNS = (
    re.compile(
        r"Nomination Record.{0,120}per the canonical map.{0,80}authority ownership",
        re.IGNORECASE | re.DOTALL,
    ),
    re.compile(
        r"Next Spec Transition Nomination.{0,80}## Governance Decision Authority Map",
        re.IGNORECASE | re.DOTALL,
    ),
)


class GovernanceChecks:
  """Stateless validators mapped to governance-consistency-test-spec.md."""

  def __init__(self, files: dict[str, str], strict_literals: bool = True) -> None:
      self.files = files
      self.strict_literals = strict_literals
      self._counter = 0
      self.tests_passed: set[str] = set()
      self.tests_failed: set[str] = set()

  def run_all(self) -> list[Finding]:
      findings: list[Finding] = []
      phases: list[Callable[[], list[Finding]]] = [
          self.phase_1_authority_model,
          self.phase_2_execution_policy,
          self.phase_3_enforcer,
          self.phase_4_catalog,
          self.phase_5_cross_file,
      ]
      for phase in phases:
          findings.extend(phase())
      return findings

  def _next_id(self) -> str:
      self._counter += 1
      return f"f-{self._counter:04d}"

  def _record_test(self, test_id: str, passed: bool) -> None:
      if passed:
          self.tests_passed.add(test_id)
      else:
          self.tests_failed.add(test_id)

  def _line_of(self, content: str, needle: str) -> int | None:
      for index, line in enumerate(content.splitlines(), start=1):
          if needle in line:
              return index
      return None

  def _make(
      self,
      *,
      rule_id: str,
      failure_type: str,
      severity: str,
      file_key: str,
      message: str,
      affected_sections: list[str] | None = None,
      line_start: int | None = None,
      remediation_hint: str = "",
      related_files: list[str] | None = None,
  ) -> Finding:
      return Finding(
          finding_id=self._next_id(),
          rule_id=rule_id,
          failure_type=failure_type,
          severity=severity,
          file=self.files[file_key]["path"],
          message=message,
          affected_sections=affected_sections or [],
          line_start=line_start,
          line_end=line_start,
          remediation_hint=remediation_hint,
          related_files=related_files or [],
      )

  def phase_1_authority_model(self) -> list[Finding]:
      findings: list[Finding] = []
      content = self.files["authority_model"]["content"]
      path = self.files["authority_model"]["path"]

      three_types = "Exactly **three** operational authority types"
      has_three_phrase = three_types in content
      if not has_three_phrase:
          findings.append(
              self._make(
                  rule_id="INV-01",
                  failure_type="AUTHORITY_DRIFT",
                  severity="CRITICAL",
                  file_key="authority_model",
                  message=f"{path}: missing required phrase '{three_types}'.",
                  affected_sections=["§2 — Operational Authority Types"],
              )
          )

      op_section = re.search(
          r"### Operational Authority Types.*?\n\n(.*?)(?=\n### |\n## )",
          content,
          re.DOTALL,
      )
      numbered = []
      if op_section:
          numbered = re.findall(
              r"^\d+\.\s+\*\*(Design Approval|Implementation Authorization|Batch Execution Permission)\*\*",
              op_section.group(1),
              re.MULTILINE,
          )
      if len(numbered) != 3 or set(numbered) != set(OPERATIONAL_AUTHORITY_TYPES):
          findings.append(
              self._make(
                  rule_id="A1",
                  failure_type="AUTHORITY_DRIFT",
                  severity="CRITICAL",
                  file_key="authority_model",
                  message=(
                      f"{path}: §2 must enumerate exactly three operational authority types; "
                      f"found {len(numbered)}."
                  ),
                  affected_sections=["§2 — Operational Authority Types"],
              )
          )

      if "Non-Operational Governance Decision Classes" not in content:
          findings.append(
              self._make(
                  rule_id="A2",
                  failure_type="AUTHORITY_DRIFT",
                  severity="CRITICAL",
                  file_key="authority_model",
                  message=f"{path}: missing §2 Non-Operational Governance Decision Classes.",
                  affected_sections=["§2"],
              )
          )

      if "**Nomination Record**" not in content or "evidence-only" not in content:
          findings.append(
              self._make(
                  rule_id="INV-03",
                  failure_type="NOMINATION_AUTHORITY_LEAKAGE",
                  severity="CRITICAL",
                  file_key="authority_model",
                  message=f"{path}: Nomination Record must be defined as evidence-only.",
                  affected_sections=["§2 — Nomination Record"],
              )
          )

      if "Next Spec Transition Nomination" in content and "**non-operational**" not in content:
          findings.append(
              self._make(
                  rule_id="A3",
                  failure_type="CASE_C_MISCLASSIFICATION",
                  severity="CRITICAL",
                  file_key="authority_model",
                  message=f"{path}: Next Spec Transition Nomination must be non-operational.",
                  affected_sections=["§2 — Next Spec Transition Nomination"],
              )
          )

      if "Artifacts outside the authorization record lifecycle" in content:
          lifecycle_section = content.split("Artifacts outside the authorization record lifecycle", 1)[-1]
          if "Nomination Records" not in lifecycle_section[:800]:
              findings.append(
                  self._make(
                      rule_id="INV-05",
                      failure_type="NOMINATION_AUTHORITY_LEAKAGE",
                      severity="CRITICAL",
                      file_key="authority_model",
                      message=(
                          f"{path}: Nomination Records must be listed outside authorization lifecycle."
                      ),
                      affected_sections=["§4 — Artifacts outside the authorization record lifecycle"],
                  )
              )

      for inv in ("I8", "I9"):
          if f"| {inv} |" not in content:
              findings.append(
                  self._make(
                      rule_id=f"INV-{inv[-1]}",
                      failure_type="AUTHORITY_DRIFT",
                      severity="CRITICAL",
                      file_key="authority_model",
                      message=f"{path}: invariant {inv} missing from §6.",
                      affected_sections=["§6 — Invariants"],
                  )
              )

      self._record_test("A1", not any(f.rule_id in {"A1", "INV-01"} for f in findings))
      self._record_test("A2", not any(f.rule_id in {"A2", "INV-03", "INV-05"} for f in findings))
      self._record_test("A3", not any(f.rule_id == "A3" for f in findings))
      return findings

  def phase_2_execution_policy(self) -> list[Finding]:
      findings: list[Finding] = []
      content = self.files["execution_policy"]["content"]
      path = self.files["execution_policy"]["path"]

      if "### Case C — Governance precondition failure" not in content:
          findings.append(
              self._make(
                  rule_id="B1",
                  failure_type="CASE_C_MISCLASSIFICATION",
                  severity="CRITICAL",
                  file_key="execution_policy",
                  message=f"{path}: missing § Case C — Governance precondition failure.",
                  affected_sections=["§ HALT Classification — Case C"],
              )
          )

      if HALT_CASE_C_MESSAGE not in content:
          findings.append(
              self._make(
                  rule_id="DRIFT-07",
                  failure_type="PRECEDENCE_MISMATCH",
                  severity="MAJOR",
                  file_key="execution_policy",
                  message=f"{path}: missing exact Case C HALT message.",
                  affected_sections=["§ Case C"],
              )
          )

      if "## Nomination and Execution Policy" not in content:
          findings.append(
              self._make(
                  rule_id="B2",
                  failure_type="NOMINATION_AUTHORITY_LEAKAGE",
                  severity="CRITICAL",
                  file_key="execution_policy",
                  message=f"{path}: missing § Nomination and Execution Policy.",
                  affected_sections=["§ Nomination and Execution Policy"],
              )
          )

      for must_not in (
          "MUST NOT** grant Design Approval, Implementation Authorization, or Batch Execution Permission",
          "Nomination Records **cannot** satisfy steps 1–4",
      ):
          if must_not not in content:
              findings.append(
                  self._make(
                      rule_id="B2",
                      failure_type="NOMINATION_AUTHORITY_LEAKAGE",
                      severity="CRITICAL",
                      file_key="execution_policy",
                      message=f"{path}: missing non-authorizing constraint.",
                      affected_sections=["§ Nomination and Execution Policy / Pre-Execution Requirements"],
                  )
              )
              break

      if "does not** clear HALT caused by missing operational authority" not in content.replace(
          "does not clear HALT", "does not** clear HALT"
      ):
          if "does not clear HALT caused by missing operational authority" not in content:
              if "remains** a **HALT**" not in content and "remains a **HALT**" not in content:
                  findings.append(
                      self._make(
                          rule_id="INV-10",
                          failure_type="NOMINATION_AUTHORITY_LEAKAGE",
                          severity="CRITICAL",
                          file_key="execution_policy",
                          message=f"{path}: must state nomination does not clear missing operational authority HALT.",
                          affected_sections=["§ Nomination and Execution Policy"],
                      )
                  )

      step6 = re.search(
          r"### Detection procedure.*?^6\.\s+If multiple cases could apply.*$",
          content,
          re.MULTILINE | re.DOTALL,
      )
      precedence_ok = False
      if step6:
          line = step6.group(0)
          precedence_ok = (
              "Case C" in line
              and "Case A" in line
              and "Case B" in line
              and line.index("Case C") < line.index("Case A") < line.index("Case B")
          )
      if not precedence_ok:
          findings.append(
              self._make(
                  rule_id="B3",
                  failure_type="PRECEDENCE_MISMATCH",
                  severity="MAJOR",
                  file_key="execution_policy",
                  message=f"{path}: detection procedure step 6 must order Case C before Case A before Case B.",
                  affected_sections=["§ HALT Classification — Detection procedure"],
              )
          )

      if self.strict_literals and PRECEDENCE_LITERAL not in content:
          catalog = self.files["catalog"]["content"]
          if PRECEDENCE_LITERAL not in catalog:
              findings.append(
                  self._make(
                      rule_id="DRIFT-04",
                      failure_type="PRECEDENCE_MISMATCH",
                      severity="MINOR",
                      file_key="execution_policy",
                      message=(
                          f"{path}: optional precedence literal '{PRECEDENCE_LITERAL}' absent; "
                          "semantic precedence in step 6 is required instead."
                      ),
                      affected_sections=["§ HALT Classification"],
                      remediation_hint="Add literal or keep step 6 ordering per consistency spec B3.",
                  )
              )

      if "Exactly **three** operational authority types" not in content:
          findings.append(
              self._make(
                  rule_id="A1",
                  failure_type="AUTHORITY_DRIFT",
                  severity="CRITICAL",
                  file_key="execution_policy",
                  message=f"{path}: must affirm exactly three operational authority types.",
                  affected_sections=["§ Authority Ownership"],
              )
          )

      for line in content.splitlines():
          if _positive_authority_leakage(line):
              findings.append(
                  self._make(
                      rule_id="DRIFT-01",
                      failure_type="CASE_C_MISCLASSIFICATION",
                      severity="CRITICAL",
                      file_key="execution_policy",
                      message=f"{path}: possible Case C / Nomination operational authority leakage.",
                      affected_sections=["§ Nomination and Execution Policy"],
                      line_start=self._line_of(content, line.strip()[:60]),
                  )
              )
              break

      for pattern in DRIFT_10_PATTERNS:
          if pattern.search(content):
              findings.append(
                  self._make(
                      rule_id="DRIFT-10",
                      failure_type="CATALOG_MAP_VIOLATION",
                      severity="CRITICAL",
                      file_key="execution_policy",
                      message=f"{path}: nomination must not be tied to canonical map authority ownership.",
                      affected_sections=["§ Governance Transition Follow-Up"],
                  )
              )

      self._record_test("B1", "B1" not in {f.rule_id for f in findings})
      self._record_test("B2", not any(f.rule_id in {"B2", "INV-10"} for f in findings))
      self._record_test("B3", "B3" not in {f.rule_id for f in findings})
      return findings

  def phase_3_enforcer(self) -> list[Finding]:
      findings: list[Finding] = []
      content = self.files["enforcer"]["content"]
      path = self.files["enforcer"]["path"]

      if "Case C MUST be evaluated before any operational authority checks" not in content:
          findings.append(
              self._make(
                  rule_id="C1",
                  failure_type="PRECEDENCE_MISMATCH",
                  severity="MAJOR",
                  file_key="enforcer",
                  message=f"{path}: step 4 must require Case C evaluation before operational checks.",
                  affected_sections=["Validation Order step 4"],
              )
          )

      if PRECEDENCE_LITERAL not in content:
          findings.append(
              self._make(
                  rule_id="DRIFT-04",
                  failure_type="PRECEDENCE_MISMATCH",
                  severity="MAJOR",
                  file_key="enforcer",
                  message=f"{path}: missing mandatory precedence literal '{PRECEDENCE_LITERAL}'.",
                  affected_sections=["Validation Order step 7"],
              )
          )

      if HALT_CASE_C_MESSAGE not in content:
          findings.append(
              self._make(
                  rule_id="DRIFT-07",
                  failure_type="PRECEDENCE_MISMATCH",
                  severity="MAJOR",
                  file_key="enforcer",
                  message=f"{path}: missing exact Case C HALT message in Output Expectations.",
                  affected_sections=["Output Expectations"],
              )
          )

      required_output = "Case C — Governance precondition failure: transition nomination record required."
      if required_output not in content:
          findings.append(
              self._make(
                  rule_id="C3",
                  failure_type="CASE_C_MISCLASSIFICATION",
                  severity="MAJOR",
                  file_key="enforcer",
                  message=f"{path}: missing Case C classification output message.",
                  affected_sections=["Output Expectations — Case C reporting"],
              )
          )

      if "Nomination Records are **not** part of authorization validation" not in content:
          findings.append(
              self._make(
                  rule_id="C2",
                  failure_type="NOMINATION_AUTHORITY_LEAKAGE",
                  severity="CRITICAL",
                  file_key="enforcer",
                  message=f"{path}: must exclude Nomination Records from authorization validation.",
                  affected_sections=["Validation Order step 4"],
              )
          )

      if "evaluate only after Case C is ruled out or satisfied" not in content:
          findings.append(
              self._make(
                  rule_id="DRIFT-08",
                  failure_type="PRECEDENCE_MISMATCH",
                  severity="MAJOR",
                  file_key="enforcer",
                  message=f"{path}: operational checks must run only after Case C is ruled out.",
                  affected_sections=["HARD RULE"],
              )
          )

      if "Exactly **three** operational authority types" not in content:
          findings.append(
              self._make(
                  rule_id="A1",
                  failure_type="AUTHORITY_DRIFT",
                  severity="CRITICAL",
                  file_key="enforcer",
                  message=f"{path}: must affirm exactly three operational authority types.",
                  affected_sections=["HARD RULE Clarification"],
              )
          )

      self._record_test("C1", "C1" not in {f.rule_id for f in findings})
      self._record_test("C2", "C2" not in {f.rule_id for f in findings})
      self._record_test("C3", "C3" not in {f.rule_id for f in findings})
      return findings

  def phase_4_catalog(self) -> list[Finding]:
      findings: list[Finding] = []
      content = self.files["catalog"]["content"]
      path = self.files["catalog"]["path"]

      if "### Operational authority map scope (strict)" not in content:
          findings.append(
              self._make(
                  rule_id="D1",
                  failure_type="CATALOG_MAP_VIOLATION",
                  severity="CRITICAL",
                  file_key="catalog",
                  message=f"{path}: missing § Operational authority map scope (strict).",
                  affected_sections=["§ Governance Decision Authority Map"],
              )
          )

      if "### Case C — governance precondition classification" not in content:
          findings.append(
              self._make(
                  rule_id="D2",
                  failure_type="CASE_C_MISCLASSIFICATION",
                  severity="CRITICAL",
                  file_key="catalog",
                  message=f"{path}: missing § Case C non-operational boundary.",
                  affected_sections=["§ Case C"],
              )
          )

      if "### Nomination Record boundary" not in content:
          findings.append(
              self._make(
                  rule_id="D3",
                  failure_type="CATALOG_MAP_VIOLATION",
                  severity="CRITICAL",
                  file_key="catalog",
                  message=f"{path}: missing § Nomination Record boundary.",
                  affected_sections=["§ Nomination Record boundary"],
              )
          )

      rows = self._parse_authority_map_rows(content)
      if len(rows) != 3:
          findings.append(
              self._make(
                  rule_id="D1",
                  failure_type="CATALOG_MAP_VIOLATION",
                  severity="CRITICAL",
                  file_key="catalog",
                  message=f"{path}: authority map must contain exactly three data rows; found {len(rows)}.",
                  affected_sections=["§ Governance Decision Authority Map"],
              )
          )

      invalid = [row for row in rows if row not in OPERATIONAL_AUTHORITY_TYPES]
      if invalid:
          findings.append(
              self._make(
                  rule_id="DRIFT-05",
                  failure_type="CATALOG_MAP_VIOLATION",
                  severity="CRITICAL",
                  file_key="catalog",
                  message=f"{path}: invalid authority map decisions: {invalid}.",
                  affected_sections=["§ Governance Decision Authority Map"],
              )
          )

      for forbidden in FORBIDDEN_MAP_DECISIONS:
          for row in rows:
              if forbidden.lower() in row.lower():
                  findings.append(
                      self._make(
                          rule_id="D3",
                          failure_type="CATALOG_MAP_VIOLATION",
                          severity="CRITICAL",
                          file_key="catalog",
                          message=f"{path}: non-operational class '{forbidden}' must not appear as map row.",
                          affected_sections=["§ Governance Decision Authority Map"],
                      )
                  )

      if "Case C is NOT an operational governance decision class" not in content:
          findings.append(
              self._make(
                  rule_id="INV-04",
                  failure_type="CASE_C_MISCLASSIFICATION",
                  severity="CRITICAL",
                  file_key="catalog",
                  message=f"{path}: Case C must be documented as non-operational.",
                  affected_sections=["§ Case C"],
              )
          )

      self._record_test("D1", "D1" not in {f.rule_id for f in findings} and "DRIFT-05" not in {f.rule_id for f in findings})
      self._record_test("D2", "D2" not in {f.rule_id for f in findings} and "INV-04" not in {f.rule_id for f in findings})
      self._record_test("D3", "D3" not in {f.rule_id for f in findings})
      return findings

  def phase_5_cross_file(self) -> list[Finding]:
      findings: list[Finding] = []

      for file_key in ("authority_model", "execution_policy", "enforcer"):
          content = self.files[file_key]["content"]
          path = self.files[file_key]["path"]
          if "Exactly **three** operational authority types" not in content:
              findings.append(
                  self._make(
                      rule_id="A1",
                      failure_type="AUTHORITY_DRIFT",
                      severity="CRITICAL",
                      file_key=file_key,
                      message=f"{path}: must affirm exactly three operational authority types.",
                      affected_sections=[],
                  )
              )

      catalog_content = self.files["catalog"]["content"]
      if "exactly **three** operational governance decision classes" not in catalog_content.lower():
          findings.append(
              self._make(
                  rule_id="A1",
                  failure_type="AUTHORITY_DRIFT",
                  severity="CRITICAL",
                  file_key="catalog",
                  message=(
                      f"{self.files['catalog']['path']}: must affirm exactly three operational "
                      "governance decision classes in map scope."
                  ),
                  affected_sections=["§ Operational authority map scope (strict)"],
              )
          )

      enforcer = self.files["enforcer"]["content"]
      policy = self.files["execution_policy"]["content"]
      if HALT_CASE_C_MESSAGE not in policy or HALT_CASE_C_MESSAGE not in enforcer:
          findings.append(
              self._make(
                  rule_id="DRIFT-07",
                  failure_type="PRECEDENCE_MISMATCH",
                  severity="MAJOR",
                  file_key="enforcer",
                  message="Case C HALT message must match exactly in execution-policy and enforcer.",
                  affected_sections=["§ HALT Classification", "Output Expectations"],
                  related_files=[
                      self.files["execution_policy"]["path"],
                      self.files["enforcer"]["path"],
                  ],
              )
          )

      for file_key in ("authority_model", "execution_policy", "enforcer"):
          content = self.files[file_key]["content"]
          if CANONICAL_MAP_HEADER in content:
              findings.append(
                  self._make(
                      rule_id="DRIFT-06",
                      failure_type="AUTHORITY_DRIFT",
                      severity="CRITICAL",
                      file_key=file_key,
                      message=f"{self.files[file_key]['path']}: parallel ownership map table detected outside catalog-decisions.md.",
                      affected_sections=[],
                      remediation_hint="Remove duplicate map; catalog-decisions.md is sole owner register.",
                  )
              )

      for file_key in ("authority_model", "execution_policy", "enforcer", "catalog"):
          content = self.files[file_key]["content"]
          for line in content.splitlines():
              if _positive_authority_leakage(line):
                  findings.append(
                      self._make(
                          rule_id="DRIFT-02",
                          failure_type="NOMINATION_AUTHORITY_LEAKAGE",
                          severity="CRITICAL",
                          file_key=file_key,
                          message=f"{self.files[file_key]['path']}: nomination/Case C authority leakage pattern.",
                          affected_sections=[],
                          line_start=self._line_of(content, line.strip()[:60]),
                      )
                  )
                  break

      return findings

  def _parse_authority_map_rows(self, content: str) -> list[str]:
      if "## Governance Decision Authority Map" not in content:
          return []
      region = content.split("## Governance Decision Authority Map", 1)[1]
      region = region.split("\n## ", 1)[0]
      rows: list[str] = []
      for line in region.splitlines():
          if not line.startswith("|") or line.startswith("| ---"):
              continue
          cells = [cell.strip() for cell in line.strip("|").split("|")]
          if not cells:
              continue
          decision = cells[0]
          if decision in ("Decision", "---"):
              continue
          if decision.startswith("---"):
              continue
          rows.append(decision)
      return rows
