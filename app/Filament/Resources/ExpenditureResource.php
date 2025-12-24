<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenditureResource\Pages;
use App\Filament\Resources\ExpenditureResource\RelationManagers;
use App\Filament\Traits\HasIconizedTableActions;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Models\Expenditure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenditureResource extends Resource
{
    use HasIconizedTableActions;
    protected static ?string $model = Expenditure::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Financials';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expenditure Details')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255)
                            ->rules('required')
                            ->placeholder('Enter description')
                            ->columnSpan(['md' => 4]),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->placeholder('Enter amount in KES')
                            ->prefix('KES')
                            ->columnSpan(['md' => 4]),
                        Forms\Components\DatePicker::make('spent_at')
                            ->required()
                            ->columnSpan(['md' => 4]),
                        Forms\Components\Select::make('category')
                            ->options([
                                'Meeting' => 'Meeting',
                                'Transport' => 'Transport',
                                'Food' => 'Food',
                                'Other' => 'Other',
                            ])
                            ->placeholder('Select expense category')
                            ->searchable()
                            ->preload()
                            ->columnSpan(['md' => 4]),
                    ])
                    ->columns(12),
            ]);
    }

    public static function table(Table $table): Table
    {
        $self = new self;
        return $self->applyIconizedTableActions(
            $table
                ->striped()
                ->columns([
                    Tables\Columns\TextColumn::make('description')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('amount')
                        ->money('kes')
                        ->sortable()
                        ->summarize(Sum::make()->money('kes')),
                    Tables\Columns\TextColumn::make('spent_at')
                        ->date()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('category')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->defaultSort('spent_at', 'desc')
                ->filters([
                    Tables\Filters\Filter::make('spent_at')
                        ->form([
                            Forms\Components\DatePicker::make('spent_from'),
                            Forms\Components\DatePicker::make('spent_until'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['spent_from'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('spent_at', '>=', $date),
                                )
                                ->when(
                                    $data['spent_until'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('spent_at', '<=', $date),
                                );
                        }),
                ])
                ->actions([
                    Tables\Actions\EditAction::make(),
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ])
        );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenditures::route('/'),
            'create' => Pages\CreateExpenditure::route('/create'),
            'edit' => Pages\EditExpenditure::route('/{record}/edit'),
        ];
    }
}
