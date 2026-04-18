<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;

class ExpendituresRelationManager extends RelationManager
{
    protected static string $relationship = 'expenditures';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('KES'),
                Forms\Components\DatePicker::make('spent_at')
                    ->required(),
                Forms\Components\Select::make('category')
                    ->options([
                        'Meeting' => 'Meeting',
                        'Transport' => 'Transport',
                        'Food' => 'Food',
                        'Other' => 'Other',
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('kes')
                    ->summarize(Sum::make()->money('kes')),
                Tables\Columns\TextColumn::make('spent_at')
                    ->date(),
                Tables\Columns\TextColumn::make('category'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
