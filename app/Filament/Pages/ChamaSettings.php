<?php

namespace App\Filament\Pages;

use App\Filament\Traits\HasIconizedFormActions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Models\ChamaSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Form;

class ChamaSettings extends Page
{
    use HasIconizedFormActions;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System Administration';
    protected static ?string $title = 'Loan Settings';
    protected static string $view = 'filament.pages.chama-settings';
    protected static ?int $navigationSort = 2;

    public $data = [];

    public function mount()
    {
        $this->data = ChamaSetting::current()->toArray();
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
    public function save()
    {
        ChamaSetting::current()->update($this->data);
        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')   // Still valid in Filament 4
            ->schema([

                Section::make('Loan Configuration')
                    ->description('Configure how loan interest, penalties, and approvals are handled.')
                    ->schema([

                        TextInput::make('standard_interest_percent')
                            ->label('Standard Interest %')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('overdue_penalty_percent')
                            ->label('Overdue Penalty %')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('min_loan_approvals')
                            ->label('Min. Approvals to Disburse')
                            ->numeric()
                            ->default(3)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Section::make('Loan Duration Rules')
                    ->schema([

                        TextInput::make('loan_duration_months')
                            ->label('Max Loan Period (Months)')
                            ->numeric()
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('grace_period_days')
                            ->label('Grace Period (Days Before Overdue)')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(2),
                    ])
                    ->columns(4),
            ]);
    }

}