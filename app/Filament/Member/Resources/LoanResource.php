<?php

namespace App\Filament\Member\Resources;

use App\Filament\Member\Resources\LoanResource\Pages;
use App\Filament\Member\Resources\LoanResource\RelationManagers;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar'; // Changed icon
    protected static ?string $navigationLabel = 'My Loans'; // Changed label
    protected static ?string $pluralLabel = 'My Loans';

    public static function getEloquentQuery(): Builder
    {
        // Scope to current member
        return parent::getEloquentQuery()->where('member_id', auth()->user()->member_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only details for the member view (if they click view)
                Forms\Components\TextInput::make('amount')
                    ->prefix('KES')
                    ->readOnly(),
                Forms\Components\TextInput::make('balance')
                    ->prefix('KES')
                    ->readOnly(),
                Forms\Components\TextInput::make('status')
                    ->readOnly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // DEBUG: Temporary check to see what's happening
        // Log::info('Current User Member ID: ' . auth()->user()->member_id);

        return $table
            // Ensure query modification is respected
            ->modifyQueryUsing(fn(Builder $query) => $query->where('member_id', auth()->user()->member_id))
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Loan ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Principal')
                    ->money('kes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Current Balance')
                    ->money('kes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Loan::STATUS_DISBURSED => 'success',
                        Loan::STATUS_APPLIED => 'info',
                        Loan::STATUS_APPROVED => 'warning',
                        Loan::STATUS_REPAID => 'gray',
                        default => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('request_topup')
                    ->label('Request Topup')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->hidden(fn(Loan $record) => $record->status !== Loan::STATUS_DISBURSED || $record->repaid)
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('info')
                                    ->label('Topup Information')
                                    ->content('A topup will pay off your current balance and create a new loan with the additional amount.'),

                                Forms\Components\TextInput::make('current_balance')
                                    ->label('Current Balance')
                                    ->disabled()
                                    ->default(fn(Loan $record) => number_format($record->balance, 2))
                                    ->prefix('KES'),

                                Forms\Components\TextInput::make('topup_amount')
                                    ->label('Additional Cash Needed')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1000)
                                    ->prefix('KES')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set, Loan $record) {
                                        $balance = $record->balance;
                                        $topup = (float) $state;
                                        $set('new_principal', number_format($balance + $topup, 2));
                                    }),

                                Forms\Components\TextInput::make('new_term')
                                    ->label('New Term (Months)')
                                    ->numeric()
                                    ->required()
                                    ->default(fn(Loan $record) => $record->term_months)
                                    ->minValue(1),

                                Forms\Components\TextInput::make('new_principal')
                                    ->label('Estimated New Principal')
                                    ->disabled()
                                    ->prefix('KES'),

                                Forms\Components\Select::make('disbursement_method')
                                    ->label('Send Funds To')
                                    ->options([
                                        'mpesa' => 'M-Pesa',
                                        'bank' => 'Bank Account',
                                    ])
                                    ->required()
                                    ->default('mpesa'),

                                Forms\Components\Textarea::make('reason')
                                    ->label('Reason for Topup')
                                    ->rows(2),
                            ])
                    ])
                    ->action(function (array $data, Loan $record) {
                        // Ensure member can only topup their own loan
                        abort_unless($record->member_id === auth()->user()->member_id, 403, 'Unauthorized action.');

                        $topupAmount = (float) $data['topup_amount'];
                        $newPrincipal = $record->balance + $topupAmount;

                        // Create a new Loan Application (Status: Applied)
                        // Notes will indicate it's a Topup request linked to the old loan
                        $newLoan = Loan::create([
                            'member_id' => $record->member_id,
                            'amount' => $newPrincipal, // They want to owe this much total
                            // Inherit interest from old loan or system default? Inherit for now.
                            'interest_percent' => $record->interest_percent,
                            'term_months' => $data['new_term'],
                            'status' => Loan::STATUS_APPLIED,
                            // We don't set disbursed_at or due_at yet.
                        ]);

                        // We can't easily link "Old Loan" ID in the "Loan" model unless we add a column.
                        // For now, I'll assume Admins check recent applications. 
                        // Or I can add a note if there was a notes table, but Loan doesn't seem to have one.
                        // I'll assume the admin operational process handles "Oh this member has an active loan, this must be a topup".
                        // Wait, user asked: "request for loan topups as they can be topped up by admin".
                        // Admin needs to know it's a topup to perform the "Rollover" logic.
                        // If I simply create a new loan, Admin might just disburse it as a SECOND loan.
                        // I should probably add a notification to the admin or some way to flag it.
            
                        \Filament\Notifications\Notification::make()
                            ->title('Topup Requested Successfully')
                            ->body("Your request for a topup of KES " . number_format($topupAmount) . " has been submitted.")
                            ->success()
                            ->send();

                        // Notify Admins (Optional, better to persist)
                        \Filament\Notifications\Notification::make()
                            ->title('New Topup Request')
                            ->body("Member " . $record->member->full_name . " requested topup on Loan #" . $record->id)
                            ->warning()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->url('/admin/loans/' . $newLoan->id), // Point to the new loan application
                            ])
                            ->sendToDatabase(\App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->get());
                    }),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListLoans::route('/'),
            // 'create' => Pages\CreateLoan::route('/create'), // Member cannot create loans directly
            // 'edit' => Pages\EditLoan::route('/{record}/edit'), // Member cannot edit loans
        ];
    }
}
