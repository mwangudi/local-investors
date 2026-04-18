<?php

namespace App\Livewire\Expenditures;

use App\Models\Expenditure;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'spent_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function delete($id)
    {
        Expenditure::findOrFail($id)->delete();
        session()->flash('success', 'Expenditure deleted successfully.');
    }

    public function render()
    {
        $expenditures = Expenditure::when($this->search, function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('category', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.expenditures.index', [
            'expenditures' => $expenditures,
        ]);
    }
}
