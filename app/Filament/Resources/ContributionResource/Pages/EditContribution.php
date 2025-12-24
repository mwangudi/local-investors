<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContributionResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContribution extends EditRecord
{
    use HasIconizedFormActions;
    protected static string $resource = ContributionResource::class;
}
