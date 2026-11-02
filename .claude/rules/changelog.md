---
paths:
  - "CHANGELOG.md"
  - "server/plugins/*/CHANGELOG.md"
---
# Changelog rules

A changelog says what is different in this version compared to the previous one. It is not a work log.

- Describe the result as it is in the released version, for the people who use or upgrade SelfHelp: what's new, what changed, what was fixed, and what they must do (run a migration, clear the cache, breaking change).
- Don't describe how the work went: no "first we did X, then changed it to Y", no review findings, no rejected approaches, no "refactored again", no test runs.
- When something changes again before the version is released, rewrite the existing entry to the final state. Don't add a second entry about the same change.
- A bug that only existed in the unreleased version is not a *Bug Fix*: fix the entry of the feature instead.
- Keep entries short: one bold lead line with the change, then a few bullets for the details that matter (behaviour, limits, access, required steps). Name tables, services and settings only where a developer needs them, typically in one "For developers" bullet; don't list every file, method or template.
- Use the section headings of the existing entries (*New Feature*, *Bug Fix*, *Improvement*). A migration is the last bullet of the entry it belongs to: the file name, what it adds or changes, and whether the cache must be cleared.
- An unreleased version is marked `(work in progress)` in its heading.
