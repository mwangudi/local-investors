<?php

namespace App\Filament\Member\Resources;

use App\Filament\Member\Resources\ContributionResource\Pages;
use App\Filament\Member\Resources\ContributionResource\RelationManagers;
use App\Models\Contribution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContributionResource extends Resource
{
    protected static ?string $model = Contribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('member_id', auth()->user()->member_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->readOnly(),
                Forms\Components\DatePicker::make('received_at')
                    ->readOnly(),
                Forms\Components\TextInput::make('description')
                    ->readOnly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('member_id', auth()->user()->member_id))
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money('kes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContributions::route('/'),
            // 'create' => Pages\CreateContribution::route('/create'),
            // 'edit' => Pages\EditContribution::route('/{record}/edit'),
        ];
    }
}
