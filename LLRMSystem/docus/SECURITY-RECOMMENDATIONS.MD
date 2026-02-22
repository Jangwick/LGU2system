# Security Recommendations
## Legislative Records Management System (LRMS)

> **System Type:** Custom PHP (No Framework) — MySQL/PDO Backend  
> **Analysis Date:** February 22, 2026  
> **Scope:** Full codebase review covering authentication, session management, access control, data handling, file operations, and API security.

---

## Table of Contents

1. [Current Security Mechanisms](#1-current-security-mechanisms)
2. [Why They Were Implemented](#2-why-they-were-implemented)
3. [Weaknesses in the Current Implementation](#3-weaknesses-in-the-current-implementation)
4. [Recommended Improvements](#4-recommended-improvements)
5. [Why Implement Those Improvements](#5-why-implement-those-improvements)
6. [Next Steps](#6-next-steps)

---

## 1. Current Security Mechanisms

### 1.1 Authentication

| Mechanism | Implementation Location |
|---|---|
| Email + password login | `modules/authentication/controllers/LoginController.php` |
| Passwords hashed with `password_hash()` (bcrypt) | `modules/authentication/controllers/RegisterController.php`, `UserController.php` |
| Two-factor OTP via email (6-digit code, 10-min expiry) | `modules/authentication/controllers/VerifyOtpController.php` |
| OTP marked as used after single verification | `modules/authentication/controllers/VerifyOtpController.php` |
| Administrator registration blocked on public form | `modules/authentication/controllers/RegisterController.php` |

### 1.2 Session Management

| Mechanism | Implementation Location |
|---|---|
| `session_start()` with `httponly` and `use_only_cookies` flags | `modules/core/config/config.php` |
| Concurrent session prevention via `last_session_id` tracking | `modules/core/config/config.php` — `isSessionValid()`, `modules/authentication/controllers/VerifyOtpController.php` |
| Session destruction on logout (cookie and session cleared) | `modules/authentication/controllers/LogoutController.php` |
| "Remember Me" token using `bin2hex(random_bytes(32))` | `modules/authentication/controllers/VerifyOtpController.php` |

### 1.3 Brute-Force and Lockout Protection

| Mechanism | Implementation Location |
|---|---|
| Progressive lockout: 5 free attempts, then +5 minutes per failure | `modules/core/utils/Security.php` |
| Lockout tracked per IP address + email identifier pair | `modules/core/utils/Security.php`, `database/migrations/008_create_login_attempts_table.sql` |
| Failed attempt count communicated to the user | `modules/authentication/controllers/LoginController.php` |

### 1.4 Role-Based Access Control (RBAC)

| Mechanism | Implementation Location |
|---|---|
| Four-tier role hierarchy: viewer → staff → officer → administrator | `modules/core/middleware/PermissionMiddleware.php` |
| Granular permission matrix per role | `modules/core/middleware/PermissionMiddleware.php` |
| Document-level access rules (e.g., staff can only edit own docs) | `modules/core/middleware/PermissionMiddleware.php` — `canAccessDocument()` |
| New non-viewer accounts require admin approval (`pending` status) | `modules/authentication/controllers/RegisterController.php` |

### 1.5 SQL Injection Prevention

| Mechanism | Implementation Location |
|---|---|
| PDO with prepared statements and bound parameters throughout | All database interaction files |
| `PDO::ATTR_EMULATE_PREPARES => false` (real prepared statements) | `modules/core/config/database.php` |

### 1.6 Output Encoding (XSS Prevention)

| Mechanism | Implementation Location |
|---|---|
| `htmlspecialchars()` used on dynamic output in views | `modules/user-management/views/profile.php`, `modules/integration/views/shared_layout.php`, `modules/core/middleware/PermissionMiddleware.php` (403 page) |

### 1.7 File Upload Security

| Mechanism | Implementation Location |
|---|---|
| MIME type allowlist (PDF, Word, Excel, PowerPoint only) | `modules/document-management/services/DocumentService.php` — `validateFile()` |
| 50 MB file size limit enforced server-side | `modules/document-management/services/DocumentService.php` |
| Randomised stored filenames (`timestamp_random.ext`) | `modules/document-management/services/FileStorageService.php` |
| Uploaded files stored outside the web root (`/storage/`) | `modules/document-management/services/FileStorageService.php` |

### 1.8 API Security

| Mechanism | Implementation Location |
|---|---|
| API key validation with database lookup | `modules/core/middleware/ApiAuthMiddleware.php` |
| API key expiry support | `modules/core/middleware/ApiAuthMiddleware.php` |
| In-memory rate limiting: 100 requests/minute per key | `modules/core/middleware/ApiAuthMiddleware.php` |
| `last_used_at` tracking per key | `modules/core/middleware/ApiAuthMiddleware.php` |

### 1.9 Audit Logging

| Mechanism | Implementation Location |
|---|---|
| Comprehensive activity log table recording user, action, IP, User-Agent, timestamps | `modules/core/utils/Logger.php` |
| Severity levels: info, warning, error, critical | `modules/core/utils/Logger.php` |
| Old/new value capture for change tracking | `modules/core/utils/Logger.php` — `logActivity()` |
| Login success, failure, and OTP events all logged | `modules/authentication/controllers/LoginController.php`, `VerifyOtpController.php` |

---

## 2. Why They Were Implemented

### Authentication & OTP
A government records system holds sensitive legislative documents. Simple username/password login is insufficient because a single compromised credential would grant full access. The OTP email step adds a second verification channel, ensuring that even if a password is stolen, an attacker still cannot log in without access to the registered email inbox.

### Session Tracking (`last_session_id`)
Without this, the same account could be active simultaneously on multiple devices — a risk in a shared-workstation government office environment. The single-session enforcement ensures that logging in from a new device automatically invalidates the previous session, limiting the blast radius of credential theft.

### Progressive Lockout
Brute-force password attacks are a common and automated threat. By progressively increasing lockout duration (5 attempts free, then escalating minutes of lockout per additional failure), the system makes automated guessing exponentially expensive without permanently denying access to legitimate users who mistype their password.

### Role-Based Access Control
A records management system is accessed by staff with vastly different responsibilities — from read-only public viewers to administrators managing users. Flat access control would allow any authenticated user to delete records, change roles, or access audit trails. RBAC limits what actions a user can perform to only what their job function requires (principle of least privilege).

### PDO Prepared Statements
SQL injection is consistently ranked as one of the top web application vulnerabilities. Using real prepared statements (with `ATTR_EMULATE_PREPARES => false`) completely separates SQL code from user-supplied data at the driver level, making injection structurally impossible for all parameterised queries.

### File Upload Allowlisting
Without restrictions, a malicious user could upload a PHP web shell disguised as a document. Restricting uploads to document-type MIME types and storing files outside the web root prevents uploaded files from ever being executed by the web server.

### Audit Logging
Government systems are subject to accountability requirements. The audit log provides an immutable (within the database) trail of who performed what action, providing forensic evidence in case of disputes, breaches, or misconduct investigations.

---

## 3. Weaknesses in the Current Implementation

### 3.1 No CSRF Protection (Critical)
**Finding:** No CSRF tokens are generated or validated on any form — login, registration, forgot-password, document upload, user management, or profile updates.  
Despite being referenced in the README and documentation (`docus/FINAL_SYSTEM_COMPLETION_REPORT.md`, `docus/STRUCTURE.md`), a `CsrfMiddleware.php` file does not exist in the codebase. All state-changing POST endpoints are fully vulnerable to Cross-Site Request Forgery.

### 3.2 OTP Generated with `mt_rand()` (High)
**File:** `modules/authentication/controllers/LoginController.php` — line ~109  
```php
$otpCode = sprintf("%06d", mt_rand(1, 999999));
```  
`mt_rand()` is a pseudo-random number generator (Mersenne Twister). Its output can be predicted if an attacker has observed prior outputs from the same PHP process. It is not suitable for security tokens. `random_int()` is the cryptographically secure alternative available since PHP 7.

### 3.3 OTP Stored in Plaintext (High)
**File:** `database/migrations/007_create_user_otps_table.sql`, `VerifyOtpController.php`  
The OTP code is stored verbatim in the `user_otps` table and compared directly. A database compromise exposes all currently valid OTPs, allowing an attacker with read access to bypass MFA entirely.

### 3.4 IP Spoofing Bypasses Lockout (High)
**File:** `modules/core/utils/Security.php` — `getClientIP()`  
```php
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    return $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    return $_SERVER['HTTP_X_FORWARDED_FOR'];
}
```  
Brute-force lockout is keyed on IP address. Both `HTTP_CLIENT_IP` and `HTTP_X_FORWARDED_FOR` are attacker-controlled HTTP headers — any client can set them to an arbitrary value. An attacker can trivially bypass IP-based lockout by rotating a spoofed header value on each request.

### 3.5 Remember Me Cookie Missing Security Flags (Medium)
**File:** `modules/authentication/controllers/VerifyOtpController.php` — line ~84  
```php
setcookie('remember_token', $token, time() + (7 * 24 * 60 * 60), '/');
```  
The cookie is set without the `httponly` flag (making it accessible to JavaScript — XSS-vulnerable) and without the `secure` flag (allowing transmission over plain HTTP). The token is also stored in plaintext in the `users` table rather than as a hash.

### 3.6 Credentials and API Keys Hardcoded in Source (Medium)
**File:** `modules/core/config/config.php`  
```php
define('SMTP_PASS', 'imok xero ttaf mypf'); // Gmail App Password
define('DB_USER', 'root');
define('DB_PASS', '');
```  
SMTP credentials (a live Gmail App Password) and default database credentials are committed directly in the configuration source file. Anyone with repository read access has full credentials.

### 3.7 File Upload MIME Type Relies on Client-Supplied Value (Medium)
**File:** `modules/document-management/services/DocumentService.php` — `validateFile()`  
```php
if (!in_array($file['type'], $allowedTypes)) { ... }
```  
`$_FILES['type']` is the MIME type sent by the browser. It is entirely client-controlled and can be forged. An attacker can upload a PHP file with a `Content-Type: application/pdf` header and bypass this check.

### 3.8 Restrictive and Counterproductive Password Policy (Medium)
**File:** `modules/authentication/controllers/RegisterController.php`  
```php
if ($passwordLength < 8 || $passwordLength > 14) { ... }
if ($uniqueCharsCount < $passwordLength) { ... }
```  
Capping passwords at 14 characters prevents users from using passphrases, which are both more secure and more memorable than short passwords. The "all characters must be unique" rule makes long passwords structurally impossible and pushes users toward shorter, less secure choices. NIST SP 800-63B recommends allowing up to at least 64 characters with no character-repetition restrictions.

### 3.9 In-Memory API Rate Limiting Resets Per Request (Medium)
**File:** `modules/core/middleware/ApiAuthMiddleware.php`  
The `$rateLimits` array is an instance property. PHP is share-nothing — each HTTP request spawns a fresh process. The rate limit counter resets to zero on every request, making the rate limiter completely ineffective. An attacker can send unlimited requests and will never be throttled.

### 3.10 `APP_ENV` Set to `development` in Production Config (Medium)
**File:** `modules/core/config/config.php`  
```php
define('APP_ENV', 'development');
```  
In development mode the system enables `display_errors = 1` and `E_ALL` error reporting. If this value is not overridden via `config.local.php`, stack traces, file paths, and SQL error messages may be exposed to end users in a production environment.

### 3.11 No Output Sanitization in Many Views (Medium)
While `htmlspecialchars()` is used in some views (profile page, integration view), many other views that echo user-or database-sourced data do not apply it consistently. Unsanitised output in dashboard widgets, document listing, audit log displays, and search results may be exploitable via stored or reflected XSS.

### 3.12 No OTP Brute-Force Rate Limiting (Medium)
**File:** `modules/authentication/controllers/VerifyOtpController.php`  
There is no attempt counter or lockout on the OTP verification endpoint. A 6-digit OTP has 1,000,000 possible values. An attacker who has stolen a valid session (`otp_pending_user_id`) can attempt all combinations before the 10-minute expiry. At a rate of ~2,000 requests/minute, this is crackable within minutes.

### 3.13 Sensitive Data Returned in API Responses (Low)
**File:** `modules/authentication/controllers/LoginController.php`  
```php
'email' => $targetEmail
```  
The OTP delivery address is returned to the client in the login JSON response. While useful for UX, this leaks the email redirect for the admin account (`Johnrick1214@gmail.com`) to any client that submits the admin email during login.

### 3.14 Username Enumeration via Distinct Error Messages (Low)
**File:** `modules/authentication/controllers/LoginController.php`  
Different error messages are returned for "email not found" vs. "invalid password". This lets an attacker enumerate valid email addresses by observing which error is returned.

---

## 4. Recommended Improvements

### 4.1 Implement CSRF Tokens on All State-Changing Forms (Critical)

Create a `CsrfMiddleware.php` and integrate it globally:

```php
// modules/core/middleware/CsrfMiddleware.php
class CsrfMiddleware {
    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateToken(string $token): bool {
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireValidToken(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!self::validateToken($token)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Invalid CSRF token.']));
        }
    }
}
```

In every HTML form, add a hidden field:
```html
<input type="hidden" name="csrf_token" value="<?php echo CsrfMiddleware::generateToken(); ?>">
```

At the top of every POST-handling controller, call:
```php
CsrfMiddleware::requireValidToken();
```

### 4.2 Replace `mt_rand()` with `random_int()` for OTP Generation

```php
// Before
$otpCode = sprintf("%06d", mt_rand(1, 999999));

// After
$otpCode = sprintf("%06d", random_int(0, 999999));
```

### 4.3 Hash OTPs Before Storage

Store a hash of the OTP, not the plaintext value:

```php
// On generation — store hash
$otpCode    = sprintf("%06d", random_int(0, 999999));
$otpHash    = password_hash($otpCode, PASSWORD_DEFAULT);
$stmt->execute([$user['id'], $otpHash, $expiry]);

// On verification — compare via password_verify
$stmt->execute([$userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$validOtp = null;
foreach ($rows as $row) {
    if (password_verify($otp, $row['otp_code'])) {
        $validOtp = $row;
        break;
    }
}
```

### 4.4 Use Only `REMOTE_ADDR` for Lockout (or Validate Proxy Headers)

```php
public static function getClientIP(): string {
    // Only trust REMOTE_ADDR which cannot be spoofed by the client.
    // If your infrastructure uses a trusted reverse proxy, validate
    // the proxy's IP before accepting X-Forwarded-For.
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}
```

If the application operates behind a known reverse proxy, validate that `REMOTE_ADDR` matches a trusted proxy IP before reading `HTTP_X_FORWARDED_FOR`.

### 4.5 Fix the Remember Me Cookie

```php
// Set with httponly, secure, samesite flags
setcookie('remember_token', $token, [
    'expires'  => time() + (7 * 24 * 60 * 60),
    'path'     => '/',
    'secure'   => true,          // HTTPS only
    'httponly' => true,          // Not accessible via JavaScript
    'samesite' => 'Strict',      // Prevents CSRF via cookie
]);

// Store only a hash of the token in the database
$stmt->execute([hash('sha256', $token), $userId]);
```

On validation, retrieve the stored hash and verify with `hash_equals(hash('sha256', $cookieToken), $storedHash)`.

### 4.6 Move Credentials to Environment Variables

Remove all credentials from `config.php`. Instead, use a `.env` file (not committed to version control) or PHP environment variables:

```php
// modules/core/config/config.php — load from environment
define('SMTP_PASS',  getenv('SMTP_PASS')  ?: '');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
```

Add a `.env.example` file documenting required variables without values, and add `.env` to `.gitignore`.

### 4.7 Use `mime_content_type()` for Server-Side MIME Validation

Never trust `$_FILES['type']`. Read the actual file content signature:

```php
private function validateFile(array $file): bool {
    // ...existing size check...

    // Read MIME type from the actual file content — not the browser value
    $detectedMime = mime_content_type($file['tmp_name']);

    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    if (!in_array($detectedMime, $allowedTypes, true)) {
        throw new Exception("Invalid file type detected.");
    }

    return true;
}
```

### 4.8 Revise Password Policy to Follow NIST SP 800-63B

```php
// Remove the 14-character cap and unique-character rule.
// Enforce:
//   - Minimum 12 characters
//   - Maximum 128 characters (to prevent DoS via bcrypt long-password attacks)
//   - Check against a list of commonly breached passwords

if ($passwordLength < 12 || $passwordLength > 128) {
    echo json_encode(['success' => false,
        'message' => 'Password must be between 12 and 128 characters.']);
    exit;
}

$commonPasswords = ['password123', '123456789', 'qwertyuiop', /* ... */ ];
if (in_array(strtolower($password), $commonPasswords)) {
    echo json_encode(['success' => false,
        'message' => 'This password is too common. Please choose a different one.']);
    exit;
}
```

### 4.9 Persist API Rate Limiting in the Database or Cache

The current in-memory counter resets on every request. Store counts persistently:

```php
private function checkRateLimit(int $apiKeyId): bool {
    $windowStart = date('Y-m-d H:i:s', strtotime('-1 minute'));

    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM api_request_log
        WHERE api_key_id = :id AND requested_at > :window
    ");
    $stmt->execute([':id' => $apiKeyId, ':window' => $windowStart]);
    $count = (int) $stmt->fetchColumn();

    if ($count >= 100) {
        return false;
    }

    // Log this request
    $stmt = $this->db->prepare(
        "INSERT INTO api_request_log (api_key_id, requested_at) VALUES (:id, NOW())"
    );
    $stmt->execute([':id' => $apiKeyId]);

    return true;
}
```

### 4.10 Set `APP_ENV` to `production` and Use `config.local.php`

In `config.local.php` (not committed), override:
```php
define('APP_ENV', 'production');
```

Add a deployment check that halts startup if `APP_ENV` is `'development'` and `$_SERVER['HTTP_HOST']` is a public domain.

### 4.11 Standardise `htmlspecialchars()` Usage

Create a global helper and use it consistently on every dynamic output:

```php
// modules/core/utils/helpers.php
function e(mixed $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, string $encoding = 'UTF-8'): string {
    return htmlspecialchars((string)$value, $flags, $encoding);
}
```

Replace all bare `echo $variable` in views with `echo e($variable)`. Apply this to every template file including dashboard widgets, document listings, search results, audit views, and notification displays.

### 4.12 Add OTP Brute-Force Rate Limiting

Add an attempt counter to the `VerifyOtpController.php`:

```php
// Track failed OTP attempts separately (e.g., in the session or DB)
$_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;

if ($_SESSION['otp_attempts'] >= 5) {
    // Invalidate pending OTP and force re-login
    $stmt = $conn->prepare("UPDATE user_otps SET is_used = 1 WHERE user_id = ? AND is_used = 0");
    $stmt->execute([$userId]);
    session_destroy();
    echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please log in again.']);
    exit;
}
```

### 4.13 Use Uniform Error Messages to Prevent User Enumeration

```php
// Replace distinct "email not found" vs. "invalid password" messages with a single message
echo json_encode([
    'success' => false,
    'message' => 'Invalid email or password.'
]);
```

### 4.14 Add Security HTTP Headers

In a shared header include or `.htaccess`, add the following response headers:

```php
// modules/core/layouts/header.php or a bootstrap file
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:;");
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
```

### 4.15 Implement Session Regeneration on Privilege Escalation

After successful OTP verification (privilege increase from unauthenticated → authenticated), regenerate the session ID to prevent session fixation:

```php
// In VerifyOtpController.php, before setting session variables
session_regenerate_id(true);
```

---

## 5. Why Implement Those Improvements

| Improvement | OWASP / NIST Alignment | Core Justification |
|---|---|---|
| **CSRF Tokens** | OWASP A01 — Broken Access Control | A government system processes document state changes (create, delete, approve). Without CSRF protection, any malicious website a logged-in staff member visits can silently submit those same actions under their credentials. |
| **Cryptographically Secure OTP** | NIST SP 800-63B §5.1.3 | Predictable OTPs undermine the entire purpose of MFA. If the second factor can be guessed, it provides no additional security over a single password. |
| **Hashed OTP Storage** | OWASP A02 — Cryptographic Failures | A SQL injection or database backup leak would expose all active OTPs, allowing an attacker to complete MFA bypass on every currently-pending login. |
| **Fix IP Spoofing in Lockout** | OWASP A07 — Identification & Authentication Failures | The lockout mechanism is the primary defence against brute-force attacks. If it can be bypassed with a single spoofed header, it provides no real protection. |
| **Secure Cookie Flags** | OWASP A02 — Cryptographic Failures | Without `httponly`, any XSS vulnerability can steal the remember-me token and create a persistent backdoor. Without `secure`, the token is transmitted in cleartext over HTTP. |
| **Environment-Based Credentials** | OWASP A05 — Security Misconfiguration | Hardcoded credentials in source code mean every developer, contractor, or code reviewer who accesses the repository has database and email access credentials — and rotating them requires a code change. |
| **Server-Side MIME Validation** | OWASP A03 — Injection / File Upload Abuse | The current check can be bypassed with a malicious filename and forged Content-Type header, potentially enabling server-side code execution if PHP is later configured to serve files from the storage directory. |
| **Revised Password Policy** | NIST SP 800-63B §5.1.1 | The current 14-character maximum actively harms security — it prevents passphrases, which are both stronger and easier to remember. The unique-character rule makes the effective password space much smaller. |
| **Persistent Rate Limiting** | OWASP A07 — Identification & Authentication Failures | An ineffective rate limiter creates a false sense of security. Attackers can make unlimited API calls, enabling credential stuffing, data scraping, and resource exhaustion. |
| **Production Error Handling** | OWASP A05 — Security Misconfiguration | Stack traces visible in production reveal file paths, database schema, and PHP version — all useful reconnaissance for attackers. |
| **Consistent Output Encoding** | OWASP A03 — Cross-Site Scripting (XSS) | Stored XSS in a document management tool is high-severity — an attacker who can inject script into a document title, description, or tag can run arbitrary JavaScript in the browser of any administrator who views it. |
| **OTP Brute-Force Protection** | NIST SP 800-63B §5.1.3 | A 6-digit OTP with no rate limiting can be exhausted in minutes. Locking after 5 failed OTP attempts makes this impractical. |
| **Uniform Error Messages** | OWASP A07 — Identification & Authentication Failures | User enumeration is a reconnaissance step that enables targeted phishing and credential stuffing against known valid accounts. |
| **Security HTTP Headers** | OWASP A05 — Security Misconfiguration | Headers like `Content-Security-Policy` and `X-Frame-Options` provide defence-in-depth: they instruct the browser to apply protections even if code-level XSS or clickjacking vulnerabilities exist. |
| **Session Regeneration** | OWASP A07 — Session Fixation | If an attacker can set a victim's session ID before login (e.g., via network interception on HTTP or an old session fixation bug), they inherit the authenticated session. Regenerating after login breaks this attack. |

---

## 6. Next Steps

These are ordered by priority. Address Critical and High items before deploying to a production environment.

### Phase 1 — Critical (Complete within 1 sprint)

- [x] **Implement CSRF middleware** and integrate it into all POST-handling controllers (login-related forms are lower risk since they are pre-authentication, but document operations, user management, and profile updates are high-priority targets).
- [x] **Replace `mt_rand()` with `random_int()`** for OTP generation — this is a one-line change with no performance trade-off.
- [ ] **Fix IP address resolution** in `Security::getClientIP()` — remove trust of `HTTP_CLIENT_IP` and `HTTP_X_FORWARDED_FOR` unless a verified reverse proxy is configured. (Not planned)
- [ ] **Remove hardcoded credentials** from `config.php`. Create a `config.local.php.example` template and document the required environment variables in the README. (Not planned)

### Phase 2 — High (Complete before first user-acceptance test)

- [x] **Hash OTPs** before storing and use `password_verify()` on retrieval.
- [x] **Add OTP brute-force rate limiting** in `VerifyOtpController.php` — invalidate OTP and force re-login after 5 failed attempts.
- [x] **Replace client MIME type check** with `mime_content_type($file['tmp_name'])` in `DocumentService::validateFile()`.
- [x] **Add `session_regenerate_id(true)`** immediately after successful OTP verification in `VerifyOtpController.php`.

### Phase 3 — Medium (Complete before go-live)

- [x] **Set `APP_ENV = 'production'`** in the deployment `config.local.php` and suppress error display.
- [x] **Audit all view files** for missing `htmlspecialchars()` — search for patterns like `echo $`, `echo $_`, `<?= $` and apply the `e()` helper wrapper.
- [ ] **Implement persistent API rate limiting** using a database log table — the current in-memory counter is non-functional.
- [x] **Unify login error messages** to prevent user enumeration.

### Phase 4 — Hardening (Ongoing / Post-Launch)

- [x] **Add security response headers** globally via a bootstrap include or `.htaccess` (`Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`).
- [x] **Add `.htaccess` protection** to the `storage/` directory to prevent direct HTTP access even if it is web-accessible: `Deny from all`.
- [ ] **Conduct a dedicated XSS audit** of the chatbot, search, notifications, and reports modules, which were not included in this initial review scope.
- [ ] **Consider HTTPS enforcement** at the application level — redirect `http://` to `https://` and set the `secure` cookie flag unconditionally.

---

*This document was generated through static code analysis of the LRMS codebase. It reflects the state of the code as of February 22, 2026. Dynamic testing (penetration testing, fuzzing) may reveal additional vulnerabilities not visible through code review alone.*
