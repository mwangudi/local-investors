<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\Income;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfirmedSharesSeeder extends Seeder
{
    // Contributions from the book are all routed through Zimele.
    private const PAYMENT_METHOD = 'zimele';

    /**
     * Reconcile shares against the members' contribution book through August 2026.
     *
     * Blank cells are intentionally omitted. Existing contribution rows retain
     * their welfare, MGR, fines, payment methods, and actual payment dates.
     */
    public function run(): void
    {
        $shares = [
            'Charles' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2026-08'),
            ],
            'Torry' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2026-08'),
            ],
            'Susan Ngina' => [
                '2024-12-31' => 30000,
                '2025-01-01' => 1000, '2025-02-01' => 6000, '2025-03-01' => 1000,
                '2025-04-01' => 1000, '2025-05-01' => 1000, '2025-06-01' => 22000,
                '2025-07-01' => 1000, '2025-08-01' => 1000, '2025-09-01' => 1000,
                '2025-10-01' => 1000,
                ...$this->monthlyShares('2026-01', '2026-07'),
            ],
            'Kavinya' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2026-07'),
            ],
            'Naomi' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2025-10'),
                ...$this->monthlyShares('2026-01', '2026-07'),
            ],
            'Sifuna' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2026-07'),
            ],
            'Abigail' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2026-07'),
            ],
            'Scolastica' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2025-10'),
                ...$this->monthlyShares('2026-01', '2026-07'),
            ],
            'Michael' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2025-10'),
                ...$this->monthlyShares('2026-01', '2026-07'),
            ],
            'Tracy' => [
                '2024-12-31' => 29000,
                ...$this->monthlyShares('2025-01', '2025-07'),
                '2025-08-01' => 11000, '2025-09-01' => 16000,
                ...$this->monthlyShares('2026-01', '2026-07'),
            ],
            'Stella' => [
                '2024-12-31' => 53000,
                ...$this->monthlyShares('2025-01', '2025-09'),
                ...$this->monthlyShares('2026-01', '2026-07'),
            ],
            'Catherine' => [
                '2025-01-01' => 6000, '2025-02-01' => 1800, '2025-03-01' => 1000,
                '2025-04-01' => 1000, '2025-05-01' => 1000, '2025-06-01' => 1000,
                '2025-07-01' => 1000, '2025-08-01' => 1500, '2025-09-01' => 1500,
                '2025-11-01' => 3500, '2025-12-01' => 3500,
                '2026-01-01' => 30500, '2026-02-01' => 1000, '2026-03-01' => 1300,
                '2026-04-01' => 1300, '2026-05-01' => 1000, '2026-06-01' => 11000,
                '2026-07-01' => 1000, '2026-08-01' => 3500,
            ],
            'Symon Peter' => [
                ...$this->monthlyShares('2025-01', '2025-05'),
                '2025-06-01' => 10000,
                ...$this->monthlyShares('2025-07', '2025-10'),
                ...$this->monthlyShares('2026-01', '2026-05'),
                '2026-06-01' => 15000, '2026-07-01' => 1000,
            ],
            'Violet' => [
                ...$this->monthlyShares('2025-01', '2025-05'),
                '2025-06-01' => 10000,
                ...$this->monthlyShares('2025-07', '2025-10'),
                ...$this->monthlyShares('2026-01', '2026-05'),
                '2026-06-01' => 15000, '2026-07-01' => 1000,
            ],
            'Mike C' => [
                '2025-02-01' => 1000, '2025-03-01' => 1000, '2025-04-01' => 4000,
                ...$this->monthlyShares('2025-05', '2025-10'),
                ...$this->monthlyShares('2026-01', '2026-05'),
                '2026-06-01' => 5500, '2026-07-01' => 12500,
            ],
        ];

        DB::transaction(function () use ($shares): void {
            Contribution::query()->update(['shares' => 0]);

            foreach ($shares as $memberName => $periods) {
                $member = $this->resolveMember($memberName);

                foreach ($periods as $period => $amount) {
                    $contribution = Contribution::query()
                        ->where('member_id', $member->id)
                        ->whereDate('contribution_period', $period)
                        ->orderByRaw("CASE WHEN type = 'monthly' THEN 0 ELSE 1 END")
                        ->first();

                    if ($contribution) {
                        $contribution->update(['shares' => $amount]);
                        continue;
                    }

                    Contribution::create([
                        'member_id' => $member->id,
                        'contribution_period' => $period,
                        'shares' => $amount,
                        'welfare' => 0,
                        'merry_go_round' => 0,
                        'penalty' => 0,
                        'type' => $period === '2024-12-31' ? 'opening_balance' : 'monthly',
                        'notes' => 'Shares confirmed from the members contribution book.',
                        'paid_at' => $this->meetingDayOf($period),
                        'payment_method' => self::PAYMENT_METHOD,
                    ]);
                }
            }

            $this->backfillMissingPaymentDates();
            $this->seedRegistrationFees();
        });
    }

    // Search filters break on a null paid_at, so every row gets the meeting day it belongs to.
    private function backfillMissingPaymentDates(): void
    {
        Contribution::query()
            ->whereNull('paid_at')
            ->whereNotNull('contribution_period')
            ->get()
            ->each(function (Contribution $contribution): void {
                $contribution->update([
                    'paid_at' => $this->meetingDayOf($contribution->contribution_period->format('Y-m-d')),
                ]);
            });

        Contribution::query()
            ->whereNull('payment_method')
            ->update(['payment_method' => self::PAYMENT_METHOD]);
    }

    // Meetings are held on the second Sunday of the month.
    private function meetingDayOf(string $period): string
    {
        $firstOfMonth = new \DateTimeImmutable(substr($period, 0, 7) . '-01');

        $firstSunday = $firstOfMonth->format('N') === '7'
            ? $firstOfMonth
            : $firstOfMonth->modify('next sunday');

        return $firstSunday->modify('+7 days')->format('Y-m-d');
    }

    private function monthlyShares(string $startMonth, string $endMonth): array
    {
        $periods = [];
        $current = new \DateTimeImmutable($startMonth . '-01');
        $end = new \DateTimeImmutable($endMonth . '-01');

        while ($current <= $end) {
            $periods[$current->format('Y-m-d')] = 1000;
            $current = $current->modify('+1 month');
        }

        return $periods;
    }

    private function seedRegistrationFees(): void
    {
        $registrations = [
            'Catherine' => '2025-01-12',
            'Violet' => '2025-01-12',
            'Symon Peter' => '2025-01-12',
            'Mike C' => '2025-02-09',
        ];

        foreach ($registrations as $memberName => $joinedAt) {
            $member = $this->resolveMember($memberName);
            $member->update(['join_date' => $joinedAt]);

            Income::updateOrCreate(
                ['category' => 'joining_fee', 'member_id' => $member->id],
                [
                    'amount' => 2000,
                    'received_at' => $joinedAt,
                    'description' => 'Registration fee confirmed from the members contribution book.',
                ]
            );
        }
    }

    private function resolveMember(string $name): Member
    {
        $members = Member::query()->get();
        $member = $members->first(
            fn (Member $member): bool => strcasecmp($member->full_name, $name) === 0
        );

        if (! $member) {
            $matches = $members->filter(
                fn (Member $member): bool => strcasecmp($member->first_name, $name) === 0
                    || strcasecmp($member->last_name, $name) === 0
            );

            if ($matches->count() > 1) {
                throw new \RuntimeException("Ambiguous member name: {$name}");
            }

            $member = $matches->first();
        }

        if (! $member) {
            throw new \RuntimeException("Member not found: {$name}");
        }

        return $member;
    }
}