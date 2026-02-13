# OTP Login Implementation Plan

This document outlines the steps required to implement an Email-based One-Time Password (OTP) system for the LLRM System login process.

## 1. Database Changes
We need a table to store the generated OTP codes and their expiration times.

```sql
CREATE TABLE `user_otps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
```

## 2. Configuration (`core/config/config.php`)
Add SMTP configurations for sending emails via Gmail.

```php
// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password'); // Must use App Password for Gmail
define('SMTP_FROM', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'LLRM System Security');

// OTP Settings
define('OTP_EXPIRY_MINUTES', 10);
```

## 3. Email Utility
Since the system doesn't have a mailer, we should integrate **PHPMailer**. 
- Download PHPMailer or install via composer.
- Create a utility `modules/core/utils/Mailer.php` to handle sending the code.

## 4. Modified Login Flow (`LoginController.php`)

### Phase 1: Identity Verification
1. Verify email and password.
2. If correct, generate a 6-digit random numeric code.
3. Save code to `user_otps` with an expiration time (e.g., +10 mins).
4. Send the code to the user's email.
5. Return JSON: `{"success": true, "requires_otp": true, "email": "..."}` instead of logging in immediately.

### Phase 2: OTP Verification (`VerifyOtpController.php`)
1. Create a new controller to handle the OTP check.
2. Validate the code against the database.
3. Check if `expires_at` is still in the future.
4. If valid:
   - Clear existing OTPs for that user.
   - Set the standard login sessions (`user_id`, `role`, etc.).
   - Return `{"success": true, "redirect": "dashboard/views/index.php"}`.

## 5. Frontend UI (`login.php`)
1. Add a second "form" or a section for OTP input (initially hidden).
2. When the login AJAX receives `requires_otp`, hide the login form and slide in the OTP input.
3. Add a "Resend Code" button with a cooldown timer.

## 6. Implementation Checklist
- [ ] Create `user_otps` table.
- [ ] Install PHPMailer.
- [ ] Update `config.php` with SMTP details.
- [ ] Modify `LoginController.php` to handle OTP generation.
- [ ] Create `VerifyOtpController.php` for code validation.
- [ ] Update `login.php` UI to support the 2-step process.
- [ ] Test with a real Gmail account using App Passwords.
