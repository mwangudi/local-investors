<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InAppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $icon = 'feather-bell',
        public string $color = 'primary',
    ) {}

    /**
     * Channels: always database. Email and SMS fan out only when globally
     * enabled via config('notify.*') AND the notifiable has the required
     * contact details.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('notify.email') && ! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        if (config('notify.sms') && $this->resolvePhone($notifiable)) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => $this->type,
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'icon'    => $this->icon,
            'color'   => $this->color,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'member'))
            ->line($this->message);

        if ($this->url) {
            $mail->action('View details', $this->url);
        }

        return $mail->line('— Local Investors');
    }

    public function toSms(object $notifiable): array
    {
        return [
            'to'      => $this->resolvePhone($notifiable),
            'message' => $this->title . ': ' . $this->message,
        ];
    }

    protected function resolvePhone(object $notifiable): ?string
    {
        if (! empty($notifiable->phone)) return $notifiable->phone;

        if (isset($notifiable->member_id)) {
            $notifiable->loadMissing('member');
            return $notifiable->member?->phone ?? null;
        }

        return null;
    }
}

