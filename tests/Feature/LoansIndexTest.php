<?php

namespace Tests\Feature;

use App\Livewire\Loans\Index;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\LoanSeeder;
use Database\Seeders\MeetingJune2026Seeder;
use Database\Seeders\MembersAndContributionsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoansIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(MembersAndContributionsSeeder::class);
        $this->seed(LoanSeeder::class);
        $this->seed(MeetingJune2026Seeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
    }

    public function test_it_filters_by_member(): void
    {
        $sifuna = $this->member('Joseph Sifuna');

        Livewire::test(Index::class)
            ->set('memberFilter', (string) $sifuna->id)
            ->assertViewHas('loans', fn ($loans) => $loans->every(
                fn (Loan $loan) => $loan->member_id === $sifuna->id
            ) && $loans->total() === 2);
    }

    public function test_it_filters_by_status_and_due_date_range(): void
    {
        Livewire::test(Index::class)
            ->set('statusFilter', 'repaid')
            ->assertViewHas('loans', fn ($loans) => $loans->every(
                fn (Loan $loan) => $loan->status === 'repaid'
            ));

        Livewire::test(Index::class)
            ->set('dueFrom', '2026-08-01')
            ->set('dueTo', '2026-08-31')
            ->assertViewHas('loans', fn ($loans) => $loans->total() === 3);
    }

    public function test_it_searches_on_a_full_member_name(): void
    {
        Livewire::test(Index::class)
            ->set('search', 'Charles Kingori')
            ->assertViewHas('loans', fn ($loans) => $loans->total() === 1
                && $loans->first()->member->full_name === 'Charles Kingori');
    }

    public function test_it_lists_only_overdue_loans_when_requested(): void
    {
        $this->travelTo('2026-09-15');

        // The three 9 Aug rollovers plus Sifuna's 31,500, still unpaid since 30 Apr.
        Livewire::test(Index::class)
            ->set('overdueOnly', true)
            ->assertViewHas('loans', fn ($loans) => $loans->total() === 4
                && $loans->every(fn (Loan $loan) => $loan->is_overdue));
    }

    public function test_it_rejects_sorting_on_a_column_that_is_not_whitelisted(): void
    {
        Livewire::test(Index::class)
            ->call('sortBy', 'member_id); drop table loans;--')
            ->assertSet('sortField', 'disbursed_at')
            ->call('sortBy', 'due_at')
            ->assertSet('sortField', 'due_at')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_it_paginates_and_resets_to_the_first_page_when_filtering(): void
    {
        Livewire::test(Index::class)
            ->set('perPage', 2)
            ->assertViewHas('loans', fn ($loans) => $loans->perPage() === 2)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'Sifuna')
            ->assertSet('paginators.page', 1);
    }

    public function test_it_clears_all_filters(): void
    {
        Livewire::test(Index::class)
            ->assertDontSee('Clear all')
            ->set('search', 'Sifuna')
            ->assertSee('Clear all')
            ->set('overdueOnly', true)
            ->set('statusFilter', 'repaid')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('overdueOnly', false)
            ->assertSet('statusFilter', '')
            ->assertDontSee('Clear all');
    }

    public function test_it_reports_totals_for_the_filtered_set(): void
    {
        $kingori = $this->member('Charles Kingori');

        Livewire::test(Index::class)
            ->set('memberFilter', (string) $kingori->id)
            ->assertViewHas('totalPrincipal', 15000.0)
            ->assertViewHas('totalOutstanding', 16500.0);
    }

    private function member(string $name): Member
    {
        return Member::all()->first(fn (Member $member) => $member->full_name === $name);
    }
}
