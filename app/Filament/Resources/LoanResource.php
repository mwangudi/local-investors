<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\RelationManagers\LoansRelationManager;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use App\Filament\Resources\LoanResource\Pages\ListLoans;
use App\Filament\Resources\LoanResource\Pages\CreateLoan;
use App\Filament\Resources\LoanResource\Pages\ViewLoan;
use App\Filament\Resources\LoanResource\Pages\EditLoan;
use App\Filament\Resources\LoanResource\RelationManagers\LoanRepaymentRelationManager;
use App\Filament\Traits\HasIconizedTableActions;
use App\Models\Loan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Forms\Components\ViewField;
use App\Models\Member;

class LoanResource extends Resource
{
    use HasIconizedTableActions, HasIconizedFormActions;
    protected static ?string $model = Loan::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Menus';
    protected static ?string $navigationLabel = 'Loans';
    protected static ?int $navigationSort = 3;
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['member']);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Loan Information')
                ->schema([

                    Select::make('member_id')
                        ->label('Member')
                        ->relationship('member', 'id')
                        ->options(
                            Member::whereDoesntHave('loans', function ($q) {
                                $q->where('repaid', false);
                            })
                                ->get()
                                ->pluck('full_name', 'id')
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required()
                        ->default(fn() => auth()->user()->member_id)
                        ->visible(fn($livewire) => !$livewire instanceof LoansRelationManager)
                        ->disabled(function ($record, $livewire) {
                            // Case 1: inside relation manager → always disabled
                            if ($livewire instanceof LoansRelationManager) {
                                return true;
                            }

                            // Case 2: Member logged in -> locked to self
                            if (auth()->user()->member_id) {
                                return true;
                            }

                            // Case 3: loan exists AND is ongoing → disable
                            if ($record && !$record->repaid) {
                                return true;
                            }

                            return false;
                        })
                        ->dehydrated(),

                    TextInput::make('amount')
                        ->label('Loan Amount')
                        ->numeric()
                        ->minValue(1000)
                        ->prefix('KES')
                        ->placeholder('KES 0.00')
                        ->required()
                        ->rule(function (callable $get, $livewire) {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $livewire) {
                                $memberId = $get('member_id');

                                // If inside Relation Manager, member_id might not be in form state yet
                                if (!$memberId && $livewire instanceof LoansRelationManager) {
                                    $memberId = $livewire->ownerRecord->id;
                                }

                                if (!$memberId) {
                                    return;
                                }

                                // 1. Check for existing active loans (Force validation backend-side too)
                                $hasActiveLoan = Loan::where('member_id', $memberId)
                                    ->where('repaid', false)
                                    ->where('id', '!=', $get('id')) // Ignore self
                                    ->exists();

                                if ($hasActiveLoan) {
                                    $fail('This member already has an active (unpaid) loan.');
                                }

                                // 2. Check shares contribution limit
                                $totalShares = \App\Models\Contribution::where('member_id', $memberId)->sum('shares');
                                if ($value > $totalShares) {
                                    $fail("Loan cannot exceed member's total shares (KES " . number_format($totalShares) . ").");
                                }
                            };
                        })
                        ->disabled(fn($record) => $record && !$record->repaid)
                        ->dehydrateStateUsing(fn($state, $record) => !($record?->repaid) ? $state : null),


                    TextInput::make('term_months')
                        ->numeric()
                        ->label('Loan Term (Months)')
                        ->minValue(1)
                        ->placeholder('2')
                        ->required()
                        ->disabled(fn($record) => $record && !$record->repaid)
                        ->dehydrateStateUsing(fn($state, $record) => !($record?->repaid) ? $state : null),

                    TextInput::make('interest_percent')
                        ->label('Interest (%)')
                        ->default(10)
                        ->disabled(fn($record) => $record && !$record->repaid)
                        ->dehydrateStateUsing(fn($state, $record) => !($record?->repaid) ? $state : null),

                    DatePicker::make('disbursed_at')
                        ->label('Disbursed On')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $dueDate = Carbon::parse($state)->addMonths(2);
                                $set('due_at', $dueDate->format('Y-m-d'));
                            }
                        })
                        ->visible(fn($record) => $record && in_array($record->status, [Loan::STATUS_DISBURSED, Loan::STATUS_REPAID]))
                        ->disabled(fn($record) => $record && $record->status !== Loan::STATUS_APPLIED),

                    DatePicker::make('due_at')
                        ->label('Due Date')
                        ->readOnly()
                        ->visible(fn($record) => $record && in_array($record->status, [Loan::STATUS_DISBURSED, Loan::STATUS_REPAID])),

                    \Filament\Forms\Components\Hidden::make('status')
                        ->default(Loan::STATUS_APPLIED),
                ])
                ->columns(2),

            Section::make('Repayment Information')
                ->visible(fn($record) => $record !== null)
                ->schema(fn($record) => [

                    ViewField::make('loan_status')
                        ->view('filament.components.loan-status')
                        ->viewData(['record' => $record])
                        ->columnSpan(6),

                    Toggle::make('repaid')
                        ->label('Loan Fully Repaid?')
                        ->default(false)
                        ->disabled(fn($record) => $record && !$record->repaid)
                        ->dehydrated(fn($state, $record) => !($record?->repaid))
                        ->columnSpan(3),

                    TextInput::make('repaid_amount')
                        ->label('Amount Repaid')
                        ->prefix('KES')
                        ->numeric()
                        ->visible(fn($get) => (bool) $get('repaid'))
                        ->default(0)
                        ->disabled(fn($record) => $record?->repaid)
                        ->dehydrated(fn($state, $record) => !($record?->repaid))
                        ->columnSpan(3),

                ])
                ->columns(12)
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

                    TextColumn::make('member.full_name')
                        ->label('Member')
                        ->sortable()
                        ->searchable(query: function (Builder $query, string $search) {
                            return $query->whereHas('member', function ($q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            });
                        }),

                    TextColumn::make('amount')
                        ->label('Loan Amount')
                        ->money('kes')
                        ->sortable(),

                    BadgeColumn::make('status')
                        ->label('Status')
                        ->colors([
                            'secondary' => Loan::STATUS_APPLIED,
                            'info' => Loan::STATUS_APPROVED,
                            'primary' => Loan::STATUS_DISBURSED,
                            'success' => Loan::STATUS_REPAID,
                        ])
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            Loan::STATUS_APPLIED => 'Applied',
                            Loan::STATUS_APPROVED => 'Approved',
                            Loan::STATUS_DISBURSED => 'Active',
                            Loan::STATUS_REPAID => 'Repaid',
                            default => $state,
                        }),

                    TextColumn::make('approvals_count')
                        ->counts('approvals')
                        ->label('Approvals')
                        ->sortable(),

                    TextColumn::make('balance')
                        ->label('Balance')
                        ->money('kes')
                        ->sortable(),

                    TextColumn::make('disbursed_at')
                        ->label('DisbursedOn')
                        ->date('M Y')
                        ->sortable(),

                    TextColumn::make('due_at')
                        ->label('DueOn')
                        ->date('M Y')
                        ->sortable(),

                ])
                ->filters([

                    SelectFilter::make('status')
                        ->options([
                            Loan::STATUS_APPLIED => 'Applied',
                            Loan::STATUS_APPROVED => 'Approved',
                            Loan::STATUS_DISBURSED => 'Active',
                            Loan::STATUS_REPAID => 'Repaid',
                        ]),

                    Filter::make('disbursed_at')
                        ->label('Disbursed Date Range')
                        ->form([
                            DatePicker::make('from'),
                            DatePicker::make('to'),
                        ])
                        ->query(
                            fn(Builder $query, array $data) =>
                            $query
                                ->when($data['from'], fn($q, $date) => $q->whereDate('disbursed_at', '>=', $date))
                                ->when($data['to'], fn($q, $date) => $q->whereDate('disbursed_at', '<=', $date))
                        ),

                ])
                ->actions([
                    ViewAction::make(),
                    EditAction::make()
                        ->hidden(fn($record) => $record->status === Loan::STATUS_REPAID),

                    \Filament\Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(Loan $record) => in_array($record->status, [Loan::STATUS_APPLIED, Loan::STATUS_APPROVED]) && auth()->user()->member_id && auth()->user()->member_id !== $record->member_id && !$record->approvals()->where('member_id', auth()->user()->member_id)->exists())
                        ->requiresConfirmation()
                        ->form([
                            \Filament\Forms\Components\Textarea::make('remark')
                                ->label('Remarks (Optional)'),
                        ])
                        ->action(function (Loan $record, array $data) {
                            $record->approvals()->create([
                                'member_id' => auth()->user()->member_id,
                                'remark' => $data['remark'] ?? null,
                            ]);

                            $minApprovals = \App\Models\ChamaSetting::current()->min_loan_approvals ?? 3;

                            if ($record->approvals()->count() >= $minApprovals) {
                                $record->update(['status' => Loan::STATUS_APPROVED]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Loan Approved Successfully')
                                ->success()
                                ->send();
                        }),

                    \Filament\Tables\Actions\Action::make('disburse')
                        ->label('Disburse')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('primary')
                        ->visible(fn(Loan $record) => $record->status === Loan::STATUS_APPROVED && auth()->user()->hasRole('admin'))
                        ->requiresConfirmation()
                        ->form([
                            DatePicker::make('disbursed_at')
                                ->default(now())
                                ->required(),
                        ])
                        ->action(function (Loan $record, array $data) {
                            $disbursedAt = Carbon::parse($data['disbursed_at']);
                            $record->update([
                                'status' => Loan::STATUS_DISBURSED,
                                'disbursed_at' => $disbursedAt,
                                'due_at' => $disbursedAt->copy()->addMonths($record->term_months),
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Loan Disbursed')
                                ->success()
                                ->send();
                        }),
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
            LoanRepaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoans::route('/'),
            'create' => CreateLoan::route('/create'),
            'view' => ViewLoan::route('/{record}'),
            'edit' => EditLoan::route('/{record}/edit'),
        ];
    }
}