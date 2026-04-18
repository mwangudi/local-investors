<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Filament\Resources\IncomeResource\RelationManagers;
use App\Models\Income;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasIconizedTableActions;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;
    use HasIconizedTableActions;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financials';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Income Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('KES')
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('received_at')
                            ->label('Date Received')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),

                        Forms\Components\Select::make('category')
                            ->options([
                                'Loan Interest' => 'Loan Interest',
                                'Project' => 'Project',
                                'Registration' => 'Registration',
                                'Fines' => 'Fines',
                                'Other' => 'Other',
                            ])
                            ->required()
                            ->default('Other')
                            ->live()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        Forms\Components\Select::make('fine_type')
                            ->options([
                                'Lateness to meetings' => 'Lateness to meeting(s)',
                                'Late loan repayment' => 'Late loan repayment',
                                'Lateness to pay shares' => 'Lateness to pay shares',
                                'Other' => 'Other',
                            ])
                            ->searchable()
                            ->preload()
                            ->label('Fine Type')
                            ->nullable()
                            ->visible(fn(Forms\Get $get) => $get('category') === 'Fines')
                            ->columnSpan(1),

                        Forms\Components\Select::make('member_id')
                            ->relationship('member', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->label('Member')
                            ->placeholder('Select a member (optional)')
                            ->nullable()
                            ->columnSpan(1),

                        Forms\Components\Select::make('loan_id')
                            ->relationship('loan', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => "Loan #{$record->id} - {$record->member->full_name}")
                            ->searchable()
                            ->preload()
                            ->label('Linked Loan')
                            ->nullable()
                            ->visible(fn(Forms\Get $get) => $get('category') === 'Loan Interest')
                            ->columnSpan(1),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Related Project/Event (Optional)')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('description')
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
                    Tables\Columns\TextColumn::make('received_at')
                        ->label('Date')
                        ->date()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('amount')
                        ->money('KES')
                        ->sortable()
                        ->summarize(Tables\Columns\Summarizers\Sum::make()->money('KES')),
                    Tables\Columns\TextColumn::make('loan.member.full_name')
                        ->label('From Member')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('description')
                        ->limit(50),
                    Tables\Columns\TextColumn::make('category')
                        ->searchable()
                        ->sortable()
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'Loan Interest' => 'success',
                            'Project' => 'info',
                            'Fines' => 'danger',
                            default => 'gray',
                        }),
                    Tables\Columns\TextColumn::make('fine_type')
                        ->label('Fine Type')
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('project.name')
                        ->label('Project')
                        ->searchable(),
                ])
                ->defaultSort('received_at', 'desc')
                ->filters([
                    Tables\Filters\Filter::make('received_at')
                        ->form([
                            Forms\Components\DatePicker::make('from'),
                            Forms\Components\DatePicker::make('until'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['from'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('received_at', '>=', $date),
                                )
                                ->when(
                                    $data['until'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('received_at', '<=', $date),
                                );
                        }),

                    Tables\Filters\SelectFilter::make('category')
                        ->options([
                            'Loan Interest' => 'Loan Interest',
                            'Project' => 'Project',
                            'Registration' => 'Registration',
                            'Fines' => 'Fines',
                            'Other' => 'Other',
                        ]),
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
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}
