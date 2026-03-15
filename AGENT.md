# AGENT.md

## Objective

Act as a development agent following strict TDD-first execution.

---

## Core Rule

Always write or update tests before changing implementation.

---

## Mandatory Execution Order

1. Read relevant files
2. Identify existing behavior
3. Create failing test
4. Implement minimal code to pass
5. Refactor safely
6. Validate full execution

---

## TDD Rules

* Never start implementation before defining expected behavior in tests.
* Create one failing test per behavior.
* Implement only enough code to pass the test.
* Refactor only after tests pass.
* Preserve existing test coverage.

---

## Test Rules

* Prefer extending existing test suites.
* Follow repository test patterns.
* Keep tests isolated.
* Keep tests deterministic.
* Avoid unnecessary mocks.
* Validate edge cases.
* Validate failure cases.

---

## Code Change Rules

* Modify only code required to satisfy the test.
* Preserve existing architecture.
* Reuse existing functions whenever possible.
* Avoid duplicate logic.
* Avoid speculative abstractions.

---

## Safety Rules

* Do not rewrite entire files without necessity.
* Do not change unrelated behavior.
* Do not remove existing tests unless required.
* Do not bypass failing tests.

---

## Validation Rules

* Run affected tests before concluding.
* Validate no regression in related flows.
* Validate syntax and dependencies.
* Validate compatibility with existing modules.

---

## Backend Rules

* Preserve current backend conventions.
* Use transactions when multiple database operations exist.
* Validate data integrity.
* Validate error handling.

---

## API Rules

* Preserve response contracts.
* Preserve route compatibility.
* Validate request and response behavior through tests.

---

## Frontend Rules

* Preserve current component behavior.
* Validate UI behavior through existing test standards.
* Avoid breaking responsiveness.

---

## Debugging Rules

* Reproduce failure first.
* Write test reproducing the bug.
* Fix implementation only after test fails.
* Validate related flows after fix.

---

## Reporting Rules

Always report:

* tests created or updated
* files changed
* behavior covered
* possible impact

---

## Complexity Rules

* Break large changes into small tested steps.
* Deliver one passing behavior at a time.

---

## Final Check

Before finishing, verify:

* failing test existed first
* implementation is minimal
* all related tests pass
* no unrelated behavior changed
