<?php

namespace Database\Seeders\Members;

use App\Models\Income;
use Database\Seeders\Members\Concerns\SeedsContributions;
use Illuminate\Database\Seeder;

/**
 * Catherine Masinde — full contribution history (Feb 2025 → Jul 2026).
 *
 * Source: her Zimele statement (19 payments, KES 104,700 total) — she always
 * routes her monthly contributions through Zimele.
 *
 * Reconciliation — shares to date = KES 77,400:
 *   - Joining fee KES 3,000 (one-time) recorded as income, NOT shares.
 *   - Aug–Oct 2025: NO MGR — Nanyuki months, 2,000 base (1,000 shares + 1,000
 *     welfare; the welfare funded the Nanyuki trip). Amounts above 2,000 = shares.
 *   - Nov/Dec 2025: no group collection; her KES 4,000 each = voluntary share
 *     top-ups (all shares, no welfare/MGR).
 *   - Other months standard 3,500 = MGR 2,000 + shares 1,000 + welfare 500;
 *     "other" 4,000 = standard + 500 shares top-up (shares 1,500).
 *   - Jan 2026 buy-in KES 32,700 = all shares.
 *   - Absent fines 200 each for Mar–Jun 2026 (she missed those meetings).
 *
 * Totals: shares 77,400 + welfare 9,500 + MGR 14,000 + fines 800 + joining fee 3,000
 *         = KES 104,700.
 */
class CatherineMasindeSeeder extends Seeder
{
    use SeedsContributions;

    public function run(): void
    {
        $member = $this->resolveMember('Catherine');

        $this->seedRows($member->id, [
            // Pre-MGR period — shares + welfare only
            ['period' => '2025-02-01', 'paid_at' => '2025-01-30', 'shares' => 1500, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 5,000. Joining month: 3,000 joining fee (recorded as income below) + 1,500 shares + 500 welfare. Pre-MGR.'],
            ['period' => '2025-03-01', 'paid_at' => '2025-03-03', 'shares' => 4500, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 5,000 (4,500 shares + 500 welfare). Pre-MGR.'],
            ['period' => '2025-04-01', 'paid_at' => '2025-03-28', 'shares' => 3000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (3,000 shares + 500 welfare). Pre-MGR.'],
            ['period' => '2025-05-01', 'paid_at' => '2025-04-29', 'shares' => 3000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (3,000 shares + 500 welfare). Pre-MGR.'],
            ['period' => '2025-06-01', 'paid_at' => '2025-06-01', 'shares' => 1000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele partial KES 1,500 (1,000 shares + 500 welfare). Pre-MGR.'],
            ['period' => '2025-07-01', 'paid_at' => '2025-06-29', 'shares' => 1000, 'welfare' => 500, 'mgr' => 0,    'method' => 'zimele', 'notes' => 'Zimele partial KES 1,500 (1,000 shares + 500 welfare). Pre-MGR.'],

            // MGR begins Aug 2025
            // Aug–Dec 2025: NO MGR — monthly was 2,000 (1,000 shares + 1,000 welfare;
            // welfare funded the Nanyuki trip). Amounts above 2,000 = shares top-up.
            ['period' => '2025-08-01', 'paid_at' => '2025-07-29', 'shares' => 2500, 'welfare' => 1000, 'mgr' => 0, 'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (no MGR): 1,000 welfare (Nanyuki trip) + 2,500 shares.'],
            ['period' => '2025-09-01', 'paid_at' => '2025-08-28', 'shares' => 3000, 'welfare' => 1000, 'mgr' => 0, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (no MGR): 1,000 welfare (Nanyuki trip) + 3,000 shares.'],
            ['period' => '2025-10-01', 'paid_at' => '2025-09-27', 'shares' => 3000, 'welfare' => 1000, 'mgr' => 0, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (no MGR): 1,000 welfare (Nanyuki trip) + 3,000 shares.'],
            ['period' => '2025-11-01', 'paid_at' => '2025-11-06', 'shares' => 4000, 'welfare' => 0, 'mgr' => 0, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 — Nov 2025: no group collection this month; recorded as a voluntary share top-up (all shares).'],
            ['period' => '2025-12-01', 'paid_at' => '2025-12-03', 'shares' => 4000, 'welfare' => 0, 'mgr' => 0, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 — Dec 2025: no group collection this month; recorded as a voluntary share top-up (all shares).'],
            ['period' => '2026-01-01', 'paid_at' => '2025-12-23', 'shares' => 1500, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 4,000 (standard + 500 shares top-up). Paid in advance for January.'],
            ['period' => '2026-02-01', 'paid_at' => '2026-01-31', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (full standard).'],
            ['period' => '2026-03-01', 'paid_at' => '2026-03-09', 'shares' => 1300, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'notes' => 'Zimele KES 4,000, less 200 absenteeism fine (absent). Shares 1,300.'],
            ['period' => '2026-04-01', 'paid_at' => '2026-04-05', 'shares' => 1300, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'notes' => 'Zimele KES 4,000, less 200 absenteeism fine (absent). Shares 1,300.'],
            ['period' => '2026-05-01', 'paid_at' => '2026-04-29', 'shares' => 800,  'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'notes' => 'Zimele KES 3,500, less 200 absenteeism fine (absent). Shares 800.'],
            ['period' => '2026-06-01', 'paid_at' => '2026-06-05', 'shares' => 7300, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism', 'notes' => 'Zimele KES 10,000 = 3,500 monthly + 6,300 shares top-up + 200 absenteeism fine.'],
            ['period' => '2026-07-01', 'paid_at' => '2026-07-01', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele', 'notes' => 'Zimele KES 3,500 (full standard).'],
        ]);

        // Shares buy-in (Jan 2026) — lump sum to bring shares to parity with founding members.
        $this->seedRows($member->id, [
            ['period' => '2026-01-18', 'paid_at' => '2026-01-18', 'shares' => 32700, 'welfare' => 0, 'mgr' => 0, 'method' => 'zimele', 'type' => 'buy_in', 'notes' => 'Shares buy-in lump sum (KES 32,700) to bring shares to parity with founding members.'],
        ]);

        // Joining fee (one-time) — recorded as income, not counted in shares.
        Income::firstOrCreate(
            ['category' => 'joining_fee', 'member_id' => $member->id],
            ['amount' => 3000, 'received_at' => '2025-01-30', 'description' => 'Joining fee — Catherine Masinde (paid with her first Zimele contribution).']
        );
    }
}
