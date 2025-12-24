<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Contribution;
use Carbon\Carbon;

class MonthlyContributionsChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Contributions';
    protected int|string|array $columnSpan = [
        'lg' => 2,
        'md' => 2,
        'default' => 1,
    ];
    protected function getData(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            return Contribution::whereMonth('paid_at', $month)
                ->whereYear('paid_at', now()->year)
                ->sum('shares') +
                Contribution::whereMonth('paid_at', $month)
                ->whereYear('paid_at', now()->year)
                ->sum('welfare');
        });

        return [
            'datasets' => [
                [
                    'label' => 'KES',
                    'data' => $months->toArray(),
                ],
            ],
            'labels' => collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}