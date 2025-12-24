<?php

namespace App\Filament\Resources\ExpenditureResource\Pages;

use App\Filament\Resources\ExpenditureResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Resources\Pages\EditRecord;

class EditExpenditure extends EditRecord
{
    use HasIconizedFormActions;
    protected static string $resource = ExpenditureResource::class;
}
