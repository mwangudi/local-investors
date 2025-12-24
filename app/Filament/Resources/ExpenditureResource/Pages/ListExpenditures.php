<?php

namespace App\Filament\Resources\ExpenditureResource\Pages;

use App\Filament\Resources\ExpenditureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Traits\HasIconizedTableActions;
use App\Filament\Traits\HasIconizedFormActions;

class ListExpenditures extends ListRecords
{
    use HasIconizedTableActions, HasIconizedFormActions;
    protected static string $resource = ExpenditureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Expenditure')
                ->icon('heroicon-o-plus'),
        ];
    }
}
