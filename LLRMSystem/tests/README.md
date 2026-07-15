# LLRM System Testing Infrastructure

This directory contains a comprehensive test suite covering unit, integration, functional, end-to-end, acceptance, performance, smoke, edge-case, and accessibility testing.

## Directory Structure

```
tests/
├── bootstrap.php              # Test bootstrap
├── phpunit.xml                # PHPUnit configuration
├── Unit/                      # PHPUnit unit tests
│   ├── SanitizerTest.php
│   ├── RequestTest.php
│   ├── SessionTimeoutTest.php
│   └── EdgeCaseTest.php
├── Integration/               # PHPUnit integration tests
│   ├── SearchFilterTest.php
│   ├── FileUploadTest.php
│   └── Database/              # Real MySQL integration tests
│       ├── DatabaseTestCase.php
│       └── SearchDatabaseIntegrationTest.php
├── Functional/                # PHPUnit functional tests
│   ├── SearchFunctionalTest.php
│   ├── DocumentFunctionalTest.php
│   └── UserFunctionalTest.php
├── a11y/                      # PHPUnit accessibility tests
│   └── AccessibilityTest.php
├── cli/                       # Standalone CLI scripts (no PHPUnit needed)
│   ├── run-all-tests.php
│   ├── test-sanitizer.php
│   ├── test-search-filter.php
│   ├── test-session-timeout.php
│   ├── test-file-upload.php
│   ├── test-ping.php
│   ├── test-smoke.php
│   ├── test-edge-cases.php
│   └── test-accessibility.php
├── performance/               # PHP performance scripts
│   ├── smoke.php
│   └── benchmark.php
└── e2e/                       # Playwright end-to-end tests
    ├── playwright.config.js
    └── specs/
        ├── login.spec.js
        ├── search.spec.js
        ├── document-upload.spec.js
        ├── session-timeout.spec.js
        └── acceptance.spec.js
```

## Running CLI Tests (No Installation Required)

From the project root:

```powershell
php tests/cli/run-all-tests.php
```

Or run individual CLI tests:

```powershell
php tests/cli/test-sanitizer.php
php tests/cli/test-search-filter.php
php tests/cli/test-session-timeout.php
php tests/cli/test-file-upload.php
php tests/cli/test-ping.php
php tests/cli/test-smoke.php
php tests/cli/test-edge-cases.php
php tests/cli/test-accessibility.php
```

## Running PHPUnit Tests

```powershell
vendor\bin\phpunit --configuration tests\phpunit.xml
```

Run specific suites:

```powershell
vendor\bin\phpunit --configuration tests\phpunit.xml --testsuite Unit
vendor\bin\phpunit --configuration tests\phpunit.xml --testsuite Integration
vendor\bin\phpunit --configuration tests\phpunit.xml --testsuite Functional
vendor\bin\phpunit --configuration tests\phpunit.xml --testsuite Database
vendor\bin\phpunit --configuration tests\phpunit.xml --testsuite Accessibility
```

## Running Performance Tests

```powershell
php tests/performance/smoke.php
php tests/performance/benchmark.php
```

## Running End-to-End / Acceptance Tests

Install Playwright dependencies (one-time):

```powershell
npm install
npx playwright install chromium
```

Run all E2E tests (except the 5-minute session timeout test):

```powershell
npx playwright test --config=tests/e2e/playwright.config.js --grep-invert "session timeout"
```

Run the long-running session timeout test separately:

```powershell
npx playwright test --config=tests/e2e/playwright.config.js --grep "session timeout"
```

## What Is Covered

- **Unit**: `Sanitizer`, `Request`, `SessionTimeoutMiddleware` methods
- **Integration**: `SearchController` filter handling, `FileStorageService` upload validation
- **Database Integration**: real MySQL connection, `legislative_documents` table, type filter SQL
- **Functional**: `SearchController`, `DocumentController`, `UserController` business logic
- **End-to-End**: login, public search, admin redirect, document upload (Playwright)
- **Acceptance**: public search by document type (Playwright)
- **Performance**: HTTP response smoke checks and search benchmark
- **Smoke**: PHP server, MySQL, login page, public search page
- **Edge Cases**: SQL injection, XSS, null bytes, path traversal, empty arrays, long strings, emoji, zero-byte files
- **Accessibility**: missing `alt` attributes, unlabeled form inputs, heading order

## Notes

- Database integration tests require MySQL running and the `lrms_db` (or configured `DB_NAME`) database.
- Playwright tests require a PHP dev server on `http://localhost:8000`.
- Performance and smoke tests assume the dev server is running.
- E2E session timeout test takes ~5 minutes because it verifies the 5-minute idle timeout.
- PHP `>=8.1` is required.
