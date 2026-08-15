<?php

namespace Tests\Feature;

use App\Livewire\Reports\Index;
use App\Models\Contribution;
use App\Models\User;
use Database\Seeders\LoanSeeder;
use Database\Seeders\MembersAndContributionsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(MembersAndContributionsSeeder::class);
        $this->seed(LoanSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
    }

    public function test_it_reports_the_whole_year_when_no_months_are_selected(): void
    {
        $expected = Contribution::whereYear('contribution_period', 2026)->count();
        $this->assertGreaterThan(0, $expected);

        Livewire::test(Index::class)
            ->set('reportType', 'contributions')
            ->set('selectedYear', 2026)
            ->set('selectedMonths', [])
            ->call('generateReport')
            ->assertSet('reportData', fn ($data) => count($data) === $expected);
    }

    public function test_it_limits_the_report_to_the_selected_months(): void
    {
        $expected = Contribution::whereYear('contribution_period', 2026)
            ->whereMonth('contribution_period', 3)
            ->count();
        $this->assertGreaterThan(0, $expected);

        Livewire::test(Index::class)
            ->set('reportType', 'contributions')
            ->set('selectedYear', 2026)
            ->set('selectedMonths', [3])
            ->call('generateReport')
            ->assertSet('reportData', fn ($data) => count($data) === $expected);
    }

    public function test_it_never_returns_contributions_from_another_year(): void
    {
        Livewire::test(Index::class)
            ->set('reportType', 'contributions')
            ->set('selectedYear', 2025)
            ->set('selectedMonths', [])
            ->call('generateReport')
            ->assertSet('reportData', fn ($data) => collect($data)->every(
                fn ($row) => str_ends_with($row['for_month'], '2025')
            ));
    }

    public function test_selecting_a_year_with_no_contributions_returns_nothing(): void
    {
        Livewire::test(Index::class)
            ->set('reportType', 'contributions')
            ->set('selectedYear', 2021)
            ->set('selectedMonths', [])
            ->call('generateReport')
            ->assertSet('reportData', fn ($data) => count($data) === 0);
    }
}
