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

        // Normaliser les données pour garantir la cohérence des types
        $notifications->getCollection()->transform(function ($notification) {
            // Récupérer le symbole crypto depuis la relation ou le champ direct
            $cryptoSymbol = $notification->crypto_symbol;
            if (!$cryptoSymbol && $notification->crypto) {
                $cryptoSymbol = $notification->crypto->symbol;
            }
            
            return [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'portfolio_id' => $notification->portfolio_id,
                'crypto_currency_id' => $notification->crypto_currency_id,
                'type' => $notification->type,
                'title' => $notification->title ?? 'Notification',
                'message' => $notification->message ?? '',
                'crypto_symbol' => $cryptoSymbol,
                'gain_loss' => $notification->gain_loss !== null ? (float) $notification->gain_loss : null,
                'gain_loss_percent' => $notification->gain_loss_percent !== null ? (float) $notification->gain_loss_percent : null,
                'current_price' => $notification->current_price !== null ? (float) $notification->current_price : null,
                'previous_price' => $notification->previous_price !== null ? (float) $notification->previous_price : null,
                'is_read' => (bool) $notification->is_read,
                'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
                'level' => $notification->level !== null ? (int) $notification->level : null,
                'level_name' => $notification->level_name ?? null,
                'created_at' => $notification->created_at->toISOString(),
                'updated_at' => $notification->updated_at->toISOString(),
                'crypto' => $notification->crypto ? [
                    'id' => $notification->crypto->id,
                    'name' => $notification->crypto->name,
                    'symbol' => $notification->crypto->symbol,
                ] : null,
            ];
        });

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

