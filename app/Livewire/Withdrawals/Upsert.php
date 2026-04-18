<?php

namespace App\Livewire\Withdrawals;

use App\Models\Withdrawal;
use App\Models\Member;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Withdrawal $withdrawal = null;
    
    public $description = '';
    public $amount = 0;
    public $withdrawn_at;
    public $member_id = null;

    protected $rules = [
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'withdrawn_at' => 'required|date',
        'member_id' => 'required|exists:members,id',
    ];

    public function mount(?Withdrawal $withdrawal = null)
    {
        if ($withdrawal && $withdrawal->exists) {
            $this->withdrawal = $withdrawal;
            $this->description = $withdrawal->description;
            $this->amount = $withdrawal->amount;
            $this->withdrawn_at = $withdrawal->withdrawn_at ? $withdrawal->withdrawn_at->format('Y-m-d') : null;
            $this->member_id = $withdrawal->member_id;
        } else {
            $this->withdrawn_at = date('Y-m-d');
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->withdrawal) {
            $this->withdrawal->update($validated);
            session()->flash('success', 'Withdrawal updated successfully.');
        } else {
            Withdrawal::create($validated);
            session()->flash('success', 'Withdrawal created successfully.');
        }

        $this->redirect(route('withdrawals.index'));
    }

    public function render()
    {
        return view('livewire.withdrawals.upsert', [
            'members' => Member::orderBy('first_name')->get(),
        ]);
    }
}
