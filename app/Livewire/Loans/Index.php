<?php

namespace App\Livewire\Loans;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'disbursed_at';
    public string $sortDirection = 'desc';
    public string $statusFilter = '';
    public string $memberFilter = '';
    public string $dueFrom = '';
    public string $dueTo = '';
    public bool $overdueOnly = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'memberFilter' => ['except' => ''],
        'dueFrom' => ['except' => ''],
        'dueTo' => ['except' => ''],
        'overdueOnly' => ['except' => false],
        'sortField' => ['except' => 'disbursed_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // Public properties are client-writable, so the sort column must never reach orderBy unchecked.
    public const SORTABLE = ['id', 'amount', 'interest_percent', 'disbursed_at', 'due_at', 'status'];

    private const FILTERS = ['search', 'perPage', 'statusFilter', 'memberFilter', 'dueFrom', 'dueTo', 'overdueOnly'];

    public function updated(string $property): void
    {
        if (in_array($property, self::FILTERS, true)) {
            $this->resetPage();
        }
    }

    public function sortBy($field)
    {
        if (! in_array($field, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function clearFilters(): void
    {
        $this->reset(self::FILTERS);
        $this->resetPage();
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== ''
            || $this->statusFilter !== ''
            || $this->memberFilter !== ''
            || $this->dueFrom !== ''
            || $this->dueTo !== ''
            || $this->overdueOnly;
    }

    public function delete($loanId)
    {
        Loan::findOrFail($loanId)->delete();
        session()->flash('success', 'Loan deleted successfully.');
    }

    public function render()
    {
        $unsettled = $this->baseQuery()->where('status', '!=', Loan::STATUS_REPAID);

        $payable = (float) (clone $unsettled)->sum(DB::raw('amount + (amount * interest_percent / 100)'));
        $repaid = (float) LoanRepayment::whereIn('loan_id', (clone $unsettled)->select('id'))->sum('amount');

        $loans = $this->baseQuery()
            ->with('member')
            ->withSum('repayments', 'amount')
            ->orderBy($this->sortColumn(), $this->sortOrder())
            ->paginate($this->perPage);

        return view('livewire.loans.index', [
            'loans' => $loans,
            'members' => Member::orderBy('first_name')->get(['id', 'first_name', 'last_name']),
            'totalPrincipal' => (float) $this->baseQuery()->sum('amount'),
            'totalOutstanding' => max(0, $payable - $repaid),
            'overdueCount' => $this->baseQuery()->overdue()->count(),
        ]);
    }

    private function baseQuery()
    {
        return Loan::query()
            ->when($this->search !== '', fn ($query) => $this->applySearch($query))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->memberFilter !== '', fn ($query) => $query->where('member_id', $this->memberFilter))
            ->when($this->dueFrom !== '', fn ($query) => $query->whereDate('due_at', '>=', $this->dueFrom))
            ->when($this->dueTo !== '', fn ($query) => $query->whereDate('due_at', '<=', $this->dueTo))
            ->when($this->overdueOnly, fn ($query) => $query->overdue());
    }

    // Every term must match a name part, so "Charles Kingori" matches as well as "kingori".
    private function applySearch($query)
    {
        $terms = preg_split('/\s+/', trim($this->search), -1, PREG_SPLIT_NO_EMPTY);

        return $query->whereHas('member', function ($member) use ($terms) {
            foreach ($terms as $term) {
                $member->where(function ($name) use ($term) {
                    $name->where('first_name', 'like', '%' . $term . '%')
                        ->orWhere('last_name', 'like', '%' . $term . '%');
                });
            }
        });
    }

    private function sortColumn(): string
    {
        return in_array($this->sortField, self::SORTABLE, true) ? $this->sortField : 'disbursed_at';
    }

    private function sortOrder(): string
    {
        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }
}
