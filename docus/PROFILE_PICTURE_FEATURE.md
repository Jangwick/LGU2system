# Profile Picture Upload Feature

## Overview
Users can now upload and manage their profile pictures in the LRMS system.

## Features

### Upload Profile Picture
- Navigate to your profile page (click your name in the top-right corner → "My Profile")
- Click the camera icon on the bottom-right of your profile avatar
- Select an image file from your computer
- The image will be uploaded and displayed immediately

### Supported Formats
- JPEG/JPG
- PNG
- GIF
- WEBP

### File Size Limit
- Maximum file size: **5MB**

### Where Profile Pictures Appear
1. **Profile Page** - Large display in the header
2. **Navigation Bar** - Small avatar in top-right corner
3. **Document Activity** - Shows next to your name in logs
4. **User Management** - Displays in user listings (for admins)

## Technical Details

### Database Migration
Run the migration to add the profile_picture column:
```bash
cd database
run_profile_picture_migration.bat
```

### File Storage
- Profile pictures are stored in: `storage/profiles/`
- File naming format: `profile_{user_id}_{timestamp}.{ext}`
- Old profile pictures are automatically deleted when uploading a new one

### Security
- Only image files are allowed
- File type validation on both client and server side
- Maximum file size enforced
- PHP execution disabled in storage directory
- Directory listing disabled

### API Endpoint
**POST** `/modules/user-management/api/upload-profile-picture.php`

**Parameters:**
- `profile_picture` (file) - The image file to upload

**Response:**
```json
{
  "success": true,
  "message": "Profile picture updated successfully",
  "image_url": "http://localhost/storage/profiles/profile_1_1234567890.jpg",
  "filename": "profile_1_1234567890.jpg"
}
```

## User Experience

### Upload Process
1. Click camera button
2. Select image
3. Automatic validation (size, type)
4. Upload with loading spinner
5. Immediate display of new image
6. Success notification

### Error Handling
- File too large → Alert with size limit
- Invalid file type → Alert with supported formats
- Upload failure → Original avatar restored
- Network error → User-friendly error message

## Troubleshooting

### Profile picture not showing
1. Check if `storage/profiles/` directory exists and has write permissions
2. Verify the database column was added successfully
3. Clear browser cache
4. Check file permissions (755 or 775)

### Upload fails
1. Ensure PHP `upload_max_filesize` is at least 5M
2. Check `post_max_size` in php.ini
3. Verify directory write permissions
4. Check error logs for specific issues

### Images not loading
1. Verify `.htaccess` file exists in `storage/profiles/`
2. Check that mod_rewrite is enabled
3. Ensure BASE_URL is correctly configured
4. Check file path in database

## Future Enhancements
- Image cropping tool
- Multiple image formats/sizes for optimization
- Profile picture history
- Default avatars/placeholder images
- Integration with user management dashboard
