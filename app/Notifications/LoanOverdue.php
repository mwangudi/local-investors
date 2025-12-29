<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;
use App\Channels\SmsChannel;

class LoanOverdue extends Notification
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
            $channels[] = 'mail';
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
        $amount = number_format($this->loan->total_payable - $this->loan->total_repaid, 2);

        return (new MailMessage)
            ->subject("Urgent: Loan Overdue")
            ->greeting("Dear {$notifiable->first_name},")
            ->line("Your loan with ID #{$this->loan->id} is now overdue.")
            ->line("Outstanding Balance: KES {$amount} (includes penalties)")
            ->action('Make Payment', url('/admin/loans/' . $this->loan->id))
            ->line('Please settle your balance immediately to avoid further penalties.');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $amount = number_format($this->loan->total_payable - $this->loan->total_repaid, 2);
        return "Urgent: Your loan #{$this->loan->id} is OVERDUE. Balance: KES {$amount}. Please pay immediately.";
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
            'balance' => $this->loan->total_payable - $this->loan->total_repaid,
        ];
    }
}
