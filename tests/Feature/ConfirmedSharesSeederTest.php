<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Income;
use App\Models\Member;
use Database\Seeders\ConfirmedSharesSeeder;
use Database\Seeders\MemberContributionsSeeder;
use Database\Seeders\MembersAndContributionsSeeder;
use Database\Seeders\OpeningBalancesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmedSharesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reconciles_shares_and_registration_fees_with_the_book(): void
    {
        $this->seed(MembersAndContributionsSeeder::class);
        $this->seed(OpeningBalancesSeeder::class);
        $this->seed(MemberContributionsSeeder::class);
        $this->seed(ConfirmedSharesSeeder::class);

        $expectedTotals = [
            'Charles Kingori' => 73000,
            'Torry Mabale' => 73000,
            'Susan Ngina Muswii' => 73000,
            'Kavinya Oduor' => 72000,
            'Naomi Nyoroka' => 70000,
            'Joseph Sifuna' => 72000,
            'Abigail Njoki' => 72000,
            'Scolastica Muswii' => 70000,
            'Michael Wangudi' => 70000,
            'Tracy Muendi' => 70000,
            'Stella Mutheu' => 69000,
            'Catherine Masinde' => 73400,
            'Symon Peter Ngatia' => 40000,
            'Violet Kamadi' => 40000,
            'Mike C' => 35000,
        ];

        foreach ($expectedTotals as $name => $expectedTotal) {
            $member = $this->member($name);

            $this->assertSame(
                $expectedTotal,
                (int) Contribution::where('member_id', $member->id)->sum('shares'),
                "Unexpected confirmed shares total for {$name}."
            );
        }

        $this->assertPeriodShares('Susan Ngina Muswii', '2025-06-01', 22000);
        $this->assertPeriodShares('Susan Ngina Muswii', '2025-11-01', 0);
        $this->assertPeriodShares('Tracy Muendi', '2025-09-01', 16000);
        $this->assertPeriodShares('Catherine Masinde', '2026-01-01', 30500);
        $this->assertPeriodShares('Mike C', '2026-07-01', 12500);

        $this->assertSame(
            0,
            Contribution::whereNull('paid_at')->count(),
            'Contributions without a paid_at break search filters.'
        );

        $seeded = Contribution::query()
            ->where('member_id', $this->member('Susan Ngina Muswii')->id)
            ->whereDate('contribution_period', '2025-06-01')
            ->firstOrFail();

        $this->assertSame('2025-06-08', $seeded->paid_at->format('Y-m-d'), 'Seeded rows use the 2nd Sunday.');

        $registrations = [
            'Catherine Masinde' => '2025-01-12',
            'Violet Kamadi' => '2025-01-12',
            'Symon Peter Ngatia' => '2025-01-12',
            'Mike C' => '2025-02-09',
        ];

        foreach ($registrations as $name => $joinedAt) {
            $member = $this->member($name);

            $this->assertSame($joinedAt, $member->join_date->format('Y-m-d'));
            $fee = Income::where('member_id', $member->id)
                ->where('category', 'joining_fee')
                ->firstOrFail();

            $this->assertSame(2000, (int) $fee->amount);
            $this->assertSame($joinedAt, $fee->received_at->format('Y-m-d'));
        }

        $this->assertSame(4, Income::where('category', 'joining_fee')->count());
    }

    private function assertPeriodShares(string $name, string $period, int $expected): void
    {
        $member = $this->member($name);
        $actual = Contribution::query()
            ->where('member_id', $member->id)
            ->whereDate('contribution_period', $period)
            ->sum('shares');

        $this->assertSame($expected, (int) $actual);
    }

    private function member(string $name): Member
    {
        $member = Member::all()->first(
            fn (Member $member): bool => $member->full_name === $name
        );

        $this->assertNotNull($member, "Member not found: {$name}");

        return $member;
    }
}