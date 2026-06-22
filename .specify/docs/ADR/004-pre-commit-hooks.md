ADR-004: Pre-Commit Hook Strategy
File: docs/adr/004-pre-commit-hooks.md

Key Points to Cover
Context:

Code quality must be enforced before commits
CI pipeline takes 3-5 minutes to run
Developers need fast feedback
Balance between automation and developer experience
Decision:

Pre-commit hooks (not pre-push) with:
PHP CS Fixer (auto-fix)
PHPStan Level 8 (fail on errors)
Use husky + lint-staged pattern (PHP equivalent)
Allow bypass with --no-verify (emergencies only)
Rationale:

Fail-fast: Catch issues in 5s, not 5 minutes
Auto-fix: CS Fixer automatically formats code
Developer experience: Don’t waste time pushing bad code
CI efficiency: Fewer failed pipeline runs
Alternatives:

Pre-push hooks (run before git push)

Pros: Allows local commits without checks
Cons: Wastes CI time on bad pushes
Rejected: F01-077 requires pre-commit enforcement
IDE-only formatting

Pros: Real-time feedback
Cons: Not enforced, inconsistent across team
Rejected: Can’t rely on all devs using same IDE
CI-only checks

Pros: No local setup needed
Cons: Slow feedback (3-5 min), wastes resources
Rejected: Poor developer experience
Consequences:

Positive: Fast feedback, consistent code quality
Negative: Commits take ~5s longer
Neutral: Can bypass with --no-verify if needed
Implementation:

bash
# .husky/pre-commit (or equivalent)
#!/bin/sh

# Run PHP CS Fixer on staged files
git diff --cached --name-only --diff-filter=ACMR | grep '\.php$' | xargs -r php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php

# Run PHPStan on staged files
git diff --cached --name-only --diff-filter=ACMR | grep '\.php$' | xargs -r php vendor/bin/phpstan analyse --level=8 --no-progress

# Re-stage fixed files
git diff --cached --name-only --diff-filter=ACMR | grep '\.php$' | xargs -r git add
Setup Command:

bash
composer require --dev captainhook/captainhook
vendor/bin/captainhook install
References:

Constitution F01-077: Pre-commit quality checks
Constitution F01-078: Automated code formatting
Spec01 Section 5.2: Code Quality Automation