<?php

namespace Database\Seeders;

use Database\Seeders\Members\Concerns\SeedsContributions;
use Illuminate\Database\Seeder;

/**
 * Contributions confirmed at the Sunday 12 July 2026 chama meeting (held at Kavinya's).
 *
 * Source: handwritten meeting minutes (12/07/2026).
 * Standard 3,500 = shares 1,000 + welfare 500 + MGR 2,000 (MGR is back on in 2026).
 * Members who paid 3,700 = 3,500 + a 200 fine (apology/lateness), paid.
 *
 * Run standalone (NOT in DatabaseSeeder):
 *
 *     php artisan db:seed --class=MeetingJuly2026Seeder
 *
 * Idempotent (firstOrCreate on member + period).
 *
 * NOT included here (recorded elsewhere or pending):
 *   - Catherine — her July row lives in CatherineMasindeSeeder.
 *   - Abigail & Tracy — July was paid in advance (June seeder / advance block).
 *   - Michael — minutes marker unclear; confirm whether he paid July.
 *
 * Meeting resolutions NOT yet recorded (need confirmation before I seed them):
 *   - Mike C: 15,000 from MGR → shares (and asked to leave the group).
 *   - Kavinya: 11,000 to be deducted / 4,000 for Aug-Sep-Nov shares (and asked to leave).
 *   - Sifuna welfare: 5,500 (members contribute 500 this month and next).
 */
class MeetingJuly2026Seeder extends Seeder
{
    use SeedsContributions;

    public function run(): void
    {
        $period = '2026-07-01';
        $paidAt = '2026-07-12';

        // Keyed by partial member name. 'penalty' = a paid 200 fine (member sent 3,700).
        $contributions = [
            'Kavinya'    => ['method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'lateness',    'note' => '+200 lateness fine (paid).'],
            'Torry'      => ['method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'note' => '+200 July apology fine (paid).'],
            'Mike'       => ['method' => 'zimele'],
            'Symon'      => ['method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'note' => '+200 July apology fine (paid).'],
            'Scolastica' => ['method' => 'zimele'],
            'Ngina'      => ['method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'note' => '+200 June apology fine (paid).'],
            'Violet'     => ['method' => 'zimele'],
            'Charles'    => ['method' => 'zimele'],
            'Stella'     => ['method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'note' => '+200 fine (paid; type per minutes — confirm).'],
            'Naomi'      => ['method' => 'cash'],
            'Joseph'     => ['method' => 'mpesa'],
        ];

        foreach ($contributions as $key => $c) {
            $member = $this->resolveMember($key);
            $this->seedRows($member->id, [[
                'period'       => $period,
                'paid_at'      => $paidAt,
                'shares'       => 1000,
                'welfare'      => 500,
                'mgr'          => 2000,
                'method'       => $c['method'],
                'penalty'      => $c['penalty'] ?? 0,
                'penalty_type' => $c['penalty_type'] ?? null,
                'notes'        => 'July 2026 contribution KES 3,500 (12 Jul meeting).' . (isset($c['note']) ? ' ' . $c['note'] : ''),
            ]]);
        }

        // Mike C — July MGR payout KES 15,000 applied to shares (he did not contribute fully;
        // missed Oct 2025). 15,000 = 2,000 Oct (1,000 shares + 1,000 welfare) + 13,000 shares.
        $mike = $this->resolveMember('Mike');
        $this->seedRows($mike->id, [[
            'period'       => '2026-07-13',
            'paid_at'      => '2026-07-13',
            'shares'       => 14000,
            'welfare'      => 1000,
            'mgr'          => 0,
            'method'       => 'merry_go_round',
            'type'         => 'special',
            'notes'        => 'July MGR payout KES 15,000 applied (not cash): covers unpaid Oct 2025 (1,000 shares + 1,000 welfare, Nanyuki) + 13,000 shares top-up.',
        ]]);

        $this->command?->info('MeetingJuly2026Seeder: seeded July 2026 meeting contributions (11 members) + Mike C MGR payout.');
    }
}
