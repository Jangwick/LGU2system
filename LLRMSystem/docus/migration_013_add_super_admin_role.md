# Migration 013: Add Super Admin Role

```sql
-- Add super_admin role to users table (if using ENUM)
-- ALTER TABLE users MODIFY COLUMN role ENUM('viewer', 'staff', 'officer', 'administrator', 'super_admin');

-- Or if using VARCHAR, no schema change needed

-- Update existing administrator to super_admin if needed
-- UPDATE users SET role = 'super_admin' WHERE email = 'admin@lgusystem.gov';

-- Add super_admin specific permissions to permissions table if using permission table
-- INSERT INTO permissions (name, description) VALUES 
-- ('admin.manage', 'Manage administrator accounts'),
-- ('system.config', 'Access system configuration'),
-- ('database.backup', 'Perform database backup and restore');

-- Assign these permissions to super_admin role
-- INSERT INTO role_permissions (role_id, permission_id)
-- SELECT (SELECT id FROM roles WHERE name = 'super_admin'), id 
-- FROM permissions WHERE name IN ('admin.manage', 'system.config', 'database.backup');
```

**Instructions:**
1. Run this migration to add super_admin role support
2. Manually create the first super_admin account via database or through an existing admin account
3. Test that super_admin has all administrator permissions plus admin.manage, system.config, and database.backup
