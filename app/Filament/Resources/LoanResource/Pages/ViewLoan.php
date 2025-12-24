<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Loan;
use Filament\Notifications\Notification;
use App\Models\ChamaSetting;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Loan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn(Loan $record) => in_array($record->status, [Loan::STATUS_APPLIED, Loan::STATUS_APPROVED])
                    && auth()->user()->member_id
                    && auth()->user()->member_id !== $record->member_id
                    && !$record->approvals()->where('member_id', auth()->user()->member_id)->exists())
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('remark')
                        ->label('Remarks (Optional)'),
                ])
                ->action(function (Loan $record, array $data) {
                    $record->approvals()->create([
                        'member_id' => auth()->user()->member_id,
                        'remark' => $data['remark'] ?? null,
                    ]);

                    $minApprovals = ChamaSetting::current()->min_loan_approvals ?? 3;

                    if ($record->approvals()->count() >= $minApprovals) {
                        $record->update(['status' => Loan::STATUS_APPROVED]);
                    }

                    Notification::make()
                        ->title('Loan Approved Successfully')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),

            Actions\Action::make('disburse')
                ->label('Disburse Loan')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->visible(fn(Loan $record) => $record->status === Loan::STATUS_APPROVED && auth()->user()->hasRole('admin'))
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\DatePicker::make('disbursed_at')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (Loan $record, array $data) {
                    $disbursedAt = \Carbon\Carbon::parse($data['disbursed_at']);
                    $record->update([
                        'status' => Loan::STATUS_DISBURSED,
                        'disbursed_at' => $disbursedAt,
                        'due_at' => $disbursedAt->copy()->addMonths($record->term_months),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Loan Disbursed')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}
