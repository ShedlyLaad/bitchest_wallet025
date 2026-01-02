<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Récupère les notifications de l'utilisateur authentifié
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = (int) $request->input('per_page', 20);
        $unreadOnly = $request->boolean('unread_only', false);

        $query = Notification::where('user_id', $user->id)
            ->with(['crypto', 'portfolio'])
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate($perPage);

        return response()->json($notifications);
    }

    /**
     * Récupère le nombre de notifications non lues
     */
    public function unreadCount()
    {
        $user = auth()->user();
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead(int $id)
    {
        $user = auth()->user();
        $success = $this->notificationService->markAsRead($id, $user->id);

        if ($success) {
            return response()->json(['message' => 'Notification marquée comme lue']);
        }

        return response()->json(['message' => 'Notification non trouvée'], 404);
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $count = $this->notificationService->markAllAsRead($user->id);

        return response()->json([
            'message' => "{$count} notification(s) marquée(s) comme lue(s)",
            'count' => $count
        ]);
    }

    /**
     * Supprime une notification
     */
    public function destroy(int $id)
    {
        $user = auth()->user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            $notification->delete();
            return response()->json(['message' => 'Notification supprimée']);
        }

        return response()->json(['message' => 'Notification non trouvée'], 404);
    }
}

