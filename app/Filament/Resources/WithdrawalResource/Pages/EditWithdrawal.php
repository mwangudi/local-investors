<?php

namespace App\Filament\Resources\WithdrawalResource\Pages;

use App\Filament\Resources\WithdrawalResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWithdrawal extends EditRecord
{
    use HasIconizedFormActions;
    protected static string $resource = WithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
