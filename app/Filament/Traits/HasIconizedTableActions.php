<?php

namespace App\Filament\Traits;

use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

trait HasIconizedTableActions
{
    /**
     * Row actions with icons.
     */
    protected function getDefaultTableActions(): array
    {
        return [
            ActionGroup::make([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('View Record')
                    ->color('info')
                    ->tooltip('View Record')
                    ->extraAttributes([
                        'class' => 'text-lg',
                    ]),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit Record')
                    ->color('primary')
                    ->tooltip('Edit Record')
                    ->extraAttributes([
                        'class' => 'text-lg',
                    ]),

                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->label('Delete Record')
                    ->color('danger')
                    ->tooltip('Delete Record')
                    ->extraAttributes([
                        'class' => 'text-lg',
                    ]),
            ])
            ->label('Actions')
            ->icon('heroicon-m-ellipsis-vertical')
            ->color('primary')
            ->button()
        ];
    }

    /**
     * Bulk actions with icons.
     */
    protected function getDefaultBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->label('Delete Selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),

                RestoreBulkAction::make()
                    ->label('Restore Selected')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success'),

                ForceDeleteBulkAction::make()
                    ->label('Force Delete')
                    ->icon('heroicon-o-fire')
                    ->color('danger'),
            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->color('danger'),
        ];
    }

    /**
     * Merge trait actions into the resource table.
     */
    protected function applyIconizedTableActions(Table $table): Table
    {
        return $table
            ->actions($this->getDefaultTableActions())    // Row actions
            ->bulkActions($this->getDefaultBulkActions()); // Bulk actions (Filament 4)
    }
}