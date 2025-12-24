<?php

namespace App\Filament\Traits;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions;

trait HasIconizedFormActions
{
    /**
     * Handles form actions differently for Create vs Edit pages.
     */
    protected function getFormActions(): array
    {
        // Read-only mode (e.g., repaid loan)
        if (method_exists($this, 'isReadOnly') && $this->isReadOnly()) {
            return [];
        }

        $isEdit = $this->record && $this->record->exists;

        if ($isEdit) {
            /**
             * EDIT PAGE ACTIONS
             */
            return [
                Action::make('save')
                    ->label('Save Changes')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->submit('save'),

                Action::make('cancel')
                    ->label('Back')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->url($this->getResource()::getUrl('index')),
            ];
        }

        /**
         * CREATE PAGE ACTIONS
         */
        return [
            Action::make('save')
                ->label('Save')
                ->color('primary')
                ->icon('heroicon-o-check-circle')
                ->submit('save'),

            Action::make('save_and_create_another')
                ->label('Save & Create Another')
                ->icon('heroicon-o-plus-circle')
                ->submit('saveAndCreateAnother')
                ->color('success'),

            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    /**
     * Delete button (only on Edit pages, hidden on Create & Read-Only).
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->hidden(function ($record) {
                    // Hide on create pages
                    if (!$record || !$record->exists) {
                        return true;
                    }

                    // Hide on read-only pages
                    if (method_exists($this, 'isReadOnly') && $this->isReadOnly()) {
                        return true;
                    }

                    // Hide if loan is repaid (only if model has 'repaid' attribute)
                    if (isset($this->record->repaid) && $this->record->repaid) {
                        return true;
                    }

                    return false;
                }),
        ];
    }
}