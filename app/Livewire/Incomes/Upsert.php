<?php

namespace App\Livewire\Incomes;

use App\Models\Income;
use App\Models\Member;
use App\Models\Loan;
use App\Notifications\InAppNotification;
use App\Services\NotifyService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Income $income = null;
    
    public $amount = 0;
    public $received_at;
    public $description = '';
    public $loan_id = null;
    public $category = '';
    public $fine_type = '';
    public $member_id = null;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'received_at' => 'required|date',
        'description' => 'nullable|string|max:255',
        'loan_id' => 'nullable|exists:loans,id',
        'category' => 'required|string|max:100',
        'fine_type' => 'nullable|string|max:100',
        'member_id' => 'nullable|exists:members,id',
    ];

    public function mount(?Income $income = null)
    {
        if ($income && $income->exists) {
            $this->income = $income;
            $this->amount = $income->amount;
            $this->received_at = $income->received_at ? $income->received_at->format('Y-m-d') : null;
            $this->description = $income->description;
            $this->loan_id = $income->loan_id;
            $this->category = $income->category;
            $this->fine_type = $income->fine_type;
            $this->member_id = $income->member_id;
        } else {
            $this->received_at = date('Y-m-d');
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->income) {
            $this->income->update($validated);
            session()->flash('success', 'Income updated successfully.');
        } else {
            $income = Income::create($validated);

            // If the income is a fine and tied to a member, notify them
            if ($income->member_id && strcasecmp((string) $income->category, 'fine') === 0) {
                NotifyService::toMember($income->member_id, new InAppNotification(
                    type:    'fine.issued',
                    title:   'Fine issued',
                    message: 'A fine of KES ' . number_format((float) $income->amount, 2) . ' was recorded'
                           . ($income->fine_type ? ' (' . $income->fine_type . ')' : '') . '.',
                    url:     route('incomes.index'),
                    icon:    'feather-alert-triangle',
                    color:   'warning',
                ));
            }

            session()->flash('success', 'Income created successfully.');
        }

        $this->redirect(route('incomes.index'));
    }

    public function render()
    {
        return view('livewire.incomes.upsert', [
            'members' => Member::orderBy('first_name')->get(),
            'loans' => Loan::with('member')->orderBy('id', 'desc')->get(),
        ]);
    }
}
