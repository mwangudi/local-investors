<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use App\Filament\Traits\HasIconizedFormActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;
use App\Models\Loan;
use Filament\Notifications\Notification;

class LoanRepaymentRelationManager extends RelationManager
{
    use HasIconizedFormActions;
    protected static string $relationship = 'repayments';
    protected static ?string $title = 'Loan Repayments';

    /**
     * Disable Save/Delete buttons when editing a repayment
     */
    protected function getFormActions(): array
    {
        if ($this->ownerRecord->repaid) {
            return []; // loan fully repaid → no save/update/delete
        }

        return parent::getFormActions();
    }

    protected function updateLoanState($loan)
    {
        // Calculate full amount due including interest
        $principal = $loan->amount;
        $standardInterest = ($loan->interest_percent / 100) * $principal;
        $totalDue = $principal + $standardInterest;

        // Calculate total repaid so far
        $totalRepaid = $loan->repayments()->sum('amount');

        // Check if fully repaid
        $isRepaid = $totalRepaid >= $totalDue;

        // Update loan record
        $loan->repaid_amount = $totalRepaid;
        $loan->repaid = $isRepaid;
        $loan->save();

        if ($isRepaid) {
            // Logic: Create Income record for the interest if it doesn't exist
            // But only if we have collected enough to cover principal + interest
            // Actually, we define Income = Total Repaid - Principal.
            // If there's an overdue penalty, it's also income.
            // Simplified logic based on requirements: "Interests on loans when repaid as income"

            // Let's assume Income = Repaid Amount - Principal.
            // If Repaid > Principal, the difference is Income.

            $totalIncome = $totalRepaid - $principal;

            if ($totalIncome > 0) {
                // Determine if there is a penalty (i.e. Income > Standard Interest)
                $penaltyAmount = $totalIncome - $standardInterest;

                // Ensure no negative values due to floating point
                if ($penaltyAmount < 0)
                    $penaltyAmount = 0;

                // 1. Create/Update Standard Interest Income
                // We cap the interest income at the standard interest amount if there is a penalty,
                // otherwise it's just the total income.
                $interestIncomeAmount = ($penaltyAmount > 0) ? $standardInterest : $totalIncome;

                $loan->incomes()->updateOrCreate(
                    [
                        'loan_id' => $loan->id,
                        'category' => 'Loan Interest'
                    ],
                    [
                        'amount' => $interestIncomeAmount,
                        'category' => 'Loan Interest',
                        'received_at' => now(),
                        'description' => "Interest income from Loan #{$loan->id} (Member: {$loan->member->full_name})",
                        'member_id' => $loan->member_id,
                    ]
                );

                // 2. Create/Update Penalty Income if applicable
                if ($penaltyAmount > 1) { // Threshold > 1 KES to avoid tiny fractions
                    $loan->incomes()->updateOrCreate(
                        [
                            'loan_id' => $loan->id,
                            'category' => 'Fines',
                            'fine_type' => 'Late loan repayment'
                        ],
                        [
                            'amount' => $penaltyAmount,
                            'category' => 'Fines',
                            'fine_type' => 'Late loan repayment',
                            'received_at' => now(),
                            'description' => "Late repayment fine from Loan #{$loan->id}",
                            'member_id' => $loan->member_id,
                        ]
                    );
                }
                // Note: If previously there was a fine but now it's reduced (e.g. correction), updateOrCreate handles update.
                // But if penalty goes to 0, we might want to delete the fine record?
                // For simplicity, we leave it or maybe explicitly delete if penaltyAmount <= 0.
                if ($penaltyAmount <= 1) {
                    $loan->incomes()->where('category', 'Fines')->where('fine_type', 'Late loan repayment')->delete();
                }
            }
        } else {
            // If loan becomes unpaid (e.g. repayment deleted), remove ALL income records linked to this loan
            $loan->incomes()->delete();
        }
    }

    /**
     * Repayment form
     */
    public function form(Form $form): Form
    {
        return $form->schema([

            TextInput::make('amount')
                ->label('Amount Paid')
                ->numeric()
                ->minValue(1)
                ->required()
                ->placeholder('Enter Amount Paid')
                ->prefix('KES')
                ->columnSpan(4)
                ->disabled(fn() => $this->ownerRecord?->repaid),

            DatePicker::make('paid_at')
                ->label('Paid At')
                ->required()
                ->columnSpan(4)
                ->disabled(fn() => $this->ownerRecord?->repaid),

            Select::make('method')
                ->label('Payment Method')
                ->placeholder('Select Payment Method')
                ->options([
                    'cash' => 'Cash',
                    'mpesa' => 'M-Pesa',
                    'bank' => 'Bank Transfer',
                ])
                ->searchable()
                ->columnSpan(4)
                ->disabled(fn() => $this->ownerRecord?->repaid),

            Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull()
                ->disabled(fn() => $this->ownerRecord?->repaid),

        ])->columns(12);
    }


