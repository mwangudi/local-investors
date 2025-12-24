<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Member;
use Carbon\Carbon;

class MemberGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'Member Growth';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '600px';
    protected int|string|array $columnSpan = [
        'lg' => 2,
        'md' => 2,
        'default' => 1,
    ];

    protected function getData(): array
    {
        $data = Member::selectRaw('MONTH(join_date) AS month, COUNT(*) AS total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'New Members',
                    'data' => array_values($data),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_map(
                fn($m) => Carbon::create()->month($m)->format('M'),
                array_keys($data)
            ),
        ];
    }

    protected function getChartOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}