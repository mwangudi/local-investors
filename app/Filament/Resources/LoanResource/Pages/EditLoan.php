<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditLoan extends EditRecord
{
    use HasIconizedFormActions;
    protected static string $resource = LoanResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->repaid ? 'View Loan' : 'Edit Loan';
    }

    public function getBreadcrumb(): string
    {
        return $this->getRecord()->repaid ? 'View Loan' : 'Edit Loan';
    }

    public function getHeading(): string
    {
        return $this->getRecord()->repaid ? 'View Loan' : 'Edit Loan';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Back to Loans')
                ->icon('heroicon-o-arrow-left')
                ->color('primary')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
