# Profile Pictures Storage

This directory stores user profile pictures.

## Security
- Only image files (JPG, PNG, GIF, WEBP) are allowed
- Maximum file size: 5MB
- Directory listing is disabled
- PHP execution is disabled

## File Naming Convention
Files are named as: `profile_{user_id}_{timestamp}.{extension}`

## Permissions
Ensure this directory has write permissions (755 or 775)
