<?php

namespace App\Livewire\Contributions;

use App\Models\Contribution;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'paid_at';
    public $sortDirection = 'desc';
    public $filterMonth = '';
    public $filterMethod = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'filterMonth' => ['except' => ''],
        'filterMethod' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterMethod()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($contributionId)
    {
        Contribution::findOrFail($contributionId)->delete();
        session()->flash('success', 'Contribution deleted successfully.');
    }

    public function render()
    {
        $baseQuery = Contribution::with('member')
            ->when($this->search, function ($query) {
                $query->whereHas('member', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterMonth, function ($query) {
                // If it is stored, we can filter by the month of contribution_period
                $query->whereMonth('contribution_period', $this->filterMonth)
                      ->orWhere(function ($q) {
                          $q->whereNull('contribution_period')->whereMonth('paid_at', $this->filterMonth);
                      });
            })
            ->when($this->filterMethod, function ($query) {
                $query->where('payment_method', $this->filterMethod);
            });

        // Compute totals from the full filtered set (before pagination)
        $totalShares  = (clone $baseQuery)->sum('shares');
        $totalWelfare = (clone $baseQuery)->sum('welfare');
        $totalMerry   = (clone $baseQuery)->sum('merry_go_round');
        $totalPenalty  = (clone $baseQuery)->sum('penalty');
        $grandTotal   = $totalShares + $totalWelfare + $totalMerry + $totalPenalty;

        $contributions = $baseQuery
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.contributions.index', [
            'contributions' => $contributions,
            'totalShares'   => $totalShares,
            'totalWelfare'  => $totalWelfare,
            'totalMerry'    => $totalMerry,
            'totalPenalty'  => $totalPenalty,
            'grandTotal'    => $grandTotal,
        ]);
    }
}
