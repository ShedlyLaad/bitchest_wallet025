<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class UploadService
{
    /**
     * Get the public storage path
     */
    private function getPublicStoragePath(): string
    {
        return public_path('storage');
    }
    /**
     * Upload profile picture for user
     */
    public function uploadProfilePicture(User $user, UploadedFile $file): string
    {
        try {
            // Log incoming file info
            \Log::info('Starting uploadProfilePicture', [
                'user_id' => $user->id,
                'client_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
                'client_ext' => $file->getClientOriginalExtension(),
            ]);

            // Validate file
            $this->validateImageFile($file);

            // Use existing ProfileImage directory structure in public/storage
            $baseDir = $this->getPublicStoragePath() . DIRECTORY_SEPARATOR . 'ProfileImage';
            $userDir = $baseDir . DIRECTORY_SEPARATOR . $user->id;
            
            // Ensure directories exist
            if (!File::exists($baseDir)) {
                File::makeDirectory($baseDir, 0755, true);
            }
            if (!File::exists($userDir)) {
                File::makeDirectory($userDir, 0755, true);
            }

            // Delete old picture if exists
            if ($user->profile_picture) {
                // Convert database path to filesystem path
                $dbPath = str_replace('\\', '/', $user->profile_picture);
                $oldPath = $this->getPublicStoragePath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dbPath);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                    \Log::info('Deleted previous profile picture', [
                        'user_id' => $user->id,
                        'previous_path' => $oldPath,
                    ]);
                }
            }
            
            // Generate unique filename with timestamp
            $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $fullPath = $userDir . DIRECTORY_SEPARATOR . $filename;

            // Store the file
            \Log::info('Storing profile picture to disk', [
                'directory' => $userDir,
                'filename' => $filename,
                'full_path' => $fullPath,
            ]);

            // Move uploaded file (move() throws exception on failure)
            try {
                $file->move($userDir, $filename);
            } catch (\Exception $moveException) {
                \Log::error('Failed to move file', [
                    'error' => $moveException->getMessage(),
                    'destination' => $fullPath
                ]);
                throw new \Exception('Failed to move uploaded file: ' . $moveException->getMessage());
            }
            
            // Relative path for database storage
            $path = 'ProfileImage/' . $user->id . '/' . $filename;

            \Log::info('File moved successfully', ['path' => $path, 'full_path' => $fullPath]);

            // Update user record
            $user->update(['profile_picture' => $path]);

            \Log::info('Profile picture uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'filename' => $filename
            ]);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Error uploading profile picture', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Upload profile banner for user
     */
    public function uploadProfileBanner(User $user, UploadedFile $file): string
    {
        try {
            // Log incoming file info
            \Log::info('Starting uploadProfileBanner', [
                'user_id' => $user->id,
                'client_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
                'client_ext' => $file->getClientOriginalExtension(),
            ]);

            // Validate file
            $this->validateImageFile($file);

            // Use existing BannierImage directory structure in public/storage
            $baseDir = $this->getPublicStoragePath() . DIRECTORY_SEPARATOR . 'BannierImage';
            $userDir = $baseDir . DIRECTORY_SEPARATOR . $user->id;
            
            // Ensure directories exist
            if (!File::exists($baseDir)) {
                File::makeDirectory($baseDir, 0755, true);
            }
            if (!File::exists($userDir)) {
                File::makeDirectory($userDir, 0755, true);
            }

            // Delete old banner if exists
            if ($user->profile_banner) {
                // Convert database path to filesystem path
                $dbPath = str_replace('\\', '/', $user->profile_banner);
                $oldPath = $this->getPublicStoragePath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dbPath);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                    \Log::info('Deleted previous profile banner', [
                        'user_id' => $user->id,
                        'previous_path' => $oldPath,
                    ]);
                }
            }
            
            // Generate unique filename with timestamp
            $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $fullPath = $userDir . DIRECTORY_SEPARATOR . $filename;

            // Store the file
            \Log::info('Storing profile banner to disk', [
                'directory' => $userDir,
                'filename' => $filename,
                'full_path' => $fullPath,
            ]);

            // Move uploaded file (move() throws exception on failure)
            try {
                $file->move($userDir, $filename);
            } catch (\Exception $moveException) {
                \Log::error('Failed to move file', [
                    'error' => $moveException->getMessage(),
                    'destination' => $fullPath
                ]);
                throw new \Exception('Failed to move uploaded file: ' . $moveException->getMessage());
            }
            
            // Relative path for database storage
            $path = 'BannierImage/' . $user->id . '/' . $filename;

            \Log::info('File moved successfully', ['path' => $path, 'full_path' => $fullPath]);

            // Update user record
            $user->update(['profile_banner' => $path]);

            \Log::info('Profile banner uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'filename' => $filename
            ]);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Error uploading profile banner', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(User $user): bool
    {
        try {
            if ($user->profile_picture) {
                // Convert database path to filesystem path
                $dbPath = str_replace('\\', '/', $user->profile_picture);
                $filePath = $this->getPublicStoragePath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dbPath);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                    $user->update(['profile_picture' => null]);
                    
                    \Log::info('Profile picture deleted successfully', [
                        'user_id' => $user->id,
                        'deleted_path' => $filePath
                    ]);
                    
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Error deleting profile picture', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete profile banner
     */
    public function deleteProfileBanner(User $user): bool
    {
        try {
            if ($user->profile_banner) {
                // Convert database path to filesystem path
                $dbPath = str_replace('\\', '/', $user->profile_banner);
                $filePath = $this->getPublicStoragePath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dbPath);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                    $user->update(['profile_banner' => null]);
                    
                    \Log::info('Profile banner deleted successfully', [
                        'user_id' => $user->id,
                        'deleted_path' => $filePath
                    ]);
                    
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Error deleting profile banner', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get profile picture URL
     */
    public function getProfilePictureUrl(User $user): ?string
    {
        if ($user->profile_picture) {
            // Ensure the path uses forward slashes and doesn't have double slashes
            $path = str_replace('\\', '/', $user->profile_picture);
            $path = ltrim($path, '/');
            return url('storage/' . $path);
        }
        return null;
    }

    /**
     * Get profile banner URL
     */
    public function getProfileBannerUrl(User $user): ?string
    {
        if ($user->profile_banner) {
            // Ensure the path uses forward slashes and doesn't have double slashes
            $path = str_replace('\\', '/', $user->profile_banner);
            $path = ltrim($path, '/');
            return url('storage/' . $path);
        }
        return null;
    }

    /**
     * Validate image file
     */
    private function validateImageFile(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size of 5MB.');
        }
    }
}

