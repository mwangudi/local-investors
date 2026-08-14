<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Database\Seeder;

class MembersAndContributionsSeeder extends Seeder
{
    /**
     * Seed all Local Investors members and contributions Aug 2025 → Mar 2026.
     *
     * Standard monthly contribution = KES 3,500:
     *   Shares:        1,000
     *   Welfare:         500
     *   Table banking:  2,000  (merry_go_round — 2 members receive payout each month)
     *
     * Amounts above 3,500 = catch-up shares / loan repayment (excess added to shares).
     * Amounts below 3,500 = partial payment (reduced merry_go_round first).
     *
     * Fines: late = KES 100, absent = KES 200.
     *
     * Meeting: every 2nd Sunday of the month (April 2026 = 3rd Sunday exception).
     */
    public function run(): void
    {
        // Wipe existing contributions (loans are NOT affected)
        Contribution::query()->forceDelete();

        // ── Members (15 total) ─────────────────────────────────
        $members = [
            ['first_name' => 'Michael',     'last_name' => 'Wangudi',      'phone' => null, 'email' => null],
            ['first_name' => 'Violet',      'last_name' => 'Kamadi',       'phone' => null, 'email' => null],
            ['first_name' => 'Joseph',      'last_name' => 'Sifuna',       'phone' => null, 'email' => null],
            ['first_name' => 'Tracy',       'last_name' => 'Muendi',       'phone' => null, 'email' => null],
            ['first_name' => 'Symon Peter', 'last_name' => 'Ngatia',       'phone' => null, 'email' => null],
            ['first_name' => 'Catherine',   'last_name' => 'Masinde',      'phone' => null, 'email' => null],
            ['first_name' => 'Torry',       'last_name' => 'Mabale',       'phone' => null, 'email' => null],
            ['first_name' => 'Mike',        'last_name' => 'C',            'phone' => null, 'email' => null],
            ['first_name' => 'Abigail',     'last_name' => 'Njoki',        'phone' => null, 'email' => null],
            ['first_name' => 'Charles',     'last_name' => 'Kingori',      'phone' => null, 'email' => null],
            ['first_name' => 'Stella',      'last_name' => 'Mutheu',       'phone' => null, 'email' => null],
            ['first_name' => 'Scolastica',  'last_name' => 'Muswii',       'phone' => null, 'email' => null],
            ['first_name' => 'Naomi',       'last_name' => 'Nyoroka',      'phone' => null, 'email' => null],
            ['first_name' => 'Kavinya',     'last_name' => 'Oduor',        'phone' => null, 'email' => null],
            ['first_name' => 'Susan Ngina', 'last_name' => 'Muswii',       'phone' => null, 'email' => null],
        ];

        // Members who joined later than Jan 2025
        $jan2024Members = ['Tracy Muendi'];
        $mar2024Members = ['Susan Ngina Muswii'];

        // Members (16 total → 15 after merging Ngina into Susan Ngina)

        $memberMap = [];
        foreach ($members as $m) {
            $fullName = $m['first_name'] . ' ' . $m['last_name'];

            if (in_array($fullName, $jan2024Members)) {
                $joinDate = '2024-01-01';
            } elseif (in_array($fullName, $mar2024Members)) {
                $joinDate = '2024-03-01';
            } else {
                $joinDate = '2025-01-01';
            }

            $member = Member::firstOrCreate(
                ['first_name' => $m['first_name'], 'last_name' => $m['last_name']],
                array_merge($m, [
                    'join_date'  => $joinDate,
                    'is_active'  => true,
                    'notification_preference' => 'both',
                ])
            );
            $memberMap[$fullName] = $member->id;
        }

        // Helper: resolve member ID by partial name match
        $id = function (string $key) use ($memberMap) {
            foreach ($memberMap as $name => $mid) {
                if (stripos($name, $key) !== false) {
                    return $mid;
                }
            }
            throw new \RuntimeException("Member not found: {$key}");
        };

        // ── Breakdown helper ─────────────────────────────────────
        // Base 3,500: shares=1000 (mandatory), welfare=500 (mandatory), merry=2000 (table-banked)
        // Extra above 3,500 → added to shares (catch-up)
        // Below 3,500 → shares + welfare are mandatory; reduce merry_go_round first (table-banked
        // is discretionary), then welfare, then shares only as a last resort.
        $breakdown = function (float $total): array {
            $shares = 1000;
            $welfare = 500;
            $merry = 2000;

            if ($total >= 3500) {
                $shares += ($total - 3500); // excess = catch-up shares
            } else {
                $deficit = 3500 - $total;
                // reduce merry first
                $merryReduce = min($deficit, $merry);
                $merry -= $merryReduce;
                $deficit -= $merryReduce;
                // then welfare
                $welfareReduce = min($deficit, $welfare);
                $welfare -= $welfareReduce;
                $deficit -= $welfareReduce;
                // then shares
                $shares -= $deficit;
            }
            return ['shares' => max(0, $shares), 'welfare' => max(0, $welfare), 'merry_go_round' => max(0, $merry)];
        };

        // ── Meeting dates (2nd Sunday of each month) ─────────────
        $meetingDates = [
            'jun' => '2025-06-08',
            'jul' => '2025-07-13',
            'aug' => '2025-08-10',
            'sep' => '2025-09-14',
            'oct' => '2025-10-12',
            'nov' => '2025-11-09',
            'dec' => '2025-12-14',
            'jan' => '2026-01-11',
            'feb' => '2026-02-08',
            'mar' => '2026-03-22', // 3rd-week exception (moved from March 8)
        ];

        // Contribution period = first day of the month
        $periods = [
            'jun' => '2025-06-01',
            'jul' => '2025-07-01',
            'aug' => '2025-08-01',
            'sep' => '2025-09-01',
            'oct' => '2025-10-01',
            'nov' => '2025-11-01',
            'dec' => '2025-12-01',
            'jan' => '2026-01-01',
            'feb' => '2026-02-01',
            'mar' => '2026-03-01',
        ];

        // ── Historical contributions: 2025 (Jun → Dec) and Jan 2026 ──
        // Replaced by OpeningBalancesSeeder which rolls everything from inception
        // through 2025-12-31 into a single per-member opening_balance row using:
        //   • Oct 2024 shares snip
        //   • + 14 estimated standard months (Nov 2024 → Dec 2025)
        // 2026 contributions (Feb–May) below remain authoritative per actual records.
        //
        // Note: the previous month-by-month estimates (some accurate, some guessed)
        // are intentionally removed here. Per-member seeders under
        // Database\Seeders\Members will overwrite the opening balance with reconciled
        // data when each member's M-Pesa/Zimele/diary record is loaded in.

        // ── Catherine Masinde — see Database\Seeders\Members\CatherineMasindeSeeder ──
        // Catherine's contributions are seeded in a dedicated per-member seeder
        // for full M-Pesa/Zimele reconciliation. Run via MemberContributionsSeeder.

        // ── February 2026 Contributions (Feb 8) ──────────────────
        // Source: WhatsApp report Feb 2026 (Michael Wangudi).
        //   MPESA total: 31,460  (Ngatia 3,500 + Schola 3,500 + Violet 3,530 + Ngina 3,530
        //                          + Aby 3,500 + Naomi 5,000 + Kavinya 3,700 + Michael 3,700 + Stella 4,000)
        //   Zimele total: 24,600 (Torry 7,400 + Tracy 3,700 + Mike C 3,500 + Catherine 3,500 + Charles 3,500)
        //   Grand total collected: 56,060
        //   Sifuna: pending (absent in Feb)
        //
        // Feb expenses (NOT contributions):
        //   • Charles Kingori 5,000 (MGR payout) — TODO: seed as Withdrawal/cash return
        //   Remaining MPESA balance to deposit into Zimele: 26,460
        $febDate = $meetingDates['feb'];
        $febContributions = [
            // MPESA
            ['member' => 'Symon Peter',   'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Scolastica',    'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Violet',        'total' => 3530, 'method' => 'mpesa'],
            ['member' => 'Susan Ngina',   'total' => 3530, 'method' => 'mpesa'],
            ['member' => 'Abigail',       'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Naomi',         'total' => 5000, 'method' => 'mpesa'],
            ['member' => 'Kavinya',       'total' => 3700, 'method' => 'mpesa'],
            ['member' => 'Michael',       'total' => 3700, 'method' => 'mpesa'],
            ['member' => 'Stella',        'total' => 4000, 'method' => 'mpesa'],
            // Zimele
            // Torry 7,400 = 3,500 standard + 3,900 loan repayment.
            ['member' => 'Torry',         'total' => 7400, 'method' => 'zimele', 'loan_repayment' => 3900],
            ['member' => 'Tracy',         'total' => 3700, 'method' => 'zimele'],
            ['member' => 'Mike C',        'total' => 3500, 'method' => 'zimele'],
            // Catherine: handled in consolidated block above.
            ['member' => 'Charles',       'total' => 3500, 'method' => 'zimele'],
        ];

        foreach ($febContributions as $c) {
            $actualTotal = $c['total'];
            $penalty = 0;
            $penaltyType = null;
            $notes = 'Payment via ' . strtoupper($c['method']);

            // Per-row override: explicit loan repayment — keep base 3,500 breakdown constant.
            if (!empty($c['loan_repayment'])) {
                $loanAmt = (int) $c['loan_repayment'];
                $notes .= '. Includes KES ' . number_format($loanAmt) . ' loan repayment.';
                $actualTotal -= $loanAmt;
            }

            $extra = $actualTotal - 3500;
            if ($extra == 100) {
                $penalty = 100;
                $penaltyType = 'lateness';
                $notes .= '. Includes KES 100 fine.';
                $actualTotal -= 100;
            } elseif ($extra == 200) {
                $penalty = 200;
                $penaltyType = 'absenteeism';
                $notes .= '. Includes KES 200 fine.';
                $actualTotal -= 200;
            } elseif ($extra > 0 && $extra <= 50) {
                // Small overage (≤ 50) treated as transaction fee — not added to shares.
                $notes .= '. Includes KES ' . number_format($extra) . ' transaction fee.';
                $actualTotal -= $extra;
            } elseif ($extra > 0) {
                $notes .= '. Extra KES ' . number_format($extra) . ' (catch-up / loan repayment).';
            }

            $b = $breakdown($actualTotal);

            Contribution::firstOrCreate([
                'member_id'           => $id($c['member']),
                'contribution_period' => $periods['feb'],
            ], array_merge($b, [
                'paid_at'        => $febDate,
                'penalty'        => $penalty,
                'penalty_type'   => $penaltyType,
                'type'           => 'monthly',
                'payment_method' => $c['method'],
                'notes'          => $notes,
            ]));
        }

        // ── March 2026 Contributions (March 22) ──────────────────
        // Source: WhatsApp summary 28/03/2026 10:35.
        //   MPESA total: 10,700 (Susan Ngina 3,500 + Violet 3,500 + Michael 3,700)
        //   Zimele total: 40,900 (Sifuna 7,200 + Tracy 3,500 + Ngatia 3,700 + Catherine 4,000
        //                          + Torry 8,500 + Mike C 3,500 + Aby 3,500 + Charles 3,500 + Stella 3,500)
        //   Grand total collected: 51,600
        //
        // March payouts (28/03/2026 10:53) — NOT contributions; recorded as cash returns/loan disbursement:
        //   • Schola 8,000   (MGR payout)        — TODO: seed as cash return
        //   • Naomi  11,500  (MGR payout)        — TODO: seed as cash return
        //   • Stella 10,000  (new loan disbursed) — see LoanSeeder (disbursed_at = 2026-03-22)
        //   Withdrawn from Zimele 30,000; transaction costs 280; balance retained 220.
        $marDate = $meetingDates['mar'];
        $marContributions = [
            // MPESA
            ['member' => 'Susan Ngina',   'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Violet',        'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Michael',       'total' => 3700, 'method' => 'mpesa'],
            // Zimele
            // Sifuna 7,200 = 3,500 base + 200 absenteeism fine + 3,500 catch-up shares (paid 22/03/2026).
            ['member' => 'Sifuna',        'total' => 7200, 'method' => 'zimele', 'penalty' => 200, 'penalty_type' => 'absenteeism'],
            ['member' => 'Tracy',         'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Symon Peter',   'total' => 3700, 'method' => 'zimele'],
            // Catherine: handled in consolidated block above.
            // Torry 8,500 = 3,500 standard + 5,000 loan repayment.
            ['member' => 'Torry',         'total' => 8500, 'method' => 'zimele', 'loan_repayment' => 5000],
            ['member' => 'Mike C',        'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Abigail',       'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Charles',       'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Stella',        'total' => 3500, 'method' => 'zimele'],
        ];

        foreach ($marContributions as $c) {
            $actualTotal = $c['total'];
            $penalty = 0;
            $penaltyType = null;
            $notes = 'Payment via ' . strtoupper($c['method']);

            // Per-row override: explicit loan repayment — keep base 3,500 breakdown constant.
            if (!empty($c['loan_repayment'])) {
                $loanAmt = (int) $c['loan_repayment'];
                $notes .= '. Includes KES ' . number_format($loanAmt) . ' loan repayment.';
                $actualTotal -= $loanAmt;
            }

            // Per-row override: explicit penalty (e.g. Sifuna 7,200 with 200 absenteeism + 3,500 catch-up).
            if (!empty($c['penalty'])) {
                $penalty = (int) $c['penalty'];
                $penaltyType = $c['penalty_type'] ?? 'absenteeism';
                $notes .= '. Includes KES ' . number_format($penalty) . ' ' . $penaltyType . ' fine.';
                $actualTotal -= $penalty;
                $extra = $actualTotal - 3500;
                if ($extra > 0) {
                    $notes .= ' Extra KES ' . number_format($extra) . ' (catch-up / loan repayment).';
                }
            } else {
                $extra = $actualTotal - 3500;
                if ($extra == 100) {
                    $penalty = 100;
                    $penaltyType = 'lateness';
                    $notes .= '. Includes KES 100 fine.';
                    $actualTotal -= 100;
                } elseif ($extra == 200) {
                    $penalty = 200;
                    $penaltyType = 'absenteeism';
                    $notes .= '. Includes KES 200 fine.';
                    $actualTotal -= 200;
                } elseif ($extra > 0 && $extra <= 50) {
                    // Small overage (≤ 50) treated as transaction fee — not added to shares.
                    $notes .= '. Includes KES ' . number_format($extra) . ' transaction fee.';
                    $actualTotal -= $extra;
                } elseif ($extra > 0) {
                    $notes .= '. Extra KES ' . number_format($extra) . ' (catch-up / loan repayment).';
                }
            }

            $b = $breakdown($actualTotal);

            Contribution::firstOrCreate([
                'member_id'           => $id($c['member']),
                'contribution_period' => $periods['mar'],
            ], array_merge($b, [
                'paid_at'        => $marDate,
                'penalty'        => $penalty,
                'penalty_type'   => $penaltyType,
                'type'           => 'monthly',
                'payment_method' => $c['method'],
                'notes'          => $notes,
            ]));
        }

        // ── April 2026 Contributions (from contributions report) ──
        $aprContributions = [
            ['member' => 'Stella',        'total' => 3500, 'method' => 'zimele',  'paid_at' => '2026-04-19'],
            ['member' => 'Tracy',         'total' => 3500, 'method' => 'mgr',     'paid_at' => '2026-04-19'],
            ['member' => 'Sifuna',        'total' => 3500, 'method' => 'zimele',  'paid_at' => '2026-04-19'],
            ['member' => 'Scolastica',    'total' => 3500, 'method' => 'mpesa',   'paid_at' => '2026-04-19'],
            ['member' => 'Susan',         'total' => 3500, 'method' => 'mpesa',   'paid_at' => '2026-04-19'],
            ['member' => 'Naomi',         'total' => 3500, 'method' => 'mpesa',   'paid_at' => '2026-04-19'],
            ['member' => 'Michael',       'total' => 3500, 'method' => 'mpesa',   'paid_at' => '2026-04-19'],
            ['member' => 'Kavinya',       'total' => 3500, 'method' => 'mpesa',   'paid_at' => '2026-04-19'],
            ['member' => 'Charles',       'total' => 3500, 'method' => 'mpesa',   'paid_at' => '2026-04-19'],
            ['member' => 'Abigail',       'total' => 3500, 'method' => 'zimele',  'paid_at' => '2026-04-18'],
            ['member' => 'Symon Peter',   'total' => 3500, 'method' => 'zimele',  'paid_at' => '2026-04-18'],
            // Torry 6,500 = 3,500 standard + 3,000 loan repayment.
            ['member' => 'Torry',         'total' => 6500, 'method' => 'zimele',  'paid_at' => '2026-04-11', 'loan_repayment' => 3000],
            // Catherine: handled in consolidated block above.
            ['member' => 'Mike C',        'total' => 3500, 'method' => 'zimele',  'paid_at' => '2026-04-03'],
            ['member' => 'Violet',        'total' => 3500, 'method' => 'zimele',  'paid_at' => '2026-03-22 00:00:01'],
        ];

        foreach ($aprContributions as $c) {
            $actualTotal = $c['total'];
            $penalty = 0;
            $penaltyType = null;
            $notes = 'Payment via ' . strtoupper($c['method']);

            // Per-row override: explicit loan repayment — keep base 3,500 breakdown constant.
            if (!empty($c['loan_repayment'])) {
                $loanAmt = (int) $c['loan_repayment'];
                $notes .= '. Includes KES ' . number_format($loanAmt) . ' loan repayment.';
                $actualTotal -= $loanAmt;
            }

            $extra = $actualTotal - 3500;
            if ($extra == 200) {
                $penalty = 200;
                $penaltyType = 'absenteeism';
                $notes .= '. Includes KES 200 fine.';
                $actualTotal -= 200;
            } elseif ($extra > 0) {
                $notes .= '. Extra KES ' . number_format($extra) . ' (catch-up / loan repayment).';
            }

            $b = $breakdown($actualTotal);

            Contribution::firstOrCreate([
                'member_id'           => $id($c['member']),
                'contribution_period' => '2026-04-01',
            ], array_merge($b, [
                'paid_at'        => $c['paid_at'],
                'penalty'        => $penalty,
                'penalty_type'   => $penaltyType,
                'type'           => 'monthly',
                'payment_method' => $c['method'],
                'notes'          => $notes,
            ]));
        }

        // ── May 2026 Contributions ──
        // Lateness fines (KES 100) charged for May; paid status varies per row.
        // Michael's contribution: 2,500 Zimele + 1,000 reclaimed (deducted from his
        // Land Project overpayment as treasurer) = 3,500 standard. Lateness 100 paid.
        // Naomi: 1,700 deposit covers 100 lateness fine for April (paid late) +
        // 1,600 partial May contribution; remaining 1,900 May contribution owed.
        $mayContributions = [
            ['member' => 'Abigail',     'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-04-19'],
            ['member' => 'Charles',     'total' => 3600, 'method' => 'zimele', 'paid_at' => '2026-04-24', 'penalty' => 100, 'penalty_type' => 'lateness'],
            ['member' => 'Susan Ngina', 'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-04-27'],
            ['member' => 'Violet',      'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-04-28'],
            // Catherine: handled in consolidated block above.
            ['member' => 'Symon Peter', 'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-04-29'],
            ['member' => 'Stella',      'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-04-29'],
            ['member' => 'Mike C',      'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-05-02'],
            // Torry 5,000 = 3,500 standard + 1,500 loan repayment. Lateness 100 unpaid.
            ['member' => 'Torry',       'total' => 5000, 'method' => 'zimele', 'paid_at' => '2026-05-06', 'loan_repayment' => 1500, 'penalty' => 100, 'penalty_type' => 'lateness', 'penalty_paid' => false],
            // Tracy 3,500 standard. Lateness 100 unpaid.
            ['member' => 'Tracy',       'total' => 3500, 'method' => 'mpesa',  'paid_at' => '2026-05-07', 'penalty' => 100, 'penalty_type' => 'lateness', 'penalty_paid' => false],
            // Kavinya 3,500 standard. Lateness 100 unpaid.
            ['member' => 'Kavinya',     'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-05-10', 'penalty' => 100, 'penalty_type' => 'lateness', 'penalty_paid' => false],
            // Michael 3,500 = 2,500 Zimele + 1,000 reclaimed from Land Project overpayment.
            // Lateness 100 paid (rolled into the 2,500 deposit? Treasurer note: paid).
            ['member' => 'Michael',     'total' => 3600, 'method' => 'zimele', 'paid_at' => '2026-05-24', 'penalty' => 100, 'penalty_type' => 'lateness',
             'notes_override' => 'Payment via ZIMELE 2,500 + 1,000 reclaimed from Land Project overpayment (treasurer reconciliation) = 3,500 standard contribution. Includes KES 100 lateness fine.'],
            // Sifuna 3,700 = 3,500 standard + 200 absenteeism fine. Deposited Zimele 24 May
            // via M-Pesa 2547 ***** 425 (Joseph).
            ['member' => 'Sifuna',      'total' => 3700, 'method' => 'zimele', 'paid_at' => '2026-05-24',
             'notes_override' => 'Deposited to Zimele 2026-05-24 via M-Pesa 2547*****425 (Joseph). KES 3,500 standard + KES 200 absenteeism fine.'],
            // Scolastica 3,500 standard, sent direct to treasurer via M-Pesa.
            ['member' => 'Scolastica',  'total' => 3500, 'method' => 'mpesa',  'paid_at' => '2026-05-24',
             'notes_override' => 'Sent direct to treasurer via M-Pesa. KES 3,500 standard contribution.'],
            // Naomi 1,700 partial. Includes 100 lateness fine for April (previously unpaid).
            // Remaining 1,900 May contribution still owed.
            ['member' => 'Naomi',       'total' => 1700, 'method' => 'zimele', 'paid_at' => '2026-05-24', 'penalty' => 100, 'penalty_type' => 'lateness',
             'notes_override' => 'Deposited to Zimele 2026-05-24 via M-Pesa 2541*****289 (Naomi). KES 100 lateness fine (April, previously unpaid) + KES 1,600 partial May contribution. KES 1,900 still owed.'],
        ];

        foreach ($mayContributions as $c) {
            $actualTotal = $c['total'];
            $penalty = 0;
            $penaltyType = null;
            $penaltyPaid = true;

            if (!empty($c['notes_override'])) {
                $notes = $c['notes_override'];
            } else {
                $notes = 'Payment via ' . strtoupper($c['method']) . ' (paid in advance for May).';
            }

            // Per-row override: explicit loan repayment — keep base 3,500 breakdown constant.
            if (!empty($c['loan_repayment'])) {
                $loanAmt = (int) $c['loan_repayment'];
                if (empty($c['notes_override'])) {
                    $notes .= ' Includes KES ' . number_format($loanAmt) . ' loan repayment.';
                }
                $actualTotal -= $loanAmt;
            }

            // Per-row override: explicit penalty (lateness 100, absenteeism 200, etc.).
            if (!empty($c['penalty'])) {
                $penalty = (int) $c['penalty'];
                $penaltyType = $c['penalty_type'] ?? 'lateness';
                $penaltyPaid = $c['penalty_paid'] ?? true;
                if ($penaltyPaid) {
                    // Fine paid as part of deposit — strip from contribution total.
                    $actualTotal -= $penalty;
                    if (empty($c['notes_override'])) {
                        $notes .= ' Includes KES ' . number_format($penalty) . ' ' . $penaltyType . ' fine (paid).';
                    }
                } else {
                    // Fine charged but NOT paid — leave contribution total intact, just record the fine.
                    if (empty($c['notes_override'])) {
                        $notes .= ' KES ' . number_format($penalty) . ' ' . $penaltyType . ' fine charged but NOT paid yet.';
                    }
                }
            } else {
                // Auto-detect penalty from amount overage (legacy behaviour).
                $extra = $actualTotal - 3500;
                if ($extra == 200) {
                    $penalty = 200;
                    $penaltyType = 'absenteeism';
                    $notes .= ' Includes KES 200 fine.';
                    $actualTotal -= 200;
                } elseif ($extra == 100) {
                    $penalty = 100;
                    $penaltyType = 'lateness';
                    $notes .= ' Includes KES 100 fine.';
                    $actualTotal -= 100;
                } elseif ($extra > 0) {
                    $notes .= ' Extra KES ' . number_format($extra) . ' (catch-up / loan repayment).';
                }
            }

            $b = $breakdown($actualTotal);

            Contribution::firstOrCreate([
                'member_id'           => $id($c['member']),
                'contribution_period' => '2026-05-01',
            ], array_merge($b, [
                'paid_at'        => $c['paid_at'],
                'penalty'        => $penaltyPaid ? $penalty : 0,
                'penalty_type'   => $penaltyPaid ? $penaltyType : null,
                'type'           => 'monthly',
                'payment_method' => $c['method'],
                'notes'          => $notes,
            ]));
        }

        // ── June & July 2026 Contributions (paid in advance) ──
        // Abigail 7,000 covers BOTH June and July (two-month advance).
        $advanceContributions = [
            ['member' => 'Charles', 'period' => '2026-06-01', 'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-05-28', 'notes' => 'Payment via ZIMELE for June.'],
            ['member' => 'Abigail', 'period' => '2026-06-01', 'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-05-24', 'notes' => 'Payment via ZIMELE — first half of KES 7,000 advance covering June + July.'],
            ['member' => 'Abigail', 'period' => '2026-07-01', 'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-05-24 00:00:01', 'notes' => 'Payment via ZIMELE — second half of KES 7,000 advance covering June + July.'],
            ['member' => 'Michael', 'period' => '2026-06-01', 'total' => 3500, 'method' => 'zimele', 'paid_at' => '2026-05-30', 'notes' => 'Payment via ZIMELE for June.'],
        ];

        foreach ($advanceContributions as $c) {
            $b = $breakdown($c['total']);

            Contribution::firstOrCreate([
                'member_id'           => $id($c['member']),
                'contribution_period' => $c['period'],
            ], array_merge($b, [
                'paid_at'        => $c['paid_at'],
                'penalty'        => 0,
                'penalty_type'   => null,
                'type'           => 'monthly',
                'payment_method' => $c['method'],
                'notes'          => $c['notes'],
            ]));
        }
    }
}
