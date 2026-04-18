<?php

namespace App\Channels;

use App\Models\NotificationLog;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    /**
     * Send the given notification via Infobip SMS.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notifiable, 'routeNotificationForSms')) {
            Log::info("SmsChannel: Notifiable does not have routeNotificationForSms method.");
            return;
        }

        $phone = $notifiable->routeNotificationForSms();
        if (!$phone) {
            Log::info("SmsChannel: No phone number available for notification.");
            return;
        }

        // Get the message content from the notification
        if (!method_exists($notification, 'toSms')) {
            Log::info("SmsChannel: Notification does not have toSms method.");
            return;
        }

        $message = $notification->toSms($notifiable);

        // Create log entry
        $logEntry = NotificationLog::create([
            'notifiable_id' => $notifiable->getKey(),
            'notifiable_type' => get_class($notifiable),
            'channel' => 'sms',
            'notification_type' => get_class($notification),
            'recipient' => $phone,
            'content' => $message,
            'status' => 'pending',
        ]);

        // Send via Infobip
        $this->sendViaSms($phone, $message, $logEntry);
    }

    /**
     * Send SMS via Infobip API.
     */
    public function sendViaSms(string $phone, string $message, ?NotificationLog $logEntry = null): bool
    {
        $baseUrl = config('infobip.base_url');
        $apiKey = config('infobip.api_key');
        $senderId = config('infobip.sender_id');

        if (empty($apiKey) || empty($baseUrl)) {
            $error = "Infobip API credentials are not configured.";
            Log::error("SmsChannel: {$error}");
            $logEntry?->markAsFailed($error);
            return false;
        }

        // Format phone number
        $formattedPhone = $this->formatPhoneNumber($phone);

        // Build the endpoint URL
        $endpoint = "https://{$baseUrl}/sms/2/text/advanced";

        // Build the request payload
        $payload = [
            'messages' => [
                [
                    'destinations' => [
                        ['to' => $formattedPhone]
                    ],
                    'text' => $message,
                ]
            ]
        ];

        // Add sender ID if configured
        if (!empty($senderId)) {
            $payload['messages'][0]['from'] = $senderId;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("SMS sent successfully to {$formattedPhone}", $data);
                $logEntry?->markAsSent();
                return true;
            } else {
                $error = "HTTP {$response->status()}: " . $response->body();
                Log::error("SMS sending failed", [
                    'phone' => $formattedPhone,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                $logEntry?->markAsFailed($error);
                return false;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error("SMS sending exception: " . $error, [
                'phone' => $formattedPhone,
            ]);
            $logEntry?->markAsFailed($error);
            return false;
        }
    }

    /**
     * Format phone number to international format.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any spaces or dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);

        // If starts with 0, assume Kenya and convert to 254
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }

        // Remove + if present (Infobip prefers without +)
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}
