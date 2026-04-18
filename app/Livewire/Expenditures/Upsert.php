<?php

namespace App\Livewire\Expenditures;

use App\Models\Expenditure;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Expenditure $expenditure = null;
    
    public $description = '';
    public $amount = 0;
    public $spent_at;
    public $category = '';

    protected $rules = [
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'spent_at' => 'required|date',
        'category' => 'required|string|max:100',
    ];

    public function mount(?Expenditure $expenditure = null)
    {
        if ($expenditure && $expenditure->exists) {
            $this->expenditure = $expenditure;
            $this->description = $expenditure->description;
            $this->amount = $expenditure->amount;
            $this->spent_at = $expenditure->spent_at ? $expenditure->spent_at->format('Y-m-d') : null;
            $this->category = $expenditure->category;
        } else {
            $this->spent_at = date('Y-m-d');
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->expenditure) {
            $this->expenditure->update($validated);
            session()->flash('success', 'Expenditure updated successfully.');
        } else {
            Expenditure::create($validated);
            session()->flash('success', 'Expenditure created successfully.');
        }

        $this->redirect(route('expenditures.index'));
    }

    public function render()
    {
        return view('livewire.expenditures.upsert');
    }
}
