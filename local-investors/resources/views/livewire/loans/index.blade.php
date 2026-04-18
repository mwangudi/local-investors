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
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                        <i class="feather-filter"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', '')">
                            <i class="feather-eye me-3"></i>
                            <span>All</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', 'applied')">
                            <i class="feather-file-text me-3"></i>
                            <span>Applied</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', 'approved')">
                            <i class="feather-check-circle me-3"></i>
                            <span>Approved</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', 'disbursed')">
                            <i class="feather-dollar-sign me-3"></i>
                            <span>Disbursed</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', 'repaid')">
                            <i class="feather-check-square me-3"></i>
                            <span>Repaid</span>
                        </a>
                    </div>
                </div>
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
            <div class="card stretch stretch-full">
                <div class="card-body p-0">
                    <!-- Search and Per Page -->
                    <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span>Show</span>
                            <select class="form-select form-select-sm" style="width: auto;" wire:model.live="perPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>entries</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span>Search:</span>
                            <input type="text" class="form-control form-control-sm" style="width: 200px;" 
                                   wire:model.live.debounce.300ms="search" placeholder="Search member...">
                        </div>
                    </div>
                    

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="loanList">
                            <thead>
                                <tr>
                                    <th wire:click="sortBy('id')" style="cursor: pointer;">ID</th>
                                    <th>Member</th>
                                    <th class="text-end" wire:click="sortBy('amount')" style="cursor: pointer;">Amount</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                    <th>Disbursed</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr class="single-item">
                                        <td>#{{ $loan->id }}</td>
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
                                        <td>{{ $loan->disbursed_at ? $loan->disbursed_at->format('Y-m-d') : '-' }}</td>
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
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="feather-credit-card fs-1 mb-3 d-block"></i>
                                            No loans found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($loans->hasPages())
                        <div class="p-3 border-top">
                            {{ $loans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
