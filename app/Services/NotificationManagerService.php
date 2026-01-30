<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification as DbNotification; // Alias pour éviter le conflit de nom
use Illuminate\Support\Facades\Log;

class NotificationManagerService
{
    private FirebasePushService $pushService;

    public function __construct(FirebasePushService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Create a database notification and send a push notification.
     *
     * @param User $recipient The user who will receive the notification.
     * @param string $type The type of notification (e.g., 'couple_request', 'couple_request_accepted').
     * @param string $title The title of the notification.
     * @param string $body The body/content of the notification.
     * @param array $data Optional data payload for the app (e.g., ['request_id' => 'uuid']).
     * @return DbNotification|null The created database notification, or null if creation failed.
     */
    public function send(User $recipient, string $type, string $title, string $body, array $data = []): ?DbNotification
    {
        try {
            // 1. Create database notification
            $dbNotification = DbNotification::create([
                'user_id' => $recipient->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'read_at' => null, // Initially unread
            ]);

            // 2. Send push notification
            $this->pushService->sendToUser($recipient, $title, $body, $data);

            Log::info("Notification sent and saved for user {$recipient->id}. Type: {$type}");

            return $dbNotification;

        } catch (\Throwable $e) {
            Log::error('Failed to send or save notification.', [
                'recipient_id' => $recipient->id,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
