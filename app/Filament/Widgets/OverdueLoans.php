<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Loan;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class OverdueLoans extends TableWidget
{
    protected static ?string $heading = 'Overdue Loans';
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder|Relation|null
    {
        return Loan::with('member')
            ->where('repaid', false)
            ->where('due_at', '<', now())
            ->orderBy('due_at', 'asc');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('member.full_name')
                ->label('Member')
                ->searchable(),

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

            TextColumn::make('amount')
                ->money('kes')
                ->label('Loan Amount'),

            TextColumn::make('interest_percent')
                ->label('Interest %'),

            TextColumn::make('due_at')
                ->date()
                ->label('Due Date')
                ->sortable(),

            TextColumn::make('balance')
                ->label('Balance')
                ->money('kes'),
        ];
    }
}