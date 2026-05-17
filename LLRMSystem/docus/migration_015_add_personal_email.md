# Migration 015: Add Personal Email Column

```sql
-- Add personal_email column to users table for OTP delivery
ALTER TABLE users 
ADD COLUMN personal_email VARCHAR(255) NULL AFTER email;

-- Add index for faster lookups
CREATE INDEX idx_personal_email ON users(personal_email);
```

**Instructions:**
1. Run this migration to add the personal_email field
2. This allows OTP to be sent to a personal email (e.g., Gmail) while using an official email for login
3. Update existing Super Admin account with personal email
