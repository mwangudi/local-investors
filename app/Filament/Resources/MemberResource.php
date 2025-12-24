<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use App\Filament\Resources\MemberResource\Pages\ListMembers;
use App\Filament\Resources\MemberResource\Pages\CreateMember;
use App\Filament\Resources\MemberResource\Pages\EditMember;
use App\Filament\Resources\MemberResource\RelationManagers\ContributionRelationManager;
use App\Filament\Resources\MemberResource\RelationManagers\LoansRelationManager;
use App\Filament\Traits\HasIconizedTableActions;
use App\Models\Member;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;

class MemberResource extends Resource
{
    use HasIconizedTableActions;
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Members';
    protected static ?string $navigationGroup = 'Menus';
    protected static ?string $pluralModelLabel = 'Members';
    protected static ?string $modelLabel = 'Member';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {

        return $form->schema([
            Section::make('Personal Details')
                ->schema([
                    TextInput::make('first_name')
                        ->required()
                        ->maxLength(150)
                        ->placeholder('Enter First Name')
                        ->columnSpan(['md' => 4]),

                    TextInput::make('last_name')
                        ->required()
                        ->maxLength(150)
                        ->placeholder('Enter Last Name')
                        ->columnSpan(['md' => 4]),

                    TextInput::make('email')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('Enter Email Address')
                        ->columnSpan(['md' => 4]),

                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(20)
                        ->placeholder('Enter Phone Number')
                        ->required()
                        ->columnSpan(['md' => 4]),

                    DatePicker::make('join_date')
                        ->required()
                        ->label('Join Date')
                        ->format('Y-m-d')
                        ->columnSpan(['md' => 4]),

                    Toggle::make('is_active')
                        ->label('Active Member')
                        ->default(true)
                        ->columnSpan(['md' => 4]),

                ])
                ->columns(12),

            Section::make('Financial Summary')
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('total_shares')
                        ->label('Total Shares')
                        ->content(fn($record) => $record ? 'KES ' . number_format($record->contributions()->sum('shares'), 2) : '-'),

                    \Filament\Forms\Components\Placeholder::make('total_welfare')
                        ->label('Total Welfare')
                        ->content(fn($record) => $record ? 'KES ' . number_format($record->contributions()->sum('welfare'), 2) : '-'),

                    \Filament\Forms\Components\Placeholder::make('total_penalty')
                        ->label('Total Penalty')
                        ->content(fn($record) => $record ? 'KES ' . number_format($record->contributions()->sum('penalty'), 2) : '-'),

                    \Filament\Forms\Components\Placeholder::make('total_loans')
                        ->label('Total Loans Taken')
                        ->content(fn($record) => $record ? 'KES ' . number_format($record->loans()->sum('amount'), 2) : '-'),
                ])
                ->columns(4)
                ->visible(fn($record) => $record !== null),

        ]);
    }

    public static function table(Table $table): Table
    {
        $self = new self;
        return $self->applyIconizedTableActions(
            $table
                ->defaultSort('id', 'desc')
                ->striped()
                ->columns([

                    TextColumn::make('first_name')
                        ->searchable()
                        ->sortable(),

                    TextColumn::make('last_name')
                        ->searchable()
                        ->sortable(),

                    TextColumn::make('email')
                        ->sortable()
                        ->searchable(),

                    TextColumn::make('phone')
                        ->sortable()
                        ->searchable(),

                    TextColumn::make('join_date')
                        ->date()
                        ->label('Date Joined')
                        ->dateTime('Y-m-d')
                        ->sortable(),

                    BadgeColumn::make('is_active')
                        ->label('Status')
                        ->colors([
                            'success' => fn($state) => $state === true,
                            'danger' => fn($state) => $state === false,
                        ])
                        ->formatStateUsing(fn($state) => $state ? 'Active' : 'Inactive'),

                    TextColumn::make('created_at')
                        ->label('Created')
                        ->dateTime()
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('updated_at')
                        ->label('Updated')
                        ->dateTime()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])

                ->filters([
                    SelectFilter::make('is_active')
                        ->label('Status')
                        ->options([
                            true => 'Active',
                            false => 'Inactive',
                        ]),
                ])

                ->actions([
                    ViewAction::make(),
                    EditAction::make(),
                ])

                ->bulkActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                        ForceDeleteBulkAction::make(),
                        RestoreBulkAction::make(),
                    ]),
                ])

        );
    }

    public static function getRelations(): array
    {
        return [
            ContributionRelationManager::class,
            LoansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}