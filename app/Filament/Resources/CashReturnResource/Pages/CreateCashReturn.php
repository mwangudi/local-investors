<?php

namespace App\Filament\Resources\CashReturnResource\Pages;

use App\Filament\Resources\CashReturnResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCashReturn extends CreateRecord
{
    use HasIconizedFormActions;
    protected static string $resource = CashReturnResource::class;
}
