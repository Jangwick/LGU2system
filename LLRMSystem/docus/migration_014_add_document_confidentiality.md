# Migration 014: Add Document Confidentiality Fields

```sql
-- Add confidentiality level column to legislative_documents table
ALTER TABLE legislative_documents 
ADD COLUMN confidentiality_level ENUM('public', 'internal', 'confidential', 'restricted') DEFAULT 'public' 
AFTER status;

-- Add encryption flag column
ALTER TABLE legislative_documents 
ADD COLUMN is_encrypted BOOLEAN DEFAULT FALSE 
AFTER confidentiality_level;

-- Add encryption key column (for storing encrypted document keys)
ALTER TABLE legislative_documents 
ADD COLUMN encryption_key VARCHAR(255) NULL 
AFTER is_encrypted;

-- Add index for faster filtering by confidentiality
CREATE INDEX idx_confidentiality_level ON legislative_documents(confidentiality_level);

-- Update existing documents to 'public' by default
UPDATE legislative_documents SET confidentiality_level = 'public' WHERE confidentiality_level IS NULL;
```

**Instructions:**
1. Run this migration to add document confidentiality support
2. The confidentiality levels are:
   - `public`: Accessible to all authenticated users
   - `internal`: Accessible to staff, officers, administrators, super admins
   - `confidential`: Accessible to officers, administrators, super admins only
   - `restricted`: Accessible to super admins only
3. Documents marked as `is_encrypted = TRUE` will have their files encrypted at rest
4. Confidential/restricted documents will appear blurred in preview and require password authentication
