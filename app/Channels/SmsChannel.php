<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notifiable, 'routeNotificationForSms')) {
            Log::info("SmsChannel: Member " . $notifiable->id . " does not have routeNotificationForSms method.");
            return;
        }

        $phone = $notifiable->routeNotificationForSms();
        if (!$phone) {
            Log::info("SmsChannel: Member " . $notifiable->id . " has no phone number.");
            return;
        }

        // Get the message content from the notification
        // We assume the notification has a toSms method
        if (method_exists($notification, 'toSms')) {
            $message = $notification->toSms($notifiable);

            // Placeholder: Log the SMS
            Log::info("SMS SENT to {$phone}: {$message}");

            // TODO: Integrate real SMS provider here (Twilio, AfricasTalking, etc.)
        }
    }
}
