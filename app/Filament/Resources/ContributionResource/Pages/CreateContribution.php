<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Filament\Resources\ContributionResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Resources\Pages\CreateRecord;

class CreateContribution extends CreateRecord
{
    use HasIconizedFormActions;
    protected static string $resource = ContributionResource::class;
}
