<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIncome extends CreateRecord
{
    use HasIconizedFormActions;
    protected static string $resource = IncomeResource::class;
}