    /**
     * Repayments table
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('kes')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->date()
                    ->sortable(),

                TextColumn::make('method')
                    ->label('Method')
                    ->formatStateUsing(fn($state) => ucwords($state)),
            ])

            /**
             * Hide Create button if loan is fully paid
             */
            ->headerActions([
                \Filament\Tables\Actions\Action::make('top_up')
                    ->label('Top Up (Add Cash)')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalWidth('md')
                    ->hidden(fn(RelationManager $livewire) => !auth()->user()?->hasRole('admin') || $livewire->ownerRecord->repaid || $livewire->ownerRecord->status !== Loan::STATUS_DISBURSED)
                    ->form([
                        \Filament\Forms\Components\Section::make('Loan Status')
                            ->schema([
                                TextInput::make('current_balance')
                                    ->label('Current Balance')
                                    ->prefix('KES')
                                    ->numeric()
                                    ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->balance)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        TextInput::make('amount')
                            ->label('Top Up Amount (Cash/Mpesa to Member)')
                            ->helperText('How much extra cash is the member borrowing?')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(1)
                            ->prefix('KES')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, RelationManager $livewire) {
                                $balance = $livewire->ownerRecord->balance;
                                $topUp = (float) $state;
                                $newPrincipal = $balance + $topUp;
                                $set('new_principal_preview', number_format($newPrincipal, 2));
                                $set('new_principal', $newPrincipal);
                            }),

                        Select::make('method')
                            ->label('Disbursement Method (For Top Up)')
                            ->options([
                                'cash' => 'Cash',
                                'mpesa' => 'M-Pesa',
                                'bank' => 'Bank Transfer',
                            ])
                            ->default('mpesa')
                            ->required(),

                        TextInput::make('new_principal_preview')
                            ->label('New Loan Principal')
                            ->helperText('Current Balance + Top Up Amount')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('KES')
                            ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->balance),

                        \Filament\Forms\Components\Hidden::make('new_principal')
                            ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->balance),

                        TextInput::make('new_term')
                            ->label('New Loan Term (Months)')
                            ->default(2)
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $oldLoan = $livewire->ownerRecord;

                        if ($oldLoan->repaid) {
                            Notification::make()->title('Loan is already repaid')->danger()->send();
                            return;
                        }

                        $topUpAmount = (float) $data['amount'];
                        $totalBalance = $oldLoan->balance;

                        // New Principal = Old Balance + Top Up
                        $newPrincipal = $totalBalance + $topUpAmount;

                        DB::transaction(function () use ($data, $livewire, $oldLoan, $topUpAmount, $newPrincipal, $totalBalance) {

                            // 1. Close Old Loan via Virtual Repayment (Refinance)
                            // We pay off the entire balance to close it.
                            $livewire->getRelationship()->create([
                                'amount' => $totalBalance,
                                'paid_at' => now(),
                                'method' => 'refinance',
                                'notes' => 'Cleared via Loan Top Up (Refinance)',
                                'loan_id' => $oldLoan->id,
                            ]);

                            // 2. Update Old Loan State
                            $livewire->updateLoanState($oldLoan);

                            // 3. Create New Loan
                            $newLoan = Loan::create([
                                'member_id' => $oldLoan->member_id,
                                'amount' => $newPrincipal,
                                'interest_percent' => $oldLoan->interest_percent,
                                'term_months' => $data['new_term'],
                                'disbursed_at' => now(),
                                'due_at' => now()->addMonths((int) $data['new_term']),
                                'status' => Loan::STATUS_DISBURSED,
                            ]);
                        });

                        Notification::make()
                            ->title('Loan Topped Up Successfully')
                            ->body("Disburse KES " . number_format($topUpAmount) . " to member. New Loan: KES " . number_format($newPrincipal))
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                \Filament\Tables\Actions\Action::make('rollover')
                    ->label('Rollover (Refinance)')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->modalWidth('md')
                    ->hidden(fn(RelationManager $livewire) => !auth()->user()?->hasRole('admin') || $livewire->ownerRecord->repaid || $livewire->ownerRecord->status !== Loan::STATUS_DISBURSED)
                    ->form([
                        \Filament\Forms\Components\Section::make('Loan Status')
                            ->schema([
                                TextInput::make('current_balance')
                                    ->label('Total Balance Due')
                                    ->prefix('KES')
                                    ->numeric()
                                    ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->balance)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        TextInput::make('amount')
                            ->label('Payment Amount (Cash/Mpesa)')
                            ->helperText('Enter the amount being paid now. The remaining balance will be the new loan principal.')
                            ->required()
                            ->numeric()
                            // Default to interest only, but allow editing
                            ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->balance - $livewire->ownerRecord->amount)
                            ->prefix('KES')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, RelationManager $livewire) {
                                $balance = $livewire->ownerRecord->balance;
                                $payment = (float) $state;
                                $newPrincipal = max(0, $balance - $payment);
                                $set('new_principal_preview', number_format($newPrincipal, 2));
                                $set('new_principal', $newPrincipal);
                            }),

                        Select::make('method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'mpesa' => 'M-Pesa',
                                'bank' => 'Bank Transfer',
                            ])
                            ->default('mpesa')
                            ->required(),

