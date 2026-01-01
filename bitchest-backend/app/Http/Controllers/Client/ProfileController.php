<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateProfileRequest;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    protected UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = $request->user();

            $user->fill([
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'phone' => trim($request->phone),
            ]);

            $user->save();

            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'user' => $user->fresh()
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating profile', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            ]);

            $user = $request->user();
            $file = $request->file('profile_picture');

            $path = $this->uploadService->uploadProfilePicture($user, $file);
            $url = $this->uploadService->getProfilePictureUrl($user->fresh());

            return response()->json([
                'message' => 'Photo de profil téléchargée avec succès',
                'path' => $path,
                'url' => $url,
                'user' => $user->fresh()
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du téléchargement de la photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload profile banner
     */
    public function uploadProfileBanner(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'profile_banner' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            ]);

            $user = $request->user();
            $file = $request->file('profile_banner');

            $path = $this->uploadService->uploadProfileBanner($user, $file);
            $url = $this->uploadService->getProfileBannerUrl($user->fresh());

            return response()->json([
                'message' => 'Bannière de profil téléchargée avec succès',
                'path' => $path,
                'url' => $url,
                'user' => $user->fresh()
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du téléchargement de la bannière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $deleted = $this->uploadService->deleteProfilePicture($user);

            if ($deleted) {
                return response()->json([
                    'message' => 'Photo de profil supprimée avec succès',
                    'user' => $user->fresh()
                ]);
            }

            return response()->json([
                'message' => 'Aucune photo de profil à supprimer'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile banner
     */
    public function deleteProfileBanner(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $deleted = $this->uploadService->deleteProfileBanner($user);

            if ($deleted) {
                return response()->json([
                    'message' => 'Bannière de profil supprimée avec succès',
                    'user' => $user->fresh()
                ]);
            }

            return response()->json([
                'message' => 'Aucune bannière de profil à supprimer'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la bannière',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

