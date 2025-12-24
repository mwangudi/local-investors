<?php

namespace App\Filament\Resources\CashReturnResource\Pages;

use App\Filament\Resources\CashReturnResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashReturn extends EditRecord
{
    use HasIconizedFormActions;
    protected static string $resource = CashReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
