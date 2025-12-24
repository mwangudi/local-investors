<?php

namespace App\Filament\Resources\MemberResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Member')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
