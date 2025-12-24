<?php

namespace App\Filament\Resources\CashReturnResource\Pages;

use App\Filament\Resources\CashReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashReturns extends ListRecords
{
    protected static string $resource = CashReturnResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Cash Return')
                ->icon('heroicon-o-plus'),
        ];
    }
}