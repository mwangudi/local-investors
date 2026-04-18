<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'channel',
        'notification_type',
        'recipient',
        'content',
        'status',
        'error_message',
        'retry_count',
        'last_retry_at',
    ];

    protected $casts = [
        'last_retry_at' => 'datetime',
    ];

    /**
     * Get the notifiable entity (e.g., Member).
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: Only failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Notifications that can be retried (failed, today, max 3 retries).
     */
    public function scopeRetryable($query)
    {
        return $query->where('status', 'failed')
            ->whereDate('created_at', today())
            ->where('retry_count', '<', 3);
    }

    /**
     * Mark as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed with error message.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Increment retry count.
     */
    public function incrementRetry(): void
    {
        $this->update([
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
        ]);
    }
}
