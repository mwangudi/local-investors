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
        // ── Members (16 total — 15 original + Susan) ─────────────
        $members = [
            ['first_name' => 'Michael',     'last_name' => 'Wangudi',      'phone' => null, 'email' => null],
            ['first_name' => 'Ngina',       'last_name' => 'Muswii',       'phone' => null, 'email' => null],
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
            ['first_name' => 'Naomi',       'last_name' => 'Mutuamwari',   'phone' => null, 'email' => null],
            ['first_name' => 'Kavinya',     'last_name' => 'Oduor',        'phone' => null, 'email' => null],
            ['first_name' => 'Susan',       'last_name' => 'Muswii',       'phone' => null, 'email' => null],
        ];

        $memberMap = [];
        foreach ($members as $m) {
            $member = Member::firstOrCreate(
                ['first_name' => $m['first_name'], 'last_name' => $m['last_name']],
                array_merge($m, [
                    'join_date'  => '2025-01-01',
                    'is_active'  => true,
                    'notification_preference' => 'all',
                ])
            );
            $memberMap[$m['first_name'] . ' ' . $m['last_name']] = $member->id;
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
        // Base 3,500: shares=1000, welfare=500, merry=2000
        // Extra above 3,500 → added to shares (catch-up)
        // Below 3,500 → reduce merry first, then welfare
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
            'aug' => '2025-08-10',
            'sep' => '2025-09-14',
            'oct' => '2025-10-12',
            'nov' => '2025-11-09',
            'dec' => '2025-12-14',
            'jan' => '2026-01-11',
            'feb' => '2026-02-08',
            'mar' => '2026-03-22', // 3rd-week exception (moved from March 8)
        ];

        // ── Historical contributions: Aug 2025 – Jan 2026 ────────
        // From the SACCO records table. Dash (-) = did not contribute.
        $historical = [
            'aug' => [
                ['member' => 'Michael',       'total' => 6500],
                ['member' => 'Charles',       'total' => 3500],
                ['member' => 'Mike C',        'total' => 3500],
                ['member' => 'Symon Peter',   'total' => 3500],
                ['member' => 'Stella',        'total' => 4500],
                ['member' => 'Naomi',         'total' => 4500],
                ['member' => 'Torry',         'total' => 16000],
                // Aby: absent Aug
                ['member' => 'Tracy',         'total' => 14000],
                ['member' => 'Scolastica',    'total' => 3500],
                ['member' => 'Susan',         'total' => 3500],
                ['member' => 'Kavinya',       'total' => 3500],
                ['member' => 'Catherine',     'total' => 3500],
                ['member' => 'Sifuna',        'total' => 1500],
                ['member' => 'Violet',        'total' => 3500],
            ],
            'sep' => [
                ['member' => 'Michael',       'total' => 7000],
                ['member' => 'Charles',       'total' => 3500],
                ['member' => 'Mike C',        'total' => 3500],
                ['member' => 'Symon Peter',   'total' => 3500],
                ['member' => 'Stella',        'total' => 3500],
                ['member' => 'Naomi',         'total' => 5500],
                // Torry: absent Sep
                ['member' => 'Abigail',       'total' => 5000],
                ['member' => 'Tracy',         'total' => 18500],
                ['member' => 'Scolastica',    'total' => 3500],
                ['member' => 'Susan',         'total' => 3900],
                ['member' => 'Kavinya',       'total' => 10000],
                ['member' => 'Catherine',     'total' => 4000],
                ['member' => 'Sifuna',        'total' => 7000],
                ['member' => 'Violet',        'total' => 3500],
            ],
            'oct' => [
                // Michael: absent Oct
                // Kingori: absent Oct
                // MikeC: absent Oct
                // Ngatia: absent Oct
                // Stella: absent Oct
                // Naomi: absent Oct
                // Torry: absent Oct
                ['member' => 'Abigail',       'total' => 5500],
                // Tracy: absent Oct
                ['member' => 'Scolastica',    'total' => 3500],
                ['member' => 'Susan',         'total' => 3500],
                // Kavinya: absent Oct
                ['member' => 'Catherine',     'total' => 4000],
                ['member' => 'Sifuna',        'total' => 3500],
                // Violet: absent Oct
            ],
            'nov' => [
                ['member' => 'Michael',       'total' => 2000],
                // Most members absent Nov
                ['member' => 'Catherine',     'total' => 4000],
            ],
            'dec' => [
                // Most members absent Dec
                ['member' => 'Catherine',     'total' => 4000],
            ],
            'jan' => [
                // Most members absent Jan
                ['member' => 'Scolastica',    'total' => 20000],
                ['member' => 'Catherine',     'total' => 4000],
            ],
        ];

        foreach ($historical as $month => $contributions) {
            $date = $meetingDates[$month];
            foreach ($contributions as $c) {
                $b = $breakdown($c['total']);
                $extra = $c['total'] - 3500;
                $notes = null;

                if ($extra > 0) {
                    $notes = 'Extra KES ' . number_format($extra) . ' (catch-up shares / loan repayment).';
                } elseif ($extra < 0) {
                    $notes = 'Partial payment KES ' . number_format($c['total']) . '.';
                }

                Contribution::firstOrCreate([
                    'member_id' => $id($c['member']),
                    'paid_at'   => $date,
                ], array_merge($b, [
                    'penalty'      => 0,
                    'penalty_type' => null,
                    'notes'        => $notes,
                ]));
            }
        }

        // ── February 2026 Contributions (Feb 8) ──────────────────
        $febDate = $meetingDates['feb'];
        $febContributions = [
            // MPESA
            ['member' => 'Symon Peter',   'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Scolastica',    'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Violet',        'total' => 3530, 'method' => 'mpesa'],
            ['member' => 'Ngina Muswii',  'total' => 3530, 'method' => 'mpesa'],
            ['member' => 'Abigail',       'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Naomi',         'total' => 5000, 'method' => 'mpesa'],
            ['member' => 'Kavinya',       'total' => 3700, 'method' => 'mpesa'],
            ['member' => 'Michael',       'total' => 3700, 'method' => 'mpesa'],
            // Zimele
            ['member' => 'Torry',         'total' => 7400, 'method' => 'zimele'],
            ['member' => 'Tracy',         'total' => 3700, 'method' => 'zimele'],
            ['member' => 'Mike C',        'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Catherine',     'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Charles',       'total' => 3500, 'method' => 'zimele'],
        ];

        foreach ($febContributions as $c) {
            $b = $breakdown($c['total']);
            $extra = $c['total'] - 3500;
            $penalty = 0;
            $penaltyType = null;
            $notes = 'Payment via ' . strtoupper($c['method']);

            if ($extra == 200) {
                $penalty = 200;
                $penaltyType = 'late_attendance';
                $notes .= '. Includes KES 200 fine.';
            } elseif ($extra > 0) {
                $notes .= '. Extra KES ' . number_format($extra) . ' (catch-up / loan repayment).';
            }

            Contribution::firstOrCreate([
                'member_id' => $id($c['member']),
                'paid_at'   => $febDate,
            ], array_merge($b, [
                'penalty'      => $penalty,
                'penalty_type' => $penaltyType,
                'notes'        => $notes,
            ]));
        }

        // ── March 2026 Contributions (March 22) ──────────────────
        $marDate = $meetingDates['mar'];
        $marContributions = [
            // MPESA
            ['member' => 'Ngina Muswii',  'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Violet',        'total' => 3500, 'method' => 'mpesa'],
            ['member' => 'Michael',       'total' => 3700, 'method' => 'mpesa'],
            // Zimele
            ['member' => 'Sifuna',        'total' => 7200, 'method' => 'zimele'],
            ['member' => 'Tracy',         'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Symon Peter',   'total' => 3700, 'method' => 'zimele'],
            ['member' => 'Catherine',     'total' => 4000, 'method' => 'zimele'],
            ['member' => 'Torry',         'total' => 8500, 'method' => 'zimele'],
            ['member' => 'Mike C',        'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Abigail',       'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Charles',       'total' => 3500, 'method' => 'zimele'],
            ['member' => 'Stella',        'total' => 3500, 'method' => 'zimele'],
        ];

        foreach ($marContributions as $c) {
            $b = $breakdown($c['total']);
            $extra = $c['total'] - 3500;
            $penalty = 0;
            $penaltyType = null;
            $notes = 'Payment via ' . strtoupper($c['method']);

            if ($extra == 200) {
                $penalty = 200;
                $penaltyType = 'late_attendance';
                $notes .= '. Includes KES 200 fine.';
            } elseif ($extra > 0) {
                $notes .= '. Extra KES ' . number_format($extra) . ' (catch-up / loan repayment).';
            }

            Contribution::firstOrCreate([
                'member_id' => $id($c['member']),
                'paid_at'   => $marDate,
            ], array_merge($b, [
                'penalty'      => $penalty,
                'penalty_type' => $penaltyType,
                'notes'        => $notes,
            ]));
        }
    }
}
