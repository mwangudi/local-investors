<?php

namespace Database\Seeders\Members\Concerns;

use App\Models\Contribution;
use App\Models\Member;

/**
 * Shared helpers for per-member contribution seeders.
 *
 * Each member seeder defines:
 *   - memberKey()           : partial-name match used to resolve the Member
 *   - contributions()       : array of monthly contribution rows
 *   - extras() (optional)   : array of one-off rows (buy-ins, dividends, etc.)
 *
 * Row schema (monthly):
 *   period         (Y-m-d, first day of the month being covered)
 *   paid_at        (Y-m-d, actual payment date)
 *   shares         (decimal)
 *   welfare        (decimal)
 *   mgr            (decimal)
 *   method         (mpesa | zimele | mgr | cash | bank)
 *   notes          (string|null)
 *   penalty        (decimal, default 0)
 *   penalty_type   (lateness|absenteeism|null, default null)
 *   type           (default 'monthly')
 */
trait SeedsContributions
{
    protected function resolveMember(string $key): Member
    {
        $member = Member::where('first_name', 'LIKE', "%{$key}%")
            ->orWhere('last_name', 'LIKE', "%{$key}%")
            ->first();

        if (! $member) {
            throw new \RuntimeException("Member not found for key: {$key}");
        }

        return $member;
    }

    protected function seedRows(int $memberId, array $rows): void
    {
        foreach ($rows as $row) {
            Contribution::firstOrCreate(
                [
                    'member_id'           => $memberId,
                    'contribution_period' => $row['period'],
                ],
                [
                    'shares'         => $row['shares']  ?? 0,
                    'welfare'        => $row['welfare'] ?? 0,
                    'merry_go_round' => $row['mgr']     ?? 0,
                    'paid_at'        => $row['paid_at'],
                    'penalty'        => $row['penalty']      ?? 0,
                    'penalty_type'   => $row['penalty_type'] ?? null,
                    'type'           => $row['type']         ?? 'monthly',
                    'payment_method' => $row['method'],
                    'notes'          => $row['notes']        ?? null,
                ]
            );
        }
    }
}
