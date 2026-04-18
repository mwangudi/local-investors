<?php

namespace App\Livewire\CashReturns;

use App\Models\CashReturn;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'returned_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function delete($id)
    {
        CashReturn::findOrFail($id)->delete();
        session()->flash('success', 'Cash Return deleted successfully.');
    }

    public function render()
    {
        $cashReturns = CashReturn::when($this->search, function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.cash-returns.index', [
            'cashReturns' => $cashReturns,
        ]);
    }
}
