<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawalResource\Pages;
use App\Filament\Resources\WithdrawalResource\RelationManagers;
use App\Filament\Traits\HasIconizedTableActions;
use App\Models\Withdrawal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WithdrawalResource extends Resource
{
    use HasIconizedTableActions;

    protected static ?string $model = Withdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationGroup = 'Financials';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Withdrawal Details')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter description')
                            ->columnSpan(['md' => 4]),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->placeholder('Enter amount in KES')
                            ->prefix('KES')
                            ->columnSpan(['md' => 4]),
                        Forms\Components\Select::make('member_id')
                            ->relationship('member', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                            ->label('Withdrawn By')
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->columnSpan(['md' => 4]),
                        Forms\Components\DatePicker::make('withdrawn_at')
                            ->required()
                            ->label('Withdrawn On')
                            ->default(now())
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
                ->defaultSort('withdrawn_at', 'desc')
                ->striped()
                ->columns([
                    Tables\Columns\TextColumn::make('description')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('amount')
                        ->money('kes')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('member.first_name')
                        ->label('Withdrawn By')
                        ->formatStateUsing(fn($record) => $record->member ? "{$record->member->first_name} {$record->member->last_name}" : '')
                        ->sortable()
                        ->searchable(['first_name', 'last_name']),
                    Tables\Columns\TextColumn::make('withdrawn_at')
                        ->date()
                        ->sortable()
                        ->label('Withdrawn On'),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->filters([
                    Tables\Filters\Filter::make('withdrawn_at')
                        ->form([
                            Forms\Components\DatePicker::make('from'),
                            Forms\Components\DatePicker::make('to'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['from'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('withdrawn_at', '>=', $date),
                                )
                                ->when(
                                    $data['to'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('withdrawn_at', '<=', $date),
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
            'index' => Pages\ListWithdrawals::route('/'),
            'create' => Pages\CreateWithdrawal::route('/create'),
            'edit' => Pages\EditWithdrawal::route('/{record}/edit'),
        ];
    }
}
