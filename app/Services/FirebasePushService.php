<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Contract\Messaging as FirebaseMessaging;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    private FirebaseMessaging $messaging;

    public function __construct(FirebaseMessaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Send a push notification to a specific user.
     *
     * @param User $user The user to notify.
     * @param string $title The title of the notification.
     * @param string $body The body of the notification.
     * @param array $data Optional data payload for the app to handle navigation.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        // Récupérer tous les tokens valides pour cet utilisateur
        $deviceTokens = $user->deviceTokens()->pluck('token')->filter()->values()->all();

        if (empty($deviceTokens)) {
            Log::info("No device tokens found for user {$user->id} to send notification.");
            return;
        }

        // Construire la notification
        $notification = Notification::create($title, $body);

        // Construire le message avec la notification et les données
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);

        try {
            // Envoyer le message à tous les appareils de l'utilisateur
            $report = $this->messaging->sendMulticast($message, $deviceTokens);

            if ($report->hasFailures()) {
                Log::warning('FCM push notification sending had failures.', [
                    'success_count' => $report->successes()->count(),
                    'failure_count' => $report->failures()->count(),
                ]);

                // Log des tokens qui ont échoué pour nettoyage éventuel
                foreach ($report->failures()->getItems() as $failure) {
                    Log::debug("Failed token: " . $failure->target()->value());
                }
            } else {
                Log::info("Successfully sent push notification to user {$user->id}.");
            }

        } catch (\Throwable $e) {
            Log::error('Failed to send FCM push notification.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
