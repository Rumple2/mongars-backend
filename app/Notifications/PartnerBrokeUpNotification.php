<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class PartnerBrokeUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $userName;

    /**
     * Create a new notification instance.
     *
     * @param string $userName The name of the user who initiated the breakup.
     */
    public function __construct($userName)
    {
        $this->userName = $userName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['firebase'];
    }

    /**
     * Get the Firebase representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Kreait\Firebase\Messaging\CloudMessage
     */
    public function toFirebase($notifiable)
    {
        $notification = FirebaseNotification::create(
            'Relation terminée',
            "Votre relation avec {$this->userName} a pris fin."
        );

        return CloudMessage::new()
            ->withNotification($notification)
            ->withData([
                'type' => 'RELATIONSHIP_ENDED',
                'partner_name' => $this->userName,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'RELATIONSHIP_ENDED',
            'title' => 'Relation terminée',
            'body' => "Votre relation avec {$this->userName} a pris fin.",
            'partner_name' => $this->userName,
        ];
    }
}
