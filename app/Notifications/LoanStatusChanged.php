<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;
use App\Channels\SmsChannel;
use App\Channels\LoggingMailChannel;

class LoanStatusChanged extends Notification
{
    use Queueable;

    public $loan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        $preference = $notifiable->notification_preference ?? 'email';

        if ($preference === 'email' || $preference === 'both') {
            $channels[] = LoggingMailChannel::class;
        }

        if ($preference === 'sms' || $preference === 'both') {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->loan->status);
        $amount = number_format($this->loan->amount, 2);

        return (new MailMessage)
            ->subject("Loan Status Update: {$status}")
            ->greeting("Dear {$notifiable->first_name},")
            ->line("Your loan application for KES {$amount} status has been updated to: {$status}.")
            ->action('View Details', url('/admin/loans/' . $this->loan->id)) // Assuming a route exists, or generic link
            ->line('Thank you for being a valued member!');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $status = ucfirst($this->loan->status);
        $amount = number_format($this->loan->amount, 2);

        return "Your loan application for KES {$amount} is now {$status}.";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'status' => $this->loan->status,
            'amount' => $this->loan->amount,
        ];
    }
}
