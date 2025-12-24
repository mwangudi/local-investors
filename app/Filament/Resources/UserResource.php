<?php

namespace App\Filament\Resources;

use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Traits\HasIconizedTableActions;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;

class UserResource extends Resource
{
    use HasIconizedTableActions;
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'System Administration';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    // Only Admin can access User Management
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->placeholder('Enter Name')
                        ->maxLength(255)
                        ->columnSpan(['md' => 4]),

                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->placeholder('Enter Email Address')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->columnSpan(['md' => 4]),

                    DateTimePicker::make('email_verified_at')
                        ->label('Email Verified')
                        ->seconds(false)
                        ->columnSpan(['md' => 4]),

                    TextInput::make('password')
                        ->password()
                        ->label('Password')
                        ->placeholder('Enter Password')
                        ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn($context) => $context === 'create')
                        ->helperText('Password is required and should be at least 6 characters long.')
                        ->columnSpan(['md' => 4]),

                    // Assign roles
                    Select::make('roles')
                        ->relationship('roles', 'name')
                        ->preload()
                        ->searchable()
                        ->label('User Roles')
                        ->helperText('You can assign only one role to the user at a time.')
                        ->columnSpan(['md' => 4]),

                    Select::make('member_id')
                        ->label('Linked Member')
                        ->relationship('member', 'id')
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->helperText('Link this user account to a specific member.')
                        ->columnSpan(['md' => 4]),
                ])
                ->columns(12),

        ]);
    }

    public static function table(Table $table): Table
    {
        $self = new self;
        return $self->applyIconizedTableActions(
            $table
                ->striped()
                ->columns([

                    TextColumn::make('name')
                        ->searchable()
                        ->sortable(),

                    TextColumn::make('email')
                        ->searchable(),

                    TextColumn::make('roles.name')
                        ->label('Role')
                        ->badge()
                        ->separator(', ')
                        ->sortable(),

                    TextColumn::make('email_verified_at')
                        ->label('Verified At')
                        ->dateTime('Y-m-d H:i')
                        ->sortable(),

                    TextColumn::make('created_at')
                        ->label('Created At')
                        ->dateTime('Y-m-d H:i')
                        ->sortable(),
                ])
                ->filters([

                    // Filter by Role
                    SelectFilter::make('role')
                        ->label('Filter by Role')
                        ->options(Role::all()->pluck('name', 'name'))
                        ->query(function ($query, array $data) {
                            if (!filled($data['value'] ?? null)) {
                                return;
                            }

                            $query->whereHas(
                                'roles',
                                fn($q) =>
                                $q->where('name', $data['value'])
                            );
                        }),

                    // Filter by Verified / Unverified
                    SelectFilter::make('verified')
                        ->label('Email Verified')
                        ->options([
                            'yes' => 'Verified',
                            'no' => 'Not Verified',
                        ])
                        ->query(function ($query, array $data) {
                            $value = $data['value'] ?? null;

                            if ($value === 'yes') {
                                $query->whereNotNull('email_verified_at');
                            } elseif ($value === 'no') {
                                $query->whereNull('email_verified_at');
                            }
                        }),

                ])
                ->actions([
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}