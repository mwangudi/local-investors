<?php

namespace App\Livewire\Settings;

use App\Models\ChamaSetting;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public ChamaSetting $settings;
    
    public $standard_interest_percent = 10;
    public $overdue_penalty_percent = 30;
    public $loan_duration_months = 3;
    public $grace_period_days = 0;
    public $min_loan_approvals = 2;

    protected $rules = [
        'standard_interest_percent' => 'required|numeric|min:0|max:100',
        'overdue_penalty_percent' => 'required|numeric|min:0|max:100',
        'loan_duration_months' => 'required|integer|min:1',
        'grace_period_days' => 'required|integer|min:0',
        'min_loan_approvals' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->settings = ChamaSetting::current();
        
        $this->standard_interest_percent = $this->settings->standard_interest_percent ?? 10;
        $this->overdue_penalty_percent = $this->settings->overdue_penalty_percent ?? 30;
        $this->loan_duration_months = $this->settings->loan_duration_months ?? 3;
        $this->grace_period_days = $this->settings->grace_period_days ?? 0;
        $this->min_loan_approvals = $this->settings->min_loan_approvals ?? 2;
    }

    public function save()
    {
        $validated = $this->validate();

        $this->settings->update($validated);
        
        session()->flash('success', 'Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.settings.edit');
    }
}
