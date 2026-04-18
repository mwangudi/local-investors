<?php

namespace App\Channels;

use App\Models\NotificationLog;
use Illuminate\Mail\MailManager;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class LoggingMailChannel extends MailChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        $email = $notifiable->routeNotificationFor('mail', $notification);
        
        if (!$email) {
            Log::info("LoggingMailChannel: No email available for notification.");
            return;
        }

        // Get the mail message to extract content for logging
        $mailMessage = $notification->toMail($notifiable);
        $content = $this->extractMailContent($mailMessage);

        // Create log entry
        $logEntry = NotificationLog::create([
            'notifiable_id' => $notifiable->getKey(),
            'notifiable_type' => get_class($notifiable),
            'channel' => 'email',
            'notification_type' => get_class($notification),
            'recipient' => is_array($email) ? implode(', ', $email) : $email,
            'content' => $content,
            'status' => 'pending',
        ]);

        try {
            // Call parent to actually send the email
            parent::send($notifiable, $notification);
            $logEntry->markAsSent();
            Log::info("Email sent successfully to {$email}");
        } catch (\Exception $e) {
            $logEntry->markAsFailed($e->getMessage());
            Log::error("Email sending failed: " . $e->getMessage(), [
                'email' => $email,
            ]);
            throw $e; // Re-throw so Laravel knows it failed
        }
    }

    /**
     * Extract content from mail message for logging.
     */
    protected function extractMailContent($mailMessage): string
    {
        $parts = [];

        if ($mailMessage->subject ?? null) {
            $parts[] = "Subject: {$mailMessage->subject}";
        }

        if ($mailMessage->greeting ?? null) {
            $parts[] = $mailMessage->greeting;
        }

        foreach ($mailMessage->introLines ?? [] as $line) {
            $parts[] = $line;
        }

        foreach ($mailMessage->outroLines ?? [] as $line) {
            $parts[] = $line;
        }

        return implode("\n", $parts);
    }
}
