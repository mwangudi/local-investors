<?php

namespace App\Services;

use App\Models\User;
use App\Models\Member;
use App\Notifications\InAppNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotifyService
{
    /**
     * Staff who should receive operational notifications: admins + treasurers.
     */
    public static function admins()
    {
        return User::role(['admin', 'treasurer'])->get();
    }

    /**
     * Find the portal user tied to a given member (if any).
     */
    public static function userForMember(Member|int|null $member): ?User
    {
        if (! $member) {
            return null;
        }
        $memberId = $member instanceof Member ? $member->id : $member;
        return User::where('member_id', $memberId)->first();
    }

    public static function toAdmins(InAppNotification $notification): void
    {
        $admins = self::admins();
        if ($admins->isNotEmpty()) {
            NotificationFacade::send($admins, $notification);
        }
    }

    public static function toMember(Member|int|null $member, InAppNotification $notification): void
    {
        $user = self::userForMember($member);
        if ($user) {
            $user->notify($notification);
        }
    }

    public static function toAll(Member|int|null $member, InAppNotification $notification): void
    {
        self::toAdmins($notification);
        self::toMember($member, $notification);
    }
}
