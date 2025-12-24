<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Resources\Pages\CreateRecord;

class CreateLoan extends CreateRecord
{
    use HasIconizedFormActions;
    protected static string $resource = LoanResource::class;
    protected function getFormValidationAttributes(): array
    {
        return [
            'member_id' => 'member',
            'amount' => 'loan amount',
            'paid_at' => 'payment date',
        ];
    }

    protected function getFormValidationRules(): array
    {
        return [
            'member_id' => ['required'],
            'amount' => ['required', 'numeric', 'min:100'],
            'paid_at' => ['required', 'date'],
        ];
    }
}
