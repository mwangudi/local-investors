<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ContributionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContributions extends ListRecords
{
    protected static string $resource = ContributionResource::class;

    use \Filament\Pages\Concerns\ExposesTableToWidgets;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Contribution')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ContributionResource\Widgets\ContributionStatsOverview::class,
        ];
    }
}
