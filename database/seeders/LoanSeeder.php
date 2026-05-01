<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Loan;
use App\Models\LoanRepayment;

class LoanSeeder extends Seeder
{
    /**
     * Seed real loan data for Local Investors members.
     *
     * All loans disbursed on 2026-01-17 with 10% interest.
     * All loans due at end of April 2026 (2026-04-30).
     */
    public function run(): void
    {
        // Helper: find member by partial name match
        $findMember = function (string $key): Member {
            $member = Member::where('first_name', 'LIKE', "%{$key}%")
                ->orWhere('last_name', 'LIKE', "%{$key}%")
                ->first();

            if (!$member) {
                throw new \RuntimeException("Member not found: {$key}");
            }
            return $member;
        };

        $disbursedAt = '2026-01-17';
        $dueAt = '2026-04-30';

        // ── Actual loan records ──────────────────────────────────
        $loans = [
            // Scholastica Muswii — 30K loan, partial repayment
            [
                'member'   => 'Scolastica',
                'amount'   => 30000,
                'interest' => 10,
                'repaid'   => false,
                'repayments' => [
                    ['amount' => 10000, 'paid_at' => '2026-04-28', 'method' => 'mpesa', 'notes' => 'Loan repayment via M-Pesa'],
                ],
            ],
            // Scholastica Muswii — 20K loan, fully repaid
            [
                'member'   => 'Scolastica',
                'amount'   => 20000,
                'interest' => 10,
                'repaid'   => true,
                'repayments' => [
                    ['amount' => 12000, 'paid_at' => '2026-02-15', 'method' => 'mpesa', 'notes' => 'Partial repayment'],
                    ['amount' => 10000, 'paid_at' => '2026-03-20', 'method' => 'mpesa', 'notes' => 'Final repayment (principal + interest)'],
                ],
            ],
            // Stella Mutheu — 10K loan, disbursed Mar 2026 from Zimele withdrawal,
            // fully repaid in April 2026 (cleared — excluded from active loans list)
            // Disbursed 2026-03-22 (KES 30,000 withdrawn from Zimele on chama day).
            // [
            //     'member'   => 'Stella',
            //     'amount'   => 10000,
            //     'interest' => 10,
            //     'disbursed_at' => '2026-03-22',
            //     'due_at'       => '2026-06-30',
            //     'repaid'   => true,
            //     'repayments' => [
            //         ['amount' => 10500, 'paid_at' => '2026-04-28', 'method' => 'mpesa', 'notes' => 'Loan repayment via M-Pesa'],
            //         ['amount' => 500,   'paid_at' => '2026-04-29', 'method' => 'mpesa', 'notes' => 'Final repayment – clears principal + interest'],
            //     ],
            // ],
            // Joseph Sifuna — 31,500 loan, partial repayment
            [
                'member'   => 'Sifuna',
                'amount'   => 31500,
                'interest' => 10,
                'repaid'   => false,
                'repayments' => [
                    ['amount' => 21400, 'paid_at' => '2026-04-29', 'method' => 'mpesa', 'notes' => 'Loan repayment via M-Pesa Airtel Interops (Lipa Na M-PESA)'],
                ],
            ],
            // Joseph Sifuna — 35K loan, fully repaid
            [
                'member'   => 'Sifuna',
                'amount'   => 35000,
                'interest' => 10,
                'repaid'   => true,
                'repayments' => [
                    ['amount' => 20000, 'paid_at' => '2026-02-10', 'method' => 'mpesa', 'notes' => 'Partial repayment'],
                    ['amount' => 18500, 'paid_at' => '2026-03-25', 'method' => 'mpesa', 'notes' => 'Final repayment (principal + interest)'],
                ],
            ],
            // Naomi Mutuamwari — 30K loan, partial repayment
            [
                'member'   => 'Naomi',
                'amount'   => 30000,
                'interest' => 10,
                'repaid'   => false,
                'repayments' => [
                    ['amount' => 9500, 'paid_at' => '2026-04-29', 'method' => 'mpesa', 'notes' => 'Loan repayment via M-Pesa (April)'],
                ],
            ],
            // Michael Wangudi — 30K loan, principal cleared in April; interest (KES 3,000) outstanding
            [
                'member'   => 'Michael',
                'amount'   => 30000,
                'interest' => 10,
                'repaid'   => false,
                'repayments' => [
                    ['amount' => 15000, 'paid_at' => '2026-04-28', 'method' => 'mpesa',          'notes' => 'Loan deposit via M-Pesa (forwarded together with KES 15,890 collections from April contributions)'],
                    ['amount' => 15000, 'paid_at' => '2026-04-28', 'method' => 'merry_go_round', 'notes' => 'Loan repayment from April merry-go-round payout (clears principal; interest balance KES 3,000)'],
                ],
            ],
        ];

        foreach ($loans as $loanData) {
            $member = $findMember($loanData['member']);

            $loan = Loan::create([
                'member_id'        => $member->id,
                'amount'           => $loanData['amount'],
                'interest_percent' => $loanData['interest'],
                'term_months'      => 3,
                'disbursed_at'     => $disbursedAt,
                'due_at'           => $dueAt,
                'repaid'           => $loanData['repaid'],
                'repaid_amount'    => 0,
                'status'           => $loanData['repaid'] ? Loan::STATUS_REPAID : Loan::STATUS_DISBURSED,
            ]);

            // Create repayment records
            foreach ($loanData['repayments'] as $repayment) {
                LoanRepayment::create([
                    'loan_id' => $loan->id,
                    'amount'  => $repayment['amount'],
                    'paid_at' => $repayment['paid_at'],
                    'method'  => $repayment['method'],
                    'notes'   => $repayment['notes'],
                ]);
            }
        }
    }
}