                        TextInput::make('new_principal_preview')
                            ->label('New Loan Principal')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('KES')
                            ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->amount), // Default assuming interest only paid

                        \Filament\Forms\Components\Hidden::make('new_principal')
                            ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->amount),

                        TextInput::make('new_term')
                            ->label('New Loan Term (Months)')
                            ->default(2)
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $oldLoan = $livewire->ownerRecord;

                        if ($oldLoan->repaid) {
                            Notification::make()->title('Loan is already repaid')->danger()->send();
                            return;
                        }

                        $paymentAmount = (float) $data['amount'];
                        $totalBalance = $oldLoan->balance;

                        // Calculate rollover amount (New Principal)
                        // If user pays X, New Principal = Total Due - X
                        // This effectively capitalizes the unpaid interest into the new principal.
                        $newPrincipal = $totalBalance - $paymentAmount;

                        if ($newPrincipal <= 0) {
                            Notification::make()->title('Payment covers entire loan. Use standard repayment instead.')->warning()->send();
                            return;
                        }

                        DB::transaction(function () use ($data, $livewire, $oldLoan, $paymentAmount, $newPrincipal) {
                            // 1. Record Cash Repayment
                            if ($paymentAmount > 0) {
                                $livewire->getRelationship()->create([
                                    'amount' => $paymentAmount,
                                    'paid_at' => now(),
                                    'method' => $data['method'],
                                    'notes' => 'Rollover Partial Payment',
                                    'loan_id' => $oldLoan->id,
                                ]);
                            }

                            // 2. Record Virtual Rollover Repayment to clear the OLD loan
                            // The virtual amount must equal the REMAINING balance to act as the "transfer"
                            // Remaining Balance = Total Balance - Cash Payment = New Principal
                            $livewire->getRelationship()->create([
                                'amount' => $newPrincipal,
                                'paid_at' => now(),
                                'method' => 'rollover',
                                'notes' => 'Rollover Balance Transfer',
                                'loan_id' => $oldLoan->id,
                            ]);

                            // 3. Update Old Loan State (Triggers Income Logic & Repaid Mark)
                            $livewire->updateLoanState($oldLoan);

                            // 4. Create New Loan
                            Loan::create([
                                'member_id' => $oldLoan->member_id,
                                'amount' => $newPrincipal,
                                'interest_percent' => $oldLoan->interest_percent,
                                'term_months' => $data['new_term'],
                                'disbursed_at' => now(),
                                'due_at' => now()->addMonths((int) $data['new_term']),
                                'status' => Loan::STATUS_DISBURSED,
                            ]);
                        });

                        Notification::make()
                            ->title('Loan Refinanced Successfully')
                            ->body("Paid KES " . number_format($paymentAmount) . ". New Loan: KES " . number_format($newPrincipal))
                            ->success()
                            ->send();
                    }),

                CreateAction::make()
                    ->hidden(fn(RelationManager $livewire) => !auth()->user()?->hasRole('admin') || $livewire->ownerRecord->repaid)

                    // Apply foreign key & create the record
                    ->using(function (array $data, RelationManager $livewire) {
                        $data['loan_id'] = $livewire->ownerRecord->id;

                        // Create via relationship
                        $record = $livewire->getRelationship()->create($data);

                        return $record;
                    })

                    // Run logic *after* creation
                    ->after(function ($record, RelationManager $livewire) {
                        // Update loan state after repayment creation
                        $livewire->updateLoanState($livewire->ownerRecord);
                    }),
            ])


            /**
             * Disable edit/delete on already fully repaid loan
             */
            ->actions([
                EditAction::make()
                    ->hidden(fn() => !auth()->user()?->hasRole('admin') || $this->ownerRecord->repaid)
                    ->after(function ($record) {
                        $this->updateLoanState($record->loan);
                    }),

                DeleteAction::make()
                    ->hidden(fn() => !auth()->user()?->hasRole('admin') || $this->ownerRecord->repaid)
                    ->after(function ($record) {
                        $this->updateLoanState($record->loan);
                    }),
            ]);
    }
}
