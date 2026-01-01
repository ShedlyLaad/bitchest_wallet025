# Profile Picture and Banner Upload Setup

This document explains how to set up profile picture and banner upload functionality.

## Backend Setup

### 1. Run the Migration

First, run the migration to add the `profile_picture` and `profile_banner` columns to the users table:

```bash
cd bitchest-backend
php artisan migrate
```

### 2. Create Storage Link

Laravel needs a symbolic link from `public/storage` to `storage/app/public` to serve uploaded files:

```bash
php artisan storage:link
```

This will create a symbolic link that allows the application to serve files from the `storage/app/public` directory through the web.

### 3. Verify Storage Configuration

Make sure your `.env` file has the correct storage configuration:

```env
FILESYSTEM_DISK=public
```

The default configuration in `config/filesystems.php` should already be set up correctly.

### 4. Directory Permissions

Ensure the storage directories have the correct permissions:

```bash
# On Linux/Mac
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# On Windows (if using WSL or similar)
# The permissions should be handled automatically
```

## API Endpoints

### Upload Profile Picture
- **Method:** `POST`
- **URL:** `/api/profile/picture`
- **Auth:** Required (Client role)
- **Content-Type:** `multipart/form-data`
- **Body:** `profile_picture` (file, max 5MB, images only)

### Upload Profile Banner
- **Method:** `POST`
- **URL:** `/api/profile/banner`
- **Auth:** Required (Client role)
- **Content-Type:** `multipart/form-data`
- **Body:** `profile_banner` (file, max 5MB, images only)

### Delete Profile Picture
- **Method:** `DELETE`
- **URL:** `/api/profile/picture`
- **Auth:** Required (Client role)

### Delete Profile Banner
- **Method:** `DELETE`
- **URL:** `/api/profile/banner`
- **Auth:** Required (Client role)

## File Storage Structure

Uploaded files are stored in:
- Profile pictures: `storage/app/public/profile_pictures/{user_id}/`
- Profile banners: `storage/app/public/profile_banners/{user_id}/`

Files are served through: `http://your-domain/storage/{path}`

## Frontend Integration

The frontend automatically handles:
- Image preview
- File validation (type and size)
- Upload progress
- Error handling
- Image display with proper URLs

## Testing

1. After running migrations, test the upload functionality:
   - Navigate to the profile page
   - Click the camera icon on the profile picture or banner
   - Select an image file
   - Verify the upload succeeds

2. Check that images are accessible:
   - The uploaded images should be visible immediately
   - Check the browser network tab to ensure images load correctly

## Troubleshooting

### Images not displaying
- Verify the storage link exists: `php artisan storage:link`
- Check file permissions on the storage directory
- Verify the `APP_URL` in `.env` matches your actual domain

### Upload fails
- Check file size (max 5MB)
- Verify file type (only images: jpeg, jpg, png, gif, webp)
- Check Laravel logs: `storage/logs/laravel.log`
- Verify storage disk is set to 'public' in config

### 419 CSRF Token Mismatch
- Ensure CSRF token is being sent with requests
- Check that Sanctum is properly configured
- Verify cookies are being sent correctly

