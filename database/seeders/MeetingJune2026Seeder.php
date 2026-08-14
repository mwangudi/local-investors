<?php

namespace Database\Seeders;

use App\Models\Expenditure;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Project;
use App\Models\Withdrawal;
use Database\Seeders\Members\Concerns\SeedsContributions;
use Illuminate\Database\Seeder;

/**
 * Contributions (and related fines / loans / MGR payouts) confirmed at the
 * Sunday 14 June 2026 chama meeting.
 *
 * Source: treasurer M-Pesa collection list + handwritten meeting notes.
 *
 * Conventions (see MembersAndContributionsSeeder):
 *   Standard monthly = KES 3,500  →  shares 1,000 + welfare 500 + MGR 2,000.
 *   Amounts above 3,500 are booked to shares (catch-up).
 *   Fines: lateness = 100, absenteeism = 200 (stored on the contribution row).
 *
 * Run standalone (NOT part of DatabaseSeeder — MembersAndContributionsSeeder
 * force-deletes all contributions on a full reseed):
 *
 *     php artisan db:seed --class=MeetingJune2026Seeder
 *
 * Idempotent: re-running will not duplicate rows.
 *
 * ── RECONCILIATION NOTES (confirmed with treasurer) ──────────────────────
 *   • Charles (28 May), Abigail (24 May) and Michael (30 May) paid June EARLY via
 *     Zimele. Abigail's 7,000 and Tracy's 7,000 each cover June + July.
 *   • The 04 Jun KES 3,500 and 13 Jun KES 4,500 (treasurer's ...267 number) were
 *     both Mike C's: his June contribution and a 4,500 share top-up (he is behind
 *     on shares).
 *   • Catherine 10,000 = 3,500 monthly + 200 absenteeism fine + 6,300 extra shares.
 *   • Stella's extra 200 = absenteeism fine paid.
 *   • Scolastica 31 May KES 10,000 → LOAN REPAYMENT. Per the meeting, outstanding
 *     loan balances were re-issued as new loans (Naomi 13,500, Scolastica 13,000,
 *     Michael 3,000) and the old pre-June loans closed (not counted twice).
 *   • INTEREST 468.63 (31 May) is intentionally NOT recorded (per instruction).
 *   • Unpaid "pending" fines are noted on the contribution row but NOT added as a
 *     penalty (the app treats a penalty as paid). Pending: Kavinya, Tracy, Joseph,
 *     Violet, Susan Ngina. (Charles paid his 200 fine on 19 Apr — see fine income.)
 *   • Also records related May 2026 items: Land project +230,000 (Zimele EFT) and
 *     Susan Ngina's May MGR cash payout (3,800 on 24 May + 8,200 on 26 May).
 *   • Mike C's June contribution = the 04 Jun KES 3,500 (via the treasurer's
 *     number). He joined Feb 2025 and pays the standard 3,500/month, so his
 *     opening balance through Dec 2025 is estimated at 11 months standard
 *     (shares 11,000 + welfare 5,500 + MGR 22,000). Sifuna's loan = KES 13,250.
 */
class MeetingJune2026Seeder extends Seeder
{
    use SeedsContributions;

    public function run(): void
    {
        // ── 1. Contributions (incl. pending fines as penalty fields) ─────────
        // Keyed by partial member name (resolved via SeedsContributions::resolveMember).
        $contributions = [
            // ── Paid June EARLY via Zimele (late May) ──
            'Charles' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-05-28', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele, 28 May — paid early). KES 200 absenteeism fine paid 19 Apr (recorded as fine income below).'],
            ],

