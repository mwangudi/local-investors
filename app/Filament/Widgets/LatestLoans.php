<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Loan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestLoans extends TableWidget
{
    protected static ?string $heading = 'Recent Loans';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'lg' => 2,
        'default' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Loan::query()
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

                TextColumn::make('amount')
                    ->label('Amount (KES)')
                    ->money('kes')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => Loan::STATUS_APPLIED,
                        'info' => Loan::STATUS_APPROVED,
                        'primary' => Loan::STATUS_DISBURSED,
                        'success' => Loan::STATUS_REPAID,
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Loan::STATUS_APPLIED => 'Applied',
                        Loan::STATUS_APPROVED => 'Approved',
                        Loan::STATUS_DISBURSED => 'Active',
                        Loan::STATUS_REPAID => 'Repaid',
                        default => $state,
                    }),

                TextColumn::make('due_at')
                    ->label('Due')
                    ->date('M Y')
                    ->sortable(),

                TextColumn::make('disbursed_at')
                    ->label('Issued')
                    ->date('M Y')
                    ->sortable(),
            ]);
    }
}