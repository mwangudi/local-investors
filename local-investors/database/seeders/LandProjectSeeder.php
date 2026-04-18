<?php

namespace Database\Seeders;

use App\Models\CashReturn;
use App\Models\Expenditure;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;

class LandProjectSeeder extends Seeder
{
    /**
     * Land purchase project — January 2026.
     *
     * Withdrew KES 420,000 from Zimele Money Trust.
     *
     * Expenditures (total KES 415,000):
     *   1. Land deposit:  KES 150,000 to John Gichinga Njoroge (seller) — 29/1/26
     *   2. Land deposit:  KES 150,000 to K Unity Sacco Ltd (A/C 00510000027576) — 29/1/26
     *   3. Land deposit:  KES 100,000 to K Unity Sacco Ltd (A/C 00510000027576) — 30/1/26
     *   4. Lawyer fees:   KES  10,000 to Ndwiga Law Advocates — 29/1/26
     *   5. Land search:   KES   5,000
     *
     * Transaction costs: KES 426.50 (57 + 80.50 + 0 + 24 + 265)
     *
     * Surplus returned:  KES 420,000 - 415,000 - 426.50 ≈ KES 4,573.50
     *
     * Merry-go-round payout (Feb): KES 5,000 to Charles Kingori — 29/1/26
     */
    public function run(): void
    {
        // ── Zimele Withdrawal ────────────────────────────────────
        Withdrawal::firstOrCreate(
            ['description' => 'Zimele withdrawal — Land project', 'withdrawn_at' => '2026-01-29'],
            [
                'amount'    => 420000,
                'member_id' => null,
            ]
        );

        // ── Land Expenditures ────────────────────────────────────
        $expenditures = [
            [
                'description' => 'Land deposit — John Gichinga Njoroge (seller). Ref: b25ee881',
                'amount'      => 150000,
                'spent_at'    => '2026-01-29',
                'category'    => 'Land purchase',
            ],
            [
                'description' => 'Land deposit — K Unity Sacco Ltd A/C 00510000027576. Ref: UATR44W9RH',
                'amount'      => 150000,
                'spent_at'    => '2026-01-29',
                'category'    => 'Land purchase',
            ],
            [
                'description' => 'Land deposit — K Unity Sacco Ltd A/C 00510000027576. Ref: FT26030GFBYT / UAUT76HGVD',
                'amount'      => 100000,
                'spent_at'    => '2026-01-30',
                'category'    => 'Land purchase',
            ],
            [
                'description' => 'Lawyer fees — Ndwiga Law Advocates. Ref: UATR44W3NK',
                'amount'      => 10000,
                'spent_at'    => '2026-01-29',
                'category'    => 'Legal fees',
            ],
            [
                'description' => 'Land search fee',
                'amount'      => 5000,
                'spent_at'    => '2026-01-29',
                'category'    => 'Legal fees',
            ],
            [
                'description' => 'Transaction costs — land project payments',
                'amount'      => 426.50,
                'spent_at'    => '2026-01-30',
                'category'    => 'Bank charges',
            ],
        ];

        foreach ($expenditures as $e) {
            Expenditure::firstOrCreate(
                ['description' => $e['description']],
                $e
            );
        }

        // ── Surplus cash return ──────────────────────────────────
        $surplus = 420000 - 415000 - 426.50;
        CashReturn::firstOrCreate(
            ['description' => 'Surplus from land project — returned by treasurer'],
            [
                'amount'      => round($surplus, 2),
                'returned_at' => '2026-01-31',
            ]
        );

        // ── Merry-go-round payout (Feb) ──────────────────────────
        Expenditure::firstOrCreate(
            ['description' => 'Merry-go-round payout — Charles Kingori. Ref: UATR44VDCH'],
            [
                'amount'   => 5000,
                'spent_at' => '2026-01-29',
                'category' => 'Merry-go-round',
            ]
        );
    }
}
