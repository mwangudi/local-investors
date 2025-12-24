<?php

namespace App\Filament\Resources;

use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\MemberResource\RelationManagers\ContributionRelationManager;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use App\Filament\Resources\ContributionResource\Pages\ListContributions;
use App\Filament\Resources\ContributionResource\Pages\CreateContribution;
use App\Filament\Resources\ContributionResource\Pages\EditContribution;
use App\Filament\Traits\HasIconizedTableActions;
use App\Models\Contribution;
use App\Models\Member;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class ContributionResource extends Resource
{
    use HasIconizedTableActions;
    protected static ?string $model = Contribution::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Menus';
    protected static ?string $navigationLabel = 'Contributions';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['member']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Contribution Details')
                ->schema([

                    Select::make('member_id')
                        ->label('Member')
                        ->relationship('member', 'id')
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required()
                        ->columnSpan(['md' => 4])
                        ->visible(fn($livewire) => !$livewire instanceof ContributionRelationManager)
                        ->disabled(fn($livewire) => $livewire instanceof ContributionRelationManager),

                    TextInput::make('shares')
                        ->label('Shares (1000)')
                        ->numeric()
                        ->default(1000)
                        ->required()
                        ->prefix('KES')
                        ->columnSpan(['md' => 4]),

                    TextInput::make('welfare')
                        ->label('Welfare (500)')
                        ->numeric()
                        ->default(500)
                        ->required()
                        ->prefix('KES')
                        ->columnSpan(['md' => 4]),

                    TextInput::make('merry_go_round')
                        ->label('Merry-Go-Round (2000)')
                        ->numeric()
                        ->default(2000)
                        ->required()
                        ->prefix('KES')
                        ->columnSpan(['md' => 4]),

                    TextInput::make('penalty')
                        ->label('Penalty')
                        ->numeric()
                        ->default(0)
                        ->live()
                        ->prefix('KES')
                        ->columnSpan(['md' => 4]),

                    Select::make('penalty_type')
                        ->label('Penalty Type')
                        ->searchable()
                        ->options([
                            'late_payment' => 'Late Payment',
                            'late_shares' => 'Late Shares',
                            'loan_default' => 'Loan Default',
                            'meeting_absence' => 'Meeting Absence',
                            'other' => 'Other',
                        ])
                        ->visible(fn(callable $get) => $get('penalty') > 0)
                        ->required(fn(callable $get) => $get('penalty') > 0)
                        ->columnSpan(['md' => 4]),

                    Select::make('type')
                        ->label('Contribution Type')
                        ->searchable()
                        ->options([
                            'monthly' => 'Monthly Contribution (3500)',
                            'welfare' => 'Welfare Only',
                            'shares' => 'Shares Only',
                            'mgr' => 'Merry-Go-Round',
                            'penalty' => 'Penalty',
                            'other' => 'Other',
                        ])
                        ->default('monthly')
                        ->required()
                        ->columnSpan(['md' => 4]),

                    DatePicker::make('paid_at')
                        ->label('Paid At')
                        ->required()
                        ->live(onBlur: true)
                        ->disabled(fn($livewire) => $livewire instanceof EditRecord)
                        ->rule(function (callable $get, $livewire) {
                            return function ($attribute, $value, $fail) use ($get, $livewire) {

                                if (!$value) {
                                    return;
                                }

                                // Determine member ID
                                $memberId = $livewire instanceof ContributionRelationManager
                                    ? $livewire->ownerRecord->id
                                    : $get('member_id');

                                if (!$memberId) {
                                    return;
                                }

                                $date = Carbon::parse($value);
                                $memberName = Member::find($memberId)?->full_name ?? 'this member';
                                $currentRecordId = $get('id'); // works for edit
                
                                // Check if contribution already exists in same month/year
                                $exists = Contribution::where('member_id', $memberId)
                                    ->whereMonth('paid_at', $date->month)
                                    ->whereYear('paid_at', $date->year)
                                    ->when($currentRecordId, fn($q) => $q->where('id', '!=', $currentRecordId))
                                    ->exists();

                                if ($exists) {
                                    $fail("A contribution already exists for $memberName for {$date->format('F Y')}.");
                                }
                            };
                        })
                        ->columnSpan(['md' => 4]),

                    Textarea::make('notes')
                        ->rows(3)
                        ->placeholder('Additional notes (optional)')
                        ->columnSpanFull(),

                ])->columns(12),
        ]);
    }


    public static function table(Table $table): Table
    {
        $self = new self;
        return $self->applyIconizedTableActions(
            $table
                ->striped()
                ->columns([
                    TextColumn::make('member.full_name')
                        ->label('Member')
                        ->sortable()
                        ->searchable(query: function (Builder $query, string $search) {
                            return $query->whereHas('member', function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            });
                        }),

                    TextColumn::make('shares')
                        ->money('kes')
                        ->summarize(Sum::make()->money('kes'))
                        ->label('Shares'),

                    TextColumn::make('welfare')
                        ->money('kes')
                        ->summarize(Sum::make()->money('kes'))
                        ->label('Welfare'),

                    TextColumn::make('merry_go_round')
                        ->money('kes')
                        ->summarize(Sum::make()->money('kes'))
                        ->label('MGR'),

                    TextColumn::make('penalty')
                        ->money('kes')
                        ->summarize(Sum::make()->money('kes'))
                        ->label('Penalty'),

                    BadgeColumn::make('penalty_type')
                        ->label('Penalty Type')
                        ->colors([
                            'danger' => 'loan_default',
                            'warning' => 'late_payment',
                            'info' => 'late_shares',
                            'gray' => 'meeting_absence',
                            'primary' => 'other',
                        ])
                        ->sortable(),

                    BadgeColumn::make('type')
                        ->label('Type')
                        ->colors([
                            'primary' => 'monthly',
                            'success' => 'shares',
                            'info' => 'welfare',
                            'warning' => 'mgr',
                            'danger' => 'penalty',
                            'gray' => 'other',
                        ])
                        ->formatStateUsing(fn($state) => ucfirst($state))
                        ->sortable(),

                    TextColumn::make('paid_at')
                        ->dateTime('Y-m-d')
                        ->sortable(),

                ])
                ->defaultSort('id', 'desc')
                ->paginated()
                ->filters([
                    SelectFilter::make('member')
                        ->label('Member')
                        ->relationship('member', 'id')
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload(),
                    Filter::make('paid_at')
                        ->form([
                            DatePicker::make('paid_from'),
                            DatePicker::make('paid_until'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['paid_from'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('paid_at', '>=', $date),
                                )
                                ->when(
                                    $data['paid_until'],
                                    fn(Builder $query, $date): Builder => $query->whereDate('paid_at', '<=', $date),
                                );
                        }),
                ])
                ->actions([
                    ViewAction::make(),
                    EditAction::make(),
                ])
                ->bulkActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ])
                ])
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContributions::route('/'),
            'create' => CreateContribution::route('/create'),
            'edit' => EditContribution::route('/{record}/edit'),
        ];
    }
}