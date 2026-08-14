<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Member;
use Database\Seeders\LoanSeeder;
use Database\Seeders\MeetingJune2026Seeder;
use Database\Seeders\MembersAndContributionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_the_june_rollover_loans_and_kingoris_august_loan(): void
    {
        $this->seedLoanHistory();

        $rollovers = [
            'Naomi Nyoroka' => 13500,
            'Scolastica Muswii' => 13000,
            'Michael Wangudi' => 3000,
        ];

        foreach ($rollovers as $name => $amount) {
            $loan = $this->loanFor($name, '2026-06-14');

            $this->assertSame($amount, (int) $loan->amount);
            $this->assertSame('2026-08-09', $loan->due_at->format('Y-m-d'));
            $this->assertSame(2, (int) $loan->term_months);
            $this->assertFalse($loan->repaid);
        }

        $kingori = $this->loanFor('Charles Kingori', '2026-08-10');

        $this->assertSame(15000, (int) $kingori->amount);
        $this->assertSame('2026-10-11', $kingori->due_at->format('Y-m-d'));
        $this->assertSame(2, (int) $kingori->term_months);
        $this->assertFalse($kingori->repaid);
    }

    public function test_it_closes_the_pre_june_loans_that_were_rolled_over(): void
    {
        $this->seedLoanHistory();

        foreach (['Naomi Nyoroka', 'Scolastica Muswii', 'Michael Wangudi'] as $name) {
            $openPreJune = Loan::where('member_id', $this->member($name)->id)
                ->where('repaid', false)
                ->whereDate('disbursed_at', '<', '2026-06-01')
                ->count();

            $this->assertSame(0, $openPreJune, "{$name} still has an open pre-June loan.");
        }
    }

    public function test_rerunning_the_june_seeder_does_not_duplicate_rollover_loans(): void
    {
        $this->seedLoanHistory();
        $this->seed(MeetingJune2026Seeder::class);

        foreach (['Naomi Nyoroka', 'Scolastica Muswii', 'Michael Wangudi'] as $name) {
            $this->assertSame(
                1,
                Loan::where('member_id', $this->member($name)->id)
                    ->whereDate('disbursed_at', '2026-06-14')
                    ->count(),
                "Duplicate 14 Jun 2026 loan created for {$name}."
            );
        }
    }

    public function test_the_overdue_penalty_only_applies_from_the_month_after_the_due_date(): void
    {
        $this->seedLoanHistory();

        $loan = $this->loanFor('Naomi Nyoroka', '2026-06-14');

        $this->travelTo('2026-08-14');
        $this->assertFalse($loan->is_overdue, 'Loan should not attract a penalty within the due month.');
        $this->assertSame(0.0, (float) $loan->overdue_penalty);
        $this->assertSame(14850.0, (float) $loan->total_payable);

        $this->travelTo('2026-09-01');
        $this->assertTrue($loan->is_overdue, 'Loan should be overdue from the month after the due date.');
        $this->assertSame(4050.0, (float) $loan->overdue_penalty);
    }

    private function seedLoanHistory(): void
    {
        $this->seed(MembersAndContributionsSeeder::class);
        $this->seed(LoanSeeder::class);
        $this->seed(MeetingJune2026Seeder::class);
    }

    private function loanFor(string $name, string $disbursedAt): Loan
    {
        $loan = Loan::where('member_id', $this->member($name)->id)
            ->whereDate('disbursed_at', $disbursedAt)
            ->first();

        $this->assertNotNull($loan, "Loan not found for {$name} disbursed {$disbursedAt}.");

        return $loan;
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
