<?php

namespace App\Console\Commands;

use App\Channels\SmsChannel;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetryFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:retry {--dry-run : Show what would be retried without actually retrying}';

    /**
     * The console command description.
     */
    protected $description = 'Retry failed notifications from today (max 3 retries)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retryableNotifications = NotificationLog::retryable()->get();

        if ($retryableNotifications->isEmpty()) {
            $this->info('No failed notifications to retry.');
            return Command::SUCCESS;
        }

        $this->info("Found {$retryableNotifications->count()} notification(s) to retry.");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Channel', 'Recipient', 'Retry #', 'Created At'],
                $retryableNotifications->map(fn($n) => [
                    $n->id,
                    $n->channel,
                    $n->recipient,
                    $n->retry_count + 1,
                    $n->created_at->format('H:i:s'),
                ])
            );
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($retryableNotifications as $notification) {
            $notification->incrementRetry();
            $this->line("Retrying {$notification->channel} to {$notification->recipient}...");

            try {
                if ($notification->channel === 'sms') {
                    $success = $this->retrySms($notification);
                } elseif ($notification->channel === 'email') {
                    $success = $this->retryEmail($notification);
                } else {
                    $this->warn("Unknown channel: {$notification->channel}");
                    continue;
                }

                if ($success) {
                    $successCount++;
                    $this->info("  ✅ Success");
                } else {
                    $failCount++;
                    $this->error("  ❌ Failed");
                }
            } catch (\Exception $e) {
                $failCount++;
                $notification->markAsFailed($e->getMessage());
                $this->error("  ❌ Exception: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Completed: {$successCount} succeeded, {$failCount} failed.");

        return Command::SUCCESS;
    }

    /**
     * Retry an SMS notification.
     */
    protected function retrySms(NotificationLog $notification): bool
    {
        $smsChannel = new SmsChannel();
        return $smsChannel->sendViaSms(
            $notification->recipient,
            $notification->content,
            $notification
        );
    }

    /**
     * Retry an email notification.
     */
    protected function retryEmail(NotificationLog $notification): bool
    {
        try {
            // Parse the content to get subject and body
            $lines = explode("\n", $notification->content);
            $subject = 'Notification Retry';
            $body = $notification->content;

            // Try to extract subject from content
            if (count($lines) > 0 && str_starts_with($lines[0], 'Subject: ')) {
                $subject = substr($lines[0], 9);
                $body = implode("\n", array_slice($lines, 1));
            }

            Mail::raw($body, function ($message) use ($notification, $subject) {
                $message->to($notification->recipient)
                    ->subject($subject);
            });

            $notification->markAsSent();
            return true;
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error("Email retry failed: " . $e->getMessage());
            return false;
        }
    }
}
