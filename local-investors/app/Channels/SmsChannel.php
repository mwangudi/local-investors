<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    /**
     * Send the given notification. The Notification must define `toSms($notifiable)`
     * which returns an array: ['to' => '+2547...', 'message' => '...'].
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $payload = $notification->toSms($notifiable);
        $to      = $payload['to']      ?? null;
        $message = $payload['message'] ?? null;

        if (! $to || ! $message) {
            return;
        }

        $driver = config('notify.sms_driver', 'log');

        // Default stub: log the SMS. Wire real providers (africastalking, twilio)
        // by extending this switch.
        switch ($driver) {
            case 'log':
            default:
                Log::channel(config('logging.default'))
                    ->info('[SMS stub] ' . $to . ': ' . $message, [
                        'from' => config('notify.sms_from'),
                    ]);
                break;
        }
    }
}
