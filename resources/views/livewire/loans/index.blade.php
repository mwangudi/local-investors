<div>
    <!-- [ page-header ] start -->
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Loans</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Loans</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="{{ route('loans.create') }}" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    <span>Apply Loan</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
    @endsection
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-lg-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card stretch stretch-full">
                <div class="card-body p-0">
                    <!-- Filters -->
                    <div class="p-3 border-bottom">
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label fs-11 text-muted mb-1">Search</label>
                                <input type="text" class="form-control form-control-sm"
                                       wire:model.live.debounce.300ms="search" placeholder="Member name...">
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label class="form-label fs-11 text-muted mb-1">Member</label>
                                <select class="form-select form-select-sm" wire:model.live="memberFilter">
                                    <option value="">All members</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label class="form-label fs-11 text-muted mb-1">Status</label>
                                <select class="form-select form-select-sm" wire:model.live="statusFilter">
                                    <option value="">All statuses</option>
                                    <option value="applied">Applied</option>
                                    <option value="approved">Approved</option>
                                    <option value="disbursed">Disbursed</option>
                                    <option value="repaid">Repaid</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label class="form-label fs-11 text-muted mb-1">Due from</label>
                                <input type="date" class="form-control form-control-sm" wire:model.live="dueFrom">
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label class="form-label fs-11 text-muted mb-1">Due to</label>
                                <input type="date" class="form-control form-control-sm" wire:model.live="dueTo">
                            </div>
                            <div class="col-xl-1 col-md-6">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" id="overdueOnly"
                                           wire:model.live="overdueOnly">
                                    <label class="form-check-label fs-11" for="overdueOnly">Overdue</label>
                                </div>
                            </div>
                        </div>

                        @if ($this->hasActiveFilters)
                            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                <span class="fs-11 text-muted">Filtered:</span>
                                @if ($search)
                                    <span class="badge bg-soft-primary text-primary">Search "{{ $search }}"</span>
                                @endif
                                @if ($memberFilter)
                                    <span class="badge bg-soft-primary text-primary">{{ $members->firstWhere('id', (int) $memberFilter)?->full_name }}</span>
                                @endif
                                @if ($statusFilter)
                                    <span class="badge bg-soft-primary text-primary">{{ ucfirst($statusFilter) }}</span>
                                @endif
                                @if ($dueFrom)
                                    <span class="badge bg-soft-primary text-primary">Due from {{ $dueFrom }}</span>
                                @endif
                                @if ($dueTo)
                                    <span class="badge bg-soft-primary text-primary">Due to {{ $dueTo }}</span>
                                @endif
                                @if ($overdueOnly)
                                    <span class="badge bg-soft-danger text-danger">Overdue only</span>
                                @endif
                                <a href="javascript:void(0)" class="fs-11 text-danger" wire:click="clearFilters">
                                    <i class="feather-x me-1"></i>Clear all
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Summary of the filtered set -->
                    <div class="p-3 border-bottom d-flex flex-wrap gap-4">
                        <div>
                            <div class="fs-11 text-muted">Loans</div>
                            <div class="fw-bold">{{ number_format($loans->total()) }}</div>
                        </div>
                        <div>
                            <div class="fs-11 text-muted">Principal</div>
                            <div class="fw-bold">{{ number_format($totalPrincipal, 2) }}</div>
                        </div>
                        <div>
                            <div class="fs-11 text-muted">Outstanding (incl. interest)</div>
                            <div class="fw-bold">{{ number_format($totalOutstanding, 2) }}</div>
                        </div>
                        <div>
                            <div class="fs-11 text-muted">Overdue</div>
                            <div class="fw-bold {{ $overdueCount ? 'text-danger' : '' }}">{{ number_format($overdueCount) }}</div>
                        </div>
                        <div class="ms-auto d-flex align-items-center gap-2">
                            <span class="fs-11 text-muted">Show</span>
                            <select class="form-select form-select-sm" style="width: auto;" wire:model.live="perPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    @php
                        $sortIcon = function ($field) use ($sortField, $sortDirection) {
                            if ($sortField !== $field) {
                                return 'feather-more-vertical text-muted opacity-50';
                            }
                            return $sortDirection === 'asc' ? 'feather-arrow-up text-primary' : 'feather-arrow-down text-primary';
                        };
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="loanList">
                            <thead>
                                <tr>
                                    <th wire:click="sortBy('id')" style="cursor: pointer;">
                                        Reference <i class="{{ $sortIcon('id') }} fs-10"></i>
                                    </th>
                                    <th>Member</th>
                                    <th class="text-end" wire:click="sortBy('amount')" style="cursor: pointer;">
                                        Amount <i class="{{ $sortIcon('amount') }} fs-10"></i>
                                    </th>
                                    <th class="text-end">Balance</th>
                                    <th wire:click="sortBy('status')" style="cursor: pointer;">
                                        Status <i class="{{ $sortIcon('status') }} fs-10"></i>
                                    </th>
                                    <th wire:click="sortBy('disbursed_at')" style="cursor: pointer;">
                                        Disbursed <i class="{{ $sortIcon('disbursed_at') }} fs-10"></i>
                                    </th>
                                    <th wire:click="sortBy('due_at')" style="cursor: pointer;">
                                        Due <i class="{{ $sortIcon('due_at') }} fs-10"></i>
                                    </th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr class="single-item">
                                        <td><span class="fw-bold">{{ $loan->reference }}</span></td>
                                        <td>
                                            @if($loan->member)
                                                <a href="{{ route('members.edit', $loan->member) }}" class="hstack gap-3">
                                                    <div class="avatar-image avatar-md bg-soft-primary text-primary d-flex align-items-center justify-content-center rounded">
                                                        {{ strtoupper(substr($loan->member->first_name, 0, 1) . substr($loan->member->last_name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="text-truncate-1-line">{{ $loan->member->full_name }}</span>
                                                    </div>
                                                </a>
                                            @else
                                                <span class="text-muted">Unknown Member</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($loan->amount, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($loan->balance, 2) }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($loan->status) {
                                                    'applied' => 'warning',
                                                    'approved' => 'info',
                                                    'disbursed' => 'primary',
                                                    'repaid' => 'success',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-soft-{{ $badgeClass }} text-{{ $badgeClass }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $loan->disbursed_at ? $loan->disbursed_at->format('d M Y') : '-' }}</td>
                                        <td>
                                            @if($loan->due_at)
                                                <span class="{{ $loan->is_overdue ? 'text-danger fw-bold' : '' }}">
                                                    {{ $loan->due_at->format('d M Y') }}
                                                </span>
                                                @if($loan->is_overdue)
                                                    <span class="badge bg-soft-danger text-danger ms-1">Overdue</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('loans.show', $loan) }}" class="avatar-text avatar-md" title="View Details">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                <a href="{{ route('loans.edit', $loan) }}" class="avatar-text avatar-md" title="Edit">
                                                    <i class="feather feather-edit-3"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="avatar-text avatar-md text-danger" 
                                                   wire:click="delete({{ $loan->id }})"
                                                   wire:confirm="Are you sure you want to delete this loan?">
                                                    <i class="feather feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="feather-credit-card fs-1 mb-3 d-block"></i>
                                            No loans found
                                            @if ($this->hasActiveFilters)
                                                <div class="mt-2">
                                                    <a href="javascript:void(0)" wire:click="clearFilters">Clear filters</a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="fs-12 text-muted">
                            @if ($loans->total() > 0)
                                Showing {{ number_format($loans->firstItem()) }} to {{ number_format($loans->lastItem()) }}
                                of {{ number_format($loans->total()) }} entries
                            @else
                                No entries to show
                            @endif
                        </div>
                        @if($loans->hasPages())
                            <div>{{ $loans->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
