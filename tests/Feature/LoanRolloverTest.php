<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Member;
use Database\Seeders\LoanSeeder;
use Database\Seeders\MembersAndContributionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanRolloverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(MembersAndContributionsSeeder::class);
        $this->seed(LoanSeeder::class);
    }

    public function test_it_reissues_the_outstanding_balance_as_a_new_loan(): void
    {
        $loan = $this->partiallyPaidLoan();

        // 31,500 + 10% = 44,100 payable, less 21,400 repaid.
        $this->assertSame(13250.0, (float) $loan->balance);

        $replacement = $loan->rollOverBalance('2026-08-14', '2026-10-11');

        $this->assertSame(13250.0, (float) $replacement->amount);
        $this->assertSame($loan->id, $replacement->parent_loan_id);
        $this->assertSame(Loan::STATUS_DISBURSED, $replacement->status);
        $this->assertSame('2026-08-14', $replacement->disbursed_at->format('Y-m-d'));
        $this->assertSame('2026-10-11', $replacement->due_at->format('Y-m-d'));
        $this->assertSame(14575.0, (float) $replacement->balance);

        $loan->refresh();
        $this->assertTrue($loan->repaid);
        $this->assertSame(Loan::STATUS_REPAID, $loan->status);
        $this->assertSame(0, (int) $loan->balance);
        $this->assertSame(21400.0, (float) $loan->repaid_amount, 'Only real repayments count as repaid.');
        $this->assertSame($replacement->id, $loan->rolloverLoan->id);
    }

    public function test_it_refuses_to_roll_over_the_same_loan_twice(): void
    {
        $loan = $this->partiallyPaidLoan();
        $loan->rollOverBalance('2026-08-14', '2026-10-11');

        $this->expectException(\DomainException::class);
        $loan->refresh()->rollOverBalance('2026-08-14', '2026-10-11');
    }

    public function test_it_refuses_to_roll_over_a_settled_loan(): void
    {
        $settled = Loan::where('status', Loan::STATUS_REPAID)->firstOrFail();

        $this->expectException(\DomainException::class);
        $settled->rollOverBalance('2026-08-14', '2026-10-11');
    }

    public function test_it_leaves_the_original_untouched_when_the_rollover_fails(): void
    {
        $settled = Loan::where('status', Loan::STATUS_REPAID)->firstOrFail();
        $loanCount = Loan::count();

        try {
            $settled->rollOverBalance('2026-08-14', '2026-10-11');
        } catch (\DomainException) {
            // expected
        }

        $this->assertSame($loanCount, Loan::count(), 'No replacement loan should be created.');
    }

    public function test_every_loan_gets_a_unique_reference_used_for_routing(): void
    {
        $loan = Loan::firstOrFail();

        $this->assertSame('reference', $loan->getRouteKeyName());
        $this->assertMatchesRegularExpression('/^[A-Z]{3}[0-9]{3}$/', $loan->reference);
        $this->assertSame(Loan::count(), Loan::distinct()->count('reference'));

        $rolled = $this->partiallyPaidLoan()->rollOverBalance('2026-08-14', '2026-10-11');
        $this->assertMatchesRegularExpression('/^[A-Z]{3}[0-9]{3}$/', $rolled->reference);
    }

    private function partiallyPaidLoan(): Loan
    {
        $sifuna = Member::all()->first(fn (Member $member) => $member->full_name === 'Joseph Sifuna');

        return Loan::where('member_id', $sifuna->id)
            ->where('amount', 31500)
            ->firstOrFail();
    }
}
