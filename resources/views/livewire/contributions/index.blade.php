<div>
    <!-- [ page-header ] start -->
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Contributions</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Contributions</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="{{ route('contributions.create') }}" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    <span>Add Contribution</span>
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
                    <div class="p-3 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
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
                            <select class="form-select form-select-sm" style="width: auto;" wire:model.live="filterMonth">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select form-select-sm" style="width: auto;" wire:model.live="filterMethod">
                                <option value="">All Methods</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="zimele">Zimele</option>
                                <option value="merry_go_round">Merry-Go-Round</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span>Search:</span>
                            <input type="text" class="form-control form-control-sm" style="width: 200px;" 
                                   wire:model.live.debounce.300ms="search" placeholder="Search member...">
                        </div>
                    </div>
                    

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="contributionList">
                            <thead>
                                <tr>
                                    <th wire:click="sortBy('paid_at')" style="cursor: pointer;">
                                        Date
                                        @if($sortField === 'paid_at')
                                            <i class="feather-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('contribution_period')" style="cursor: pointer;">
                                        Period
                                        @if($sortField === 'contribution_period')
                                            <i class="feather-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th>Member</th>
                                    <th class="text-end">Shares</th>
                                    <th class="text-end">Welfare</th>
                                    <th class="text-end">Total</th>
                                    <th>Method</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contributions as $contribution)
                                    <tr class="single-item">
                                        <td>{{ $contribution->paid_at->format('Y-m-d') }}</td>
                                        <td>
                                            @if($contribution->contribution_period)
                                                <span class="badge bg-soft-info text-info">{{ $contribution->contribution_period->format('M Y') }}</span>
                                            @else
                                                <span class="badge bg-soft-secondary text-secondary">{{ $contribution->paid_at->format('M Y') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($contribution->member)
                                                <a href="{{ route('members.edit', $contribution->member) }}" class="hstack gap-3">
                                                    <div class="avatar-image avatar-md bg-soft-primary text-primary d-flex align-items-center justify-content-center rounded">
                                                        {{ strtoupper(substr($contribution->member->first_name, 0, 1) . substr($contribution->member->last_name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="text-truncate-1-line">{{ $contribution->member->full_name }}</span>
                                                    </div>
                                                </a>
                                            @else
                                                <span class="text-muted">Unknown Member</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($contribution->shares, 2) }}</td>
                                        <td class="text-end">{{ number_format($contribution->welfare, 2) }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($contribution->shares + $contribution->welfare + $contribution->merry_go_round + $contribution->penalty, 2) }}
                                        </td>
                                        <td>
                                            @php
                                                $methodBadge = match($contribution->payment_method) {
                                                    'mpesa' => ['success', 'M-Pesa'],
                                                    'zimele' => ['primary', 'Zimele'],
                                                    'merry_go_round' => ['warning', 'Merry-Go-Round'],
                                                    default => ['secondary', ucfirst($contribution->payment_method ?? 'N/A')],
                                                };
                                            @endphp
                                            <span class="badge bg-soft-{{ $methodBadge[0] }} text-{{ $methodBadge[0] }}">{{ $methodBadge[1] }}</span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('contributions.edit', $contribution) }}" class="avatar-text avatar-md" title="Edit">
                                                    <i class="feather feather-edit-3"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="avatar-text avatar-md text-danger" 
                                                   wire:click="delete({{ $contribution->id }})"
                                                   wire:confirm="Are you sure you want to delete this contribution?">
                                                    <i class="feather feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="feather-briefcase fs-1 mb-3 d-block"></i>
                                            No contributions found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="3">Filtered Totals</td>
                                    <td class="text-end">KES {{ number_format($totalShares, 2) }}</td>
                                    <td class="text-end">KES {{ number_format($totalWelfare, 2) }}</td>
                                    <td class="text-end">KES {{ number_format($grandTotal, 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($contributions->hasPages())
                        <div class="p-3 border-top">
                            {{ $contributions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
