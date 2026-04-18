<div class="dropdown nxl-h-item" wire:poll.{{ $pollSeconds }}s>
    <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
        <i class="feather-bell"></i>
        @if($unreadCount > 0)
            <span class="badge bg-danger nxl-h-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notification-dropdown">
        <div class="d-flex justify-content-between align-items-center nxl-h-dropdown-header">
            <h6 class="fw-bold text-dark mb-0">Notifications</h6>
            @if($unreadCount > 0)
                <a href="javascript:void(0);" wire:click="markAllAsRead" class="fs-11 text-success text-end ms-auto">
                    <i class="feather-check"></i>
                    <span>Mark all as read</span>
                </a>
            @endif
        </div>
        <div class="nxl-h-dropdown-body">
            <div class="nxl-notifications-scroll" style="max-height: 360px; overflow-y: auto;">
                @forelse($notifications as $notification)
                    @php
                        $data  = $notification->data ?? [];
                        $title = $data['title']   ?? 'Notification';
                        $msg   = $data['message'] ?? '';
                        $url   = $data['url']     ?? null;
                        $icon  = $data['icon']    ?? 'feather-bell';
                        $color = $data['color']   ?? 'primary';
                        $isUnread = is_null($notification->read_at);
                    @endphp
                    <a href="{{ $url ?? 'javascript:void(0);' }}"
                       wire:click.prevent="markAsRead('{{ $notification->id }}'){{ $url ? '; window.location = \''.e($url).'\'' : '' }}"
                       class="notification-item d-block text-decoration-none {{ $isUnread ? 'bg-light' : '' }}"
                       style="padding: 10px 14px; border-bottom: 1px solid #f1f3f5;">
                        <div class="d-flex align-items-start gap-2">
                            <div class="notification-icon">
                                <i class="{{ $icon }} bg-soft-{{ $color }} text-{{ $color }}"></i>
                            </div>
                            <div class="notification-content flex-grow-1">
                                <p class="mb-1 fw-semibold text-dark">{{ $title }}</p>
                                <p class="mb-1 small text-muted">{{ $msg }}</p>
                                <span class="fs-11 text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            @if($isUnread)
                                <span class="badge bg-primary rounded-pill" style="width:8px; height:8px; padding:0;"></span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="feather-bell-off fs-4 d-block mb-2"></i>
                        <span class="small">No notifications yet</span>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="text-center nxl-h-dropdown-footer">
            <a href="javascript:void(0);" class="fs-11 fw-bold text-muted">
                {{ $unreadCount }} unread {{ Str::plural('notification', $unreadCount) }}
            </a>
        </div>
    </div>
</div>
