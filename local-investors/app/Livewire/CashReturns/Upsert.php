<?php

namespace App\Livewire\CashReturns;

use App\Models\CashReturn;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?CashReturn $cashReturn = null;
    
    public $amount = 0;
    public $returned_at;
    public $description = '';

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'returned_at' => 'required|date',
        'description' => 'required|string|max:255',
    ];

    public function mount(?CashReturn $cashReturn = null)
    {
        if ($cashReturn && $cashReturn->exists) {
            $this->cashReturn = $cashReturn;
            $this->amount = $cashReturn->amount;
            $this->returned_at = $cashReturn->returned_at ? $cashReturn->returned_at->format('Y-m-d') : null;
            $this->description = $cashReturn->description;
        } else {
            $this->returned_at = date('Y-m-d');
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->cashReturn) {
            $this->cashReturn->update($validated);
            session()->flash('success', 'Cash Return updated successfully.');
        } else {
            CashReturn::create($validated);
            session()->flash('success', 'Cash Return created successfully.');
        }

        $this->redirect(route('cash-returns.index'));
    }

    public function render()
    {
        return view('livewire.cash-returns.upsert');
    }
}
