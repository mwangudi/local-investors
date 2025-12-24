<?php

namespace App\Filament\Resources\WithdrawalResource\Pages;

use App\Filament\Resources\WithdrawalResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWithdrawal extends CreateRecord
{
    use HasIconizedFormActions;
    protected static string $resource = WithdrawalResource::class;
}
