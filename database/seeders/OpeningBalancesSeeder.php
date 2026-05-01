<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Database\Seeder;

/**
 * Opening balances rolled up through 2025-12-31.
 *
 * Methodology:
 *   1. Shares as of Oct 2024  : taken from the chama diary "Total Shares 2024" snip.
 *   2. Nov 2024 → Dec 2025    : estimated standard contribution = 14 months × 3,500
 *                               (1,000 shares + 500 welfare + 2,000 MGR / month).
 *
 * Result: a single contribution row per member dated 2025-12-31 (type='opening_balance').
 * 2026 contributions remain in their own monthly rows (Feb–May 2026 in
 * MembersAndContributionsSeeder) which are kept as-is.
 *
 * Excluded — handled by their own per-member seeders:
 *   - Catherine Masinde  (CatherineMasindeSeeder — already done)
 *   - Violet Kamadi, Mike C, Symon Peter Ngatia (joined Jan 2025 — to be added)
 *
 * Tracy Muendi & Susan Ngina Muswii are included here using their snip totals as
 * starting points. Their dedicated seeders (when added) can override.
 */
class OpeningBalancesSeeder extends Seeder
{
    public function run(): void
    {
        $monthsToDec25   = 14;     // Nov 2024 → Dec 2025
        $sharesPerMonth  = 1000;
        $welfarePerMonth = 500;
        $mgrPerMonth     = 2000;

        // Oct 2024 shares totals from the chama diary snip
        $oct2024Shares = [
            'Naomi'      => 51000,
            'Michael'    => 51000,
            'Charles'    => 51000, // Kingori
            'Sifuna'     => 51000,
            'Kavinya'    => 51000,
            'Abigail'    => 51000,
            'Stella'     => 51000,
            'Scolastica' => 51000,
            'Torry'      => 51000,
            'Tracy'      => 17000, // joined Jan 2024 (incl. Sep 2024 lump 8,000)
            'Susan'      => 15000, // Susan Ngina Muswii — joined Mar 2024
        ];

        $sharesEstimate  = $sharesPerMonth  * $monthsToDec25; // 14,000
        $welfareEstimate = $welfarePerMonth * $monthsToDec25; //  7,000
        $mgrEstimate     = $mgrPerMonth     * $monthsToDec25; // 28,000

        foreach ($oct2024Shares as $key => $oct2024) {
            $member = $this->resolveMember($key);

            $totalShares = $oct2024 + $sharesEstimate;

            Contribution::updateOrCreate(
                [
                    'member_id'           => $member->id,
                    'contribution_period' => '2025-12-31',
                    'type'                => 'opening_balance',
                ],
                [
                    'shares'         => $totalShares,
                    'welfare'        => $welfareEstimate,
                    'merry_go_round' => $mgrEstimate,
                    'paid_at'        => '2025-12-31',
                    'penalty'        => 0,
                    'penalty_type'   => null,
                    'payment_method' => 'cash',
                    'notes'          => 'Opening balance through 2025-12-31. '
                        . 'Shares = Oct 2024 snip (' . number_format($oct2024) . ') + 14 months @ '
                        . number_format($sharesPerMonth) . ' (Nov 2024 → Dec 2025). '
                        . 'Welfare = ' . number_format($welfareEstimate) . ' (14 × ' . number_format($welfarePerMonth) . ' estimate). '
                        . 'MGR = ' . number_format($mgrEstimate) . ' (14 × ' . number_format($mgrPerMonth) . ' estimate). '
                        . '2026 contributions tracked separately.',
                ]
            );
        }
    }

    private function resolveMember(string $key): Member
    {
        $member = Member::where('first_name', 'LIKE', "%{$key}%")
            ->orWhere('last_name', 'LIKE', "%{$key}%")
            ->first();

        if (! $member) {
            throw new \RuntimeException("Member not found for key: {$key}");
        }

        return $member;
    }
}
