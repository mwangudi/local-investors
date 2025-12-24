<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use App\Models\Contribution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestContributions extends TableWidget
{
    protected static ?string $heading = 'Recent Contributions';

    protected int|string|array $columnSpan = [
        'lg' => 2,
        'md' => 2,
        'default' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Contribution::query()
                    ->with('member')
                    ->orderBy('id', 'desc')
            )
            ->defaultPaginationPageOption(5)
            ->paginated(true)
            ->columns([
                TextColumn::make('member.full_name')
                    ->label('Member')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('shares')
                    ->label('Shares')
                    ->money('kes')
                    ->sortable(),

                TextColumn::make('welfare')
                    ->label('Welfare')
                    ->money('kes')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->date('M Y')
                    ->sortable(),
            ]);
    }
}