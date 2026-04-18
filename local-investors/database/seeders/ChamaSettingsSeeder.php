<?php

namespace Database\Seeders;

use App\Models\ChamaSetting;
use Illuminate\Database\Seeder;

class ChamaSettingsSeeder extends Seeder
{
    /**
     * Seed your SACCO's actual configuration.
     *
     * Contribution structure (monthly):
     *   Shares:          KES 1,000
     *   Welfare:         KES   500
     *   Table banking:   KES 1,000  (merry_go_round field — 2 members receive per month)
     *   Total:           KES 2,500
     *
     * Fines:
     *   Late attendance: KES 100
     *   Full absence:    KES 200
     *
     * Loans:
     *   Interest: 10% flat
     *   Default term: 2 months
     *   Overdue penalty: 30%
     *   Min approvals before disburse: 3
     */
    public function run(): void
    {
        ChamaSetting::updateOrCreate(
            ['id' => 1],
            [
                'standard_interest_percent' => 10,
                'overdue_penalty_percent'   => 30,
                'loan_duration_months'      => 2,
                'grace_period_days'         => 0,
                'min_loan_approvals'        => 3,
            ]
        );
    }
}
