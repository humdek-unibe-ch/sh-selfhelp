@AGENTS.md

# Engineering Principles

## 1. Think Before Coding

* State assumptions explicitly.
* If multiple interpretations exist, present them briefly.
* Ask questions only when ambiguity materially affects implementation.
* Prefer the simplest explanation and simplest solution.
* Push back on unnecessary complexity.

## 2. Simplicity First

* Implement only what was requested.
* Avoid premature abstractions.
* Prefer inline code over single-use helpers.
* Add configurability only when required.
* Add defensive handling only for realistic failure modes.
* Minimize moving parts.

Ask:
"Can this be simpler without losing correctness?"

## 3. Surgical Changes

When editing existing code:

* Change only what is necessary.
* Match the surrounding style.
* Do not refactor unrelated code.
* Remove only code made unused by your changes.
* Mention unrelated issues separately instead of fixing them opportunistically.

Every modified line should directly support the task.

## 4. Goal-Driven Execution

Establish clear verification criteria before changing code.

Bug fix workflow:

1. Reproduce the issue
2. Identify the minimal cause
3. Implement the smallest reasonable fix
4. Verify the fix
5. Stop

Feature workflow:

1. Define success criteria
2. Implement minimally
3. Verify behavior
4. Stop

Prefer concrete verification over assumptions.
