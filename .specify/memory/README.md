# Research Memory Specification

## Purpose

Research Memory is a post-execution, human-readable repository for notable observations in DormSys governed development. Its primary objective is to enable retrospective analysis, pattern discovery, and project-wide health auditing.

## Immutable Principles

1. **Execution Isolation:** Research Memory must NEVER be read or referenced during any phase of active development (Analysis, Contract, Authorization, Implementation). It is strictly for human post-mortem review.
2. **Authority-Free:** This system has ZERO governance authority. It cannot dictate scope, implementation, or architectural decisions.
3. **Observation-Only:** It records what happened, not what should happen.
4. **Append-Only:** Historical records are immutable. Corrections happen via new records.
5. **No-Fail Policy:** Memory logging must never propagate errors to the primary execution. If logging fails, the process continues unaffected.

## Process

When a governed execution produces an outcome worth future human reflection (e.g., a logic dead-end or a persistent ambiguity), a log is appended to `logs/`. If no such observation exists, nothing is recorded.
