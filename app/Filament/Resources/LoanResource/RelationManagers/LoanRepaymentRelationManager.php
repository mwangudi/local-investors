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
                CreateAction::make()
                    ->hidden(fn(RelationManager $livewire) => $livewire->ownerRecord->repaid)

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
                    ->hidden(fn() => $this->ownerRecord->repaid)
                    ->after(function ($record) {
                        $this->updateLoanState($record->loan);
                    }),

                DeleteAction::make()
                    ->hidden(fn() => $this->ownerRecord->repaid)
                    ->after(function ($record) {
                        $this->updateLoanState($record->loan);
                    }),
            ]);
    }
}
