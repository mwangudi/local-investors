<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Contribution;

class WelfareSharesComparisonChart extends ChartWidget
{
    protected static ?string $heading = 'Shares vs Welfare Breakdown';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '270px';
    protected int|string|array $columnSpan = [
        'lg' => 2,
        'md' => 2,
        'default' => 1,
    ];

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Contribution Breakdown',
                    'data' => [
                        Contribution::sum('shares'),
                        Contribution::sum('welfare'),
                    ],
                    'backgroundColor' => [
                        '#16A34A',
                        '#ff8c00',
                    ],
                ],
            ],
            'labels' => ['Shares', 'Welfare'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false, 
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'display' => false, 
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }
}
