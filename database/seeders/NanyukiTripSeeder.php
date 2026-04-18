<?php

namespace Database\Seeders;

use App\Models\CashReturn;
use App\Models\Expenditure;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;

class NanyukiTripSeeder extends Seeder
{
    /**
     * Nanyuki Trip — November 2025.
     *
     * Income:
     *   1. First withdrawal from Zimele – 95,000
     *   2. Second withdrawal from Zimele – 6,000
     *   Total Income: 101,000/=
     *
     * Expenditure (Total: 86,501/=):
     *   1. Kingori's car fuel – 8,500
     *   2. Abby's fuel from Nairobi – 8,000
     *   3. Beef – 3,600
     *   4. Beef transport to Mike’s place – 200
     *   5. Foodstuff (Powerstar Supermarket) – 4,285
     *   6. Drinks (Wine, Whisky & Gin) – 6,560
     *   7. Vegetables – 800
     *   8. Potatoes & cabbages – 260
     *   9. Chicken (3 pieces) – 1,800
     *   10. Drinking water – 600
     *   11. Pineapple (3) & serviettes (2) – 500
     *   12. Ice cubes – 400
     *   13. Sufuria & White Cap cans – 2,615
     *   14. Accommodation (2 nights) – 16,000
     *   15. Ngare Ndare visit – 10,000
     *   16. Dinner – 8,450
     *   17. Birthday cake – 1,780
     *   18. Fuel (Abby return trip) – 5,000
     *   19. Fuel (Ngatia return trip) – 2,000
     *   20. Lunch (return trip) – 3,200
     *   21. Drinks (return trip) – 1,400
     *   22. Transaction cost – 551
     *
     * Balance: 14,499/=
     */
    public function run(): void
    {
        $tripDate = '2025-11-01'; // Default date for the trip month

        // ── Create Project ───────────────────────────────────────
        $project = \App\Models\Project::firstOrCreate(
            ['name' => 'Nanyuki Trip'],
            [
                'description' => 'Local Investors – Nanyuki Trip. November 2025.',
                'status' => 'completed',
                'progress' => 100,
                'start_date' => '2025-11-01',
                'budget' => 101000,
            ]
        );

        // ── Zimele Withdrawals ───────────────────────────────────
        $withdrawals = [
            ['description' => 'First withdrawal from Zimele – Nanyuki Trip', 'amount' => 95000],
            ['description' => 'Second withdrawal from Zimele – Nanyuki Trip', 'amount' => 6000],
        ];

        foreach ($withdrawals as $w) {
            Withdrawal::updateOrCreate(
                ['description' => $w['description'], 'withdrawn_at' => $tripDate],
                [
                    'amount'    => $w['amount'],
                    'member_id' => null,
                    'project_id' => $project->id,
                ]
            );
        }

        // ── Trip Expenditures ─────────────────────────────────────
        $expenditures = [
            ['description' => "Kingori's car fuel – Nanyuki Trip", 'amount' => 8500, 'category' => 'Transport', 'project_id' => $project->id],
            ['description' => "Abby's fuel from Nairobi – Nanyuki Trip", 'amount' => 8000, 'category' => 'Transport', 'project_id' => $project->id],
            ['description' => 'Beef – Nanyuki Trip', 'amount' => 3600, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Beef transport to Mike’s place – Nanyuki Trip', 'amount' => 200, 'category' => 'Transport', 'project_id' => $project->id],
            ['description' => 'Foodstuff (Powerstar Supermarket) – Nanyuki Trip', 'amount' => 4285, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Drinks (Wine, Whisky & Gin) – Nanyuki Trip', 'amount' => 6560, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Vegetables – Nanyuki Trip', 'amount' => 800, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Potatoes & cabbages – Nanyuki Trip', 'amount' => 260, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Chicken (3 pieces) – Nanyuki Trip', 'amount' => 1800, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Drinking water – Nanyuki Trip', 'amount' => 600, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Pineapple (3) & serviettes (2) – Nanyuki Trip', 'amount' => 500, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Ice cubes – Nanyuki Trip', 'amount' => 400, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Sufuria & White Cap cans – Nanyuki Trip', 'amount' => 2615, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Accommodation (2 nights) – Nanyuki Trip', 'amount' => 16000, 'category' => 'Other', 'project_id' => $project->id],
            ['description' => 'Ngare Ndare visit – Nanyuki Trip', 'amount' => 10000, 'category' => 'Other', 'project_id' => $project->id],
            ['description' => 'Dinner – Nanyuki Trip', 'amount' => 8450, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Birthday cake – Nanyuki Trip', 'amount' => 1780, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Fuel (Abby return trip) – Nanyuki Trip', 'amount' => 5000, 'category' => 'Transport', 'project_id' => $project->id],
            ['description' => 'Fuel (Ngatia return trip) – Nanyuki Trip', 'amount' => 2000, 'category' => 'Transport', 'project_id' => $project->id],
            ['description' => 'Lunch (return trip) – Nanyuki Trip', 'amount' => 3200, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Drinks (return trip) – Nanyuki Trip', 'amount' => 1400, 'category' => 'Food', 'project_id' => $project->id],
            ['description' => 'Transaction cost – Nanyuki Trip', 'amount' => 551, 'category' => 'Other', 'project_id' => $project->id],
        ];

        foreach ($expenditures as $e) {
            Expenditure::updateOrCreate(
                ['description' => $e['description']],
                [
                    'amount'   => $e['amount'],
                    'spent_at' => '2025-11-15', // Middle of the month for expenditures
                    'category' => $e['category'],
                    'project_id' => $e['project_id'],
                ]
            );
        }

        // ── Surplus cash return ──────────────────────────────────
        CashReturn::firstOrCreate(
            ['description' => 'Balance from Nanyuki Trip – returned by treasurer'],
            [
                'amount'      => 14499,
                'returned_at' => '2025-11-30',
                'project_id' => $project->id,
            ]
        );
    }
}
