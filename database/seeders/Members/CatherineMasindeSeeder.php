<?php

namespace Database\Seeders\Members;

use Database\Seeders\Members\Concerns\SeedsContributions;
use Illuminate\Database\Seeder;

/**
 * Catherine Masinde — full contribution history (Jan 2025 → May 2026).
 *
 * Source: chama diary record (matches Zimele statement; first payment was M-Pesa to treasurer).
 * MGR contributions begin Jul 2025; Jan–Jun 2025 are shares + welfare only.
 * Joining payment Jan 2025 (3,500) was sent directly to the treasurer via M-Pesa;
 * all subsequent payments routed through Zimele standing order.
 */
class CatherineMasindeSeeder extends Seeder
{
    use SeedsContributions;

    public function run(): void
    {
        $member = $this->resolveMember('Catherine');

        $this->seedRows($member->id, [
            // Pre-MGR period — shares + welfare only (Jan–Jun 2025)
            ['period' => '2025-01-01', 'paid_at' => '2025-01-15', 'shares' => 3000, 'welfare' => 500, 'mgr' => 0,    'method' => 'mpesa',  'notes' => 'Joining payment to treasurer via M-Pesa. KES 3,500 (shares 3,000 + welfare 500). Pre-MGR.'],
            ['period' => '2025-02-01', 'paid_at' => '2025-01-30', 'shares' => 4500, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 5,000 (shares 4,500 + welfare 500). Pre-MGR.'],
            ['period' => '2025-03-01', 'paid_at' => '2025-03-03', 'shares' => 4500, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 5,000 (shares 4,500 + welfare 500). Pre-MGR.'],
            ['period' => '2025-04-01', 'paid_at' => '2025-03-28', 'shares' => 3000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (shares 3,000 + welfare 500). Pre-MGR.'],
            ['period' => '2025-05-01', 'paid_at' => '2025-04-29', 'shares' => 3000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (shares 3,000 + welfare 500). Pre-MGR.'],
            ['period' => '2025-06-01', 'paid_at' => '2025-06-01', 'shares' => 1000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele partial KES 1,500 (shares 1,000 + welfare 500). Pre-MGR.'],

            // MGR begins Jul 2025
            ['period' => '2025-07-01', 'paid_at' => '2025-06-29', 'shares' => 1000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele partial KES 1,500 (shares 1,000 + welfare 500). MGR start period but partial — no MGR contribution this month.'],
            ['period' => '2025-08-01', 'paid_at' => '2025-07-29', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (full standard contribution).'],
            ['period' => '2025-09-01', 'paid_at' => '2025-08-28', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (+500 catch-up shares).'],
            ['period' => '2025-10-01', 'paid_at' => '2025-09-27', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (+500 catch-up shares).'],
            ['period' => '2025-11-01', 'paid_at' => '2025-11-06', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (+500 catch-up shares).'],
            ['period' => '2025-12-01', 'paid_at' => '2025-12-03', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (+500 catch-up shares).'],
            ['period' => '2026-01-01', 'paid_at' => '2025-12-23', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (+500 catch-up shares). Paid in advance for January.'],
            ['period' => '2026-02-01', 'paid_at' => '2026-01-31', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (full standard contribution).'],
            ['period' => '2026-03-01', 'paid_at' => '2026-03-09', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (+500 catch-up shares).'],
            ['period' => '2026-04-01', 'paid_at' => '2026-04-05', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (+500 catch-up shares).'],
            ['period' => '2026-05-01', 'paid_at' => '2026-04-29', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (full standard contribution — paid in advance for May).'],
        ]);

        // Shares Buy-in (Jan 2026) — lump-sum payment to bring shares to parity with founding members.
        $this->seedRows($member->id, [
            [
                'period'  => '2026-01-18',
                'paid_at' => '2026-01-18',
                'shares'  => 32700,
                'welfare' => 0,
                'mgr'     => 0,
                'method'  => 'zimele',
                'type'    => 'buy_in',
                'notes'   => 'Shares buy-in lump sum (KES 32,700) to bring shares to parity with founding members.',
            ],
        ]);
    }
}
