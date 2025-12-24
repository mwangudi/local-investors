<?php

namespace App\Filament\Resources\ExpenditureResource\Pages;

use App\Filament\Resources\ExpenditureResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenditure extends CreateRecord
{
    use HasIconizedFormActions;
    protected static string $resource = ExpenditureResource::class;
}
