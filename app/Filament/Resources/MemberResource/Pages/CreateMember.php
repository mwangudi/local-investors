<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\HasIconizedFormActions;


class CreateMember extends CreateRecord
{
    use HasIconizedFormActions;
    protected static string $resource = MemberResource::class;
}
