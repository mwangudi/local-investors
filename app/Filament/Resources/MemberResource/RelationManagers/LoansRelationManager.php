<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Filament\Traits\HasIconizedFormActions;
use App\Filament\Traits\HasIconizedTableActions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms\Form;
use App\Filament\Resources\LoanResource;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class LoansRelationManager extends RelationManager
{
    use HasIconizedTableActions, HasIconizedFormActions;
    protected static string $relationship = 'loans';
    protected static ?string $title = 'Member Loans';

    public function form(Form $form): Form
    {
        return app(LoanResource::class)::form($form);
    }

    public function table(Table $table): Table
    {
        $self = new self;
        return $self->applyIconizedTableActions(
            $table
                ->striped()
                ->columns([
                    TextColumn::make('amount')->money('kes'),
                    TextColumn::make('interest_percent')->suffix('%'),
                    TextColumn::make('total_payable')
                        ->label('Total Payable (KES)')
                        ->money('kes')
                        ->sortable(),
                    TextColumn::make('balance')
                        ->label('Balance (KES)')
                        ->money('kes')
                        ->sortable(),
                    TextColumn::make('disbursed_at')->date('M Y'),
                    TextColumn::make('due_at')->date('M Y'),
                    BadgeColumn::make('repaid')
                        ->label('Status')
                        ->colors([
                            'success' => fn($state) => $state,
                            'danger' => fn($state) => !$state,
                        ])
                        ->formatStateUsing(fn($state) => $state ? 'Repaid' : 'Ongoing'),
                ])
                ->defaultSort('disbursed_at', 'desc')
                ->headerActions([
                    CreateAction::make()
                        ->label('New Loan')
                        ->icon('heroicon-o-plus')
                        // Hide if member has an unpaid loan OR user is not admin
                        ->hidden(
                            fn(RelationManager $livewire) =>
                            !auth()->user()?->hasRole('admin') ||
                            $livewire->ownerRecord
                                ->loans()
                                ->where('repaid', false)
                                ->exists()
                        )
                        ->using(function (array $data, RelationManager $livewire) {
                            $data['member_id'] = $livewire->ownerRecord->id;
                            return $livewire->getRelationship()->create($data);
                        }),
                ])
                ->actions([
                    EditAction::make()
                        ->hidden(fn($record) => !auth()->user()?->hasRole('admin') || $record->repaid),
                    DeleteAction::make()
                        ->hidden(fn($record) => !auth()->user()?->hasRole('admin') || $record->repaid),
                ])
        );
    }
}