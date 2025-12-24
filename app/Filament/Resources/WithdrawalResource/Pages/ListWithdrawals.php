<?php

namespace App\Filament\Resources\WithdrawalResource\Pages;

use App\Filament\Resources\WithdrawalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Traits\HasIconizedFormActions;
use App\Filament\Traits\HasIconizedTableActions;

class ListWithdrawals extends ListRecords
{
    use HasIconizedFormActions, HasIconizedTableActions;
    protected static string $resource = WithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Withdrawal')
                ->icon('heroicon-o-plus'),
        ];
    }
}
