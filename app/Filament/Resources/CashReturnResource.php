<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashReturnResource\Pages;
use App\Filament\Resources\CashReturnResource\RelationManagers;
use App\Models\CashReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashReturnResource extends Resource
{
    protected static ?string $model = CashReturn::class;

    use \App\Filament\Traits\HasIconizedTableActions;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square'; // Icon representing return
    protected static ?string $navigationGroup = 'Financials';
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Return Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('KES')
                            ->placeholder('Enter amount being returned')
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('returned_at')
                            ->label('Date Returned')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('description')
                            ->label('Reason / Description')
                            ->rows(3)
                            ->columnSpan('full')
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $self = new static();
        return $self->applyIconizedTableActions(
            $table
                ->columns([
                    Tables\Columns\TextColumn::make('returned_at')
                        ->label('Date')
                        ->date()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('amount')
                        ->money('KES')
                        ->sortable()
                        ->summarize(Tables\Columns\Summarizers\Sum::make()->money('KES')),
                    Tables\Columns\TextColumn::make('description')
                        ->limit(50),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->defaultSort('returned_at', 'desc')
                ->filters([
                    Tables\Filters\Filter::make('returned_at')
                        ->form([
                            Forms\Components\DatePicker::make('returned_from'),
                            Forms\Components\DatePicker::make('returned_until'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['returned_from'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('returned_at', '>=', $date),
                                )
                                ->when(
                                    $data['returned_until'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('returned_at', '<=', $date),
                                );
                        }),
                ])
                ->actions([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCashReturns::route('/'),
            'create' => Pages\CreateCashReturn::route('/create'),
            'edit' => Pages\EditCashReturn::route('/{record}/edit'),
        ];
    }
}
