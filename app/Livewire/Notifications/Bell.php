<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\Attributes\On;

class Bell extends Component
{
    public int $pollSeconds = 30;

    #[On('notification-read')]
    public function refresh(): void
    {
        // no-op — render will re-run
    }

    public function markAsRead(string $id): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }
        $n = $user->notifications()->whereKey($id)->first();
        if ($n) {
            $n->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }
        $user->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = auth()->user();

        $notifications = $user
            ? $user->notifications()->latest()->limit(10)->get()
            : collect();

        $unreadCount = $user
            ? $user->unreadNotifications()->count()
            : 0;

        return view('livewire.notifications.bell', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}