            'Abigail' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-05-24',          'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele, 24 May). Paid KES 7,000 covering June & July.'],
                ['period' => '2026-07-01', 'paid_at' => '2026-05-24 00:00:01', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'July contribution KES 3,500 (Zimele, 24 May — advance, same 7,000 transaction as June). paid_at offset 1s to satisfy the unique (member, paid_at) constraint.'],
            ],

            'Michael' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-05-30', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele, 30 May — paid early).'],
                // The 04 Jun KES 3,500 and 13 Jun KES 4,500 (treasurer's ...267 number) were both
                // Mike C's payments (June contribution + share top-up) — see "Mike" below.
            ],

            // Mike C (resolved by "Mike"). Joined Feb 2025; regular contributor at the
            // standard 3,500/month (shares 1,000 + welfare 500 + MGR 2,000), 3,700 when
            // absent (+200 fine). Opening balance (est.) through Dec 2025 + all 2026
            // months Jan–Jun (each KES 3,500 via the treasurer's ...267 number).
            'Mike' => [
                ['period' => '2025-12-31', 'paid_at' => '2025-12-31', 'shares' => 11000, 'welfare' => 5500, 'mgr' => 22000, 'method' => 'cash', 'type' => 'opening_balance',
                 'notes' => 'Opening balance through 31 Dec 2025 — joined Feb 2025, 11 months at standard 3,500 (shares 11,000 + welfare 5,500 + MGR 22,000). ESTIMATE, mirroring OpeningBalancesSeeder; replace with exact figures / absent months when available.'],
                ['period' => '2026-01-01', 'paid_at' => '2026-02-03', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'January contribution KES 3,500 (Zimele via the treasurer\'s ...267 number, paid 3 Feb).'],
                ['period' => '2026-02-01', 'paid_at' => '2026-02-13', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'February contribution KES 3,500 (Zimele via the treasurer\'s ...267 number, paid 13 Feb). NB: base seeder also has a Feb 2026 row for Mike C (zimele) — firstOrCreate keeps one; same amount.'],
                ['period' => '2026-03-01', 'paid_at' => '2026-03-21', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'March contribution KES 3,500 (Zimele via the treasurer\'s ...267 number, paid 21 Mar).'],
                ['period' => '2026-04-01', 'paid_at' => '2026-04-03', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'April contribution KES 3,500 (Zimele via the treasurer\'s ...267 number, paid 3 Apr).'],
                ['period' => '2026-05-01', 'paid_at' => '2026-05-02', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'May contribution KES 3,500 (Zimele via the treasurer\'s ...267 number, paid 2 May).'],
                ['period' => '2026-06-01', 'paid_at' => '2026-06-04', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele via the treasurer\'s ...267 number, 4 Jun).'],
                ['period' => '2026-06-13', 'paid_at' => '2026-06-13', 'shares' => 4500, 'welfare' => 0,   'mgr' => 0,    'method' => 'zimele', 'type' => 'special',
                 'notes' => 'Share top-up KES 4,500 (Zimele via treasurer ...267, 13 Jun) — catching up shares (behind); separate from his 3,500 June contribution.'],
            ],

            // ── Paid at / around the June meeting ──
            'Kavinya' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-04', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele). Pending KES 200 absenteeism fine (absent in May) — unpaid (not added as penalty).'],
            ],

            'Torry' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-04', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele).'],
            ],

            'Tracy' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-05',          'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele). Paid KES 7,000 = June + July (two months in advance). Pending KES 400 absenteeism fine — unpaid (not added as penalty).'],
                ['period' => '2026-07-01', 'paid_at' => '2026-06-05 00:00:01', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'July contribution KES 3,500 (Zimele, 5 Jun — advance, same 7,000 transaction as June). paid_at offset 1s to satisfy the unique (member, paid_at) constraint.'],
            ],

            'Joseph' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-05', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele). Pending KES 100 lateness fine — unpaid (not added as penalty).'],
            ],

            'Stella' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-05', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'penalty' => 200, 'penalty_type' => 'absenteeism',
                 'notes' => 'June contribution KES 3,500 (Zimele) + KES 200 absenteeism fine paid (extra payment, 14 Jun). Total KES 3,700.'],
            ],

            'Catherine' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-05', 'shares' => 7300, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'penalty' => 200, 'penalty_type' => 'absenteeism',
                 'notes' => 'KES 10,000 (Zimele) = 3,500 monthly (shares 1,000 + welfare 500 + MGR 2,000) + 200 absenteeism fine + 6,300 extra shares.'],
            ],

            // Susan Ngina Muswii (resolved by "Ngina").
            'Ngina' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-05', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele). Pending KES 200 fine — unpaid (not added as penalty).'],
            ],

            'Symon' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-05', 'shares' => 1000,  'welfare' => 500,  'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele).'],
                ['period' => '2026-06-14', 'paid_at' => '2026-06-14', 'shares' => 14000, 'welfare' => 1000, 'mgr' => 0,    'method' => 'merry_go_round', 'type' => 'special',
                 'notes' => 'June MGR payout KES 15,000 applied (not cash): covers unpaid Oct 2025 (1,000 shares + 1,000 welfare, Nanyuki) + 13,000 shares top-up.'],
            ],

            'Violet' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-05', 'shares' => 1000,  'welfare' => 500,  'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele; typed-list amount used — notes showed 2,500). Pending KES 200 absenteeism fine — unpaid (not added as penalty).'],
                ['period' => '2026-06-14', 'paid_at' => '2026-06-14', 'shares' => 14000, 'welfare' => 1000, 'mgr' => 0,    'method' => 'merry_go_round', 'type' => 'special',
                 'notes' => 'June MGR payout KES 15,000 applied (not cash): covers unpaid Oct 2025 (1,000 shares + 1,000 welfare, Nanyuki) + 13,000 shares top-up.'],
            ],

            'Scolastica' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-14', 'shares' => 1000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'June contribution KES 3,500 (Zimele). Her 31 May KES 10,000 is recorded as a loan repayment (see below); a new KES 13,000 loan was disbursed.'],
            ],

            // KES 15,500 (14 Jun) = June 3,500 + May balance 2,000 (folded here as
            // catch-up shares) + 10,000 loan repayment (recorded separately).
            'Naomi' => [
                ['period' => '2026-06-01', 'paid_at' => '2026-06-14', 'shares' => 3000, 'welfare' => 500, 'mgr' => 2000, 'method' => 'zimele',
                 'notes' => 'KES 15,500 (Zimele) on 14 Jun = June 3,500 + May balance 2,000 (folded here as +2,000 shares) + 10,000 loan repayment (recorded separately). Contribution portion = KES 5,500.'],
            ],
        ];

        foreach ($contributions as $key => $rows) {
            $member = $this->resolveMember($key);
            $this->seedRows($member->id, $rows);
        }

        // ── 2. Loan repayments toward existing (pre-June) active loans ────────
        $activeLoanBefore = static function (Member $m, string $date): ?Loan {
            return Loan::where('member_id', $m->id)
                ->where('repaid', false)
                ->whereNotNull('disbursed_at')
                ->whereDate('disbursed_at', '<', $date)
                ->orderBy('disbursed_at')
                ->first();
        };

        // Scolastica — 31 May KES 10,000.
        $scolastica = $this->resolveMember('Scolastica');
        if ($loan = $activeLoanBefore($scolastica, '2026-06-01')) {
            LoanRepayment::firstOrCreate(
                ['loan_id' => $loan->id, 'amount' => 10000, 'paid_at' => '2026-05-31'],
                ['method' => 'zimele', 'notes' => 'Loan repayment via Zimele (31 May 2026), per June meeting reconciliation.']
            );
        }

        // Naomi — KES 10,000 out of the 14 Jun KES 15,500.
        $naomi = $this->resolveMember('Naomi');
        if ($loan = $activeLoanBefore($naomi, '2026-06-01')) {
            LoanRepayment::firstOrCreate(
                ['loan_id' => $loan->id, 'amount' => 10000, 'paid_at' => '2026-06-14'],
                ['method' => 'zimele', 'notes' => 'Loan repayment from the KES 15,500 Zimele payment on 14 Jun 2026.']
            );
        }

        // ── 3. Loan balances re-issued as NEW loans at the meeting (14 Jun 2026) ──
        // Per the June meeting, each member's outstanding loan balance was given as a
        // new loan. Amounts = balances AFTER the June repayments in section 2:
        //   Naomi:      33,000 − 19,500 (9,500 Apr + 10,000 Jun) = 13,500
        //   Scolastica: 33,000 − 20,000 (10,000 Apr + 10,000 May) = 13,000
        //   Michael:    33,000 − 30,000                          =  3,000
        // The old pre-June loan is closed so the balance is not counted twice.
        // 2-month term from 14 Jun → due at the August meeting (2nd Sunday, 9 Aug 2026).
        $rollovers = [
            ['key' => 'Naomi',      'amount' => 13500],
            ['key' => 'Scolastica', 'amount' => 13000],
            ['key' => 'Michael',    'amount' => 3000],
        ];
        foreach ($rollovers as $r) {
            $member = $this->resolveMember($r['key']);

            // Close the old pre-June loan — its balance carries into the new loan.
            Loan::where('member_id', $member->id)
                ->where('repaid', false)
                ->whereDate('disbursed_at', '<', '2026-06-01')
                ->update(['repaid' => true, 'status' => Loan::STATUS_REPAID]);

            // whereDate, so a re-run finds the existing loan and corrects it
            // instead of issuing a duplicate.
            $existing = Loan::where('member_id', $member->id)
                ->whereDate('disbursed_at', '2026-06-14')
                ->first();

            $attributes = [
                'member_id'        => $member->id,
                'amount'           => $r['amount'],
                'interest_percent' => 10,
                'term_months'      => 2,
                'disbursed_at'     => '2026-06-14',
                'due_at'           => '2026-08-09',
                'repaid'           => false,
                'repaid_amount'    => 0,
                'status'           => Loan::STATUS_DISBURSED,
            ];

            $existing ? $existing->update($attributes) : Loan::create($attributes);
        }

        // ── 4. June merry-go-round ─────────────────────────────────────────
        // June MGR recipients were Ngatia (Symon) and Violet. Neither was paid in
        // cash — both payouts were applied to their pending shares (the 'special'
        // share rows above), so there is no cash withdrawal to record.

        // ── 5. Related May 2026 finance records (not June-meeting items) ──────
        // 5a. Land Purchase Project — additional KES 230,000 paid from Zimele
        //     (EFT by M. Wangudi, 5 May 2026). Recorded as both the Zimele
        //     withdrawal (source) and the land expenditure (use), per the
        //     existing LandProjectSeeder pattern.
        $landProjectId = Project::where('name', 'Land Purchase Project')->value('id');

        Withdrawal::firstOrCreate(
            ['description' => 'Zimele withdrawal (EFT, M. Wangudi) — Land project', 'withdrawn_at' => '2026-05-05'],
            ['amount' => 230000, 'member_id' => null, 'project_id' => $landProjectId]
        );
        Expenditure::firstOrCreate(
            ['description' => 'Land payment — additional KES 230,000 (EFT via Zimele, 5 May 2026)'],
            ['amount' => 230000, 'spent_at' => '2026-05-05', 'category' => 'Land purchase', 'project_id' => $landProjectId]
        );

        // 5b. Susan Ngina — May merry-go-round payout, paid out as cash in two parts
        //     (she shared the May MGR with Sifuna, who applied his to his loan).
        //     Sifuna's outstanding loan after that = KES 13,250 (already reflected in
        //     LoanSeeder: 31,500 @ 10% less 21,400 repaid), so no extra repayment here.
        $susan = $this->resolveMember('Ngina');
        Withdrawal::firstOrCreate(
            ['description' => 'May MGR payout — Susan Ngina (M-Pesa, Ref UEOR44Y3EH)', 'withdrawn_at' => '2026-05-24'],
            ['amount' => 3800, 'member_id' => $susan->id, 'project_id' => null]
        );
        Withdrawal::firstOrCreate(
            ['description' => 'May MGR payout — Susan Ngina (M-Pesa, Ref UEQ2S5G3ZI)', 'withdrawn_at' => '2026-05-26'],
            ['amount' => 8200, 'member_id' => $susan->id, 'project_id' => null]
        );

        // ── 6. Fines paid (standalone, not tied to a contribution row) ───────
        // Charles paid his KES 200 absenteeism fine by M-Pesa on 19 Apr 2026.
        $charles = $this->resolveMember('Charles');
        Income::firstOrCreate(
            ['category' => 'fine', 'member_id' => $charles->id, 'description' => 'Absenteeism fine — Charles Kingori (M-Pesa, Ref UDJK61A5YO)'],
            ['amount' => 200, 'received_at' => '2026-04-19', 'fine_type' => 'absenteeism']
        );

        $this->command?->info('MeetingJune2026Seeder: seeded June/July contributions, fines, loan repayments, new loans, the land payment, May MGR payouts and Charles\'s fine.');
    }
}
