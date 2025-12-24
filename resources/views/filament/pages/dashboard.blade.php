<x-filament::page>

    <x-filament-widgets::widgets
        :widgets="[
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\LoanSummary::class,
            \App\Filament\Widgets\MonthlyContributionsChart::class,
            \App\Filament\Widgets\WelfareSharesComparisonChart::class,
            \App\Filament\Widgets\LatestLoans::class,
            \App\Filament\Widgets\LatestContributions::class,
        ]"
        :columns="[
            'default' => 1,
            'md' => 2,
            'lg' => 4,
        ]"
    />

</x-filament::page>
