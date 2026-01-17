<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Filament\Traits\HasIconizedTableActions;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\ContributionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class ContributionRelationManager extends RelationManager
{
    use HasIconizedTableActions;
    protected static string $relationship = 'contributions';
    protected static ?string $title = 'Member Contributions';

    public function form(Form $form): Form
    {
        return app(ContributionResource::class)::form($form);
    }

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        $self = new self;
        return $self->applyIconizedTableActions(
            $table
                ->striped()
                ->defaultSort('created_at', 'desc')
                ->columns([
                    TextColumn::make('shares')
                        ->label('Shares')
                        ->money('kes')
                        ->sortable(),

                    TextColumn::make('welfare')
                        ->label('Welfare')
                        ->money('kes')
                        ->sortable(),

                    TextColumn::make('merry_go_round')
                        ->label('MGR')
                        ->money('kes')
                        ->sortable(),

                    BadgeColumn::make('type')
                        ->label('Type')
                        ->colors([
                            'primary' => 'monthly',
                            'success' => 'shares',
                            'info' => 'welfare',
                            'warning' => 'mgr',
                            'danger' => 'penalty',
                            'gray' => 'other',
                        ])
                        ->sortable(),

                    TextColumn::make('penalty')
                        ->label('Penalty')
                        ->money('kes')
                        ->sortable(),

                    BadgeColumn::make('penalty_type')
                        ->label('Penalty Type')
                        ->colors([
                            'danger' => 'loan_default',
                            'warning' => 'late_payment',
                            'info' => 'late_shares',
                            'gray' => 'meeting_absence',
                            'primary' => 'other',
                        ])
                        ->sortable(),

                    TextColumn::make('paid_at')
                        ->label('Paid At')
                        ->date()
                        ->sortable(),
                ])
                ->headerActions([
                    CreateAction::make()
                        ->label('Add Contribution')
                        ->icon('heroicon-o-plus')
                        ->hidden(fn() => !auth()->user()?->hasRole('admin'))
                        ->using(function (array $data, RelationManager $livewire) {
                            $data['member_id'] = $livewire->ownerRecord->id;
                            return $livewire->getRelationship()->create($data);
                        }),
                ])
                ->actions([
                    EditAction::make()
                        ->hidden(fn() => !auth()->user()?->hasRole('admin')),
                    DeleteAction::make()
                        ->hidden(fn() => !auth()->user()?->hasRole('admin')),
                ])
        );
    }

}
