<?php

namespace App\Livewire\Withdrawals;

use App\Models\Withdrawal;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'withdrawn_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function delete($id)
    {
        Withdrawal::findOrFail($id)->delete();
        session()->flash('success', 'Withdrawal deleted successfully.');
    }

    public function render()
    {
        $withdrawals = Withdrawal::with('member')
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('member', function ($q) {
                          $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.withdrawals.index', [
            'withdrawals' => $withdrawals,
        ]);
    }
}
