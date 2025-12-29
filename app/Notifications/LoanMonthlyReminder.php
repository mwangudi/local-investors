<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;
use App\Channels\SmsChannel;

class LoanMonthlyReminder extends Notification
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
        $amount = number_format($this->loan->balance, 2);
        // Assuming monthly interest is the standard interest logic we have or specifically for this month
        // For simplicity, we show the total outstanding
        $interest = number_format($this->loan->standard_interest, 2);
        $dueMonth = $this->loan->due_at ? \Carbon\Carbon::parse($this->loan->due_at)->format('F Y') : 'N/A';
        $dueDate = $this->loan->due_at ? \Carbon\Carbon::parse($this->loan->due_at)->format('d M Y') : 'N/A';

        return (new MailMessage)
            ->subject("Monthly Loan Reminder")
            ->greeting("Dear {$notifiable->first_name},")
            ->line("This is a reminder regarding your loan (ID #{$this->loan->id}).")
            ->line("Outstanding Balance: KES {$amount}")
            ->line("Total Interest: KES {$interest}")
            ->line("Due Month: {$dueMonth} (Due Date: {$dueDate})")
            ->action('View Loan Details', url('/admin/loans/' . $this->loan->id))
            ->line('Please ensure your payments are up to date.');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $amount = number_format($this->loan->balance, 2);
        $dueMonth = $this->loan->due_at ? \Carbon\Carbon::parse($this->loan->due_at)->format('M Y') : 'N/A';

        return "Reminder: Your loan #{$this->loan->id} balance is KES {$amount}. Due: {$dueMonth}. Please pay on time.";
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
            'balance' => $this->loan->balance,
        ];
    }
}
