<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Dashboard</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Dashboard</li>
        </ul>
    </div>
    @endsection

    <!-- Statistics Cards Row -->
    <div class="row">
        <!-- Total Members -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-users"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark"><span class="counter">{{ $totalMembers }}</span></div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">Total Members</h3>
                            </div>
                        </div>
                        <a href="{{ route('members.index') }}" class="">
                            <i class="feather-more-vertical"></i>
                        </a>
                    </div>
                    <div class="pt-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="{{ route('members.index') }}" class="fs-12 fw-medium text-muted text-truncate-1-line">Active Members</a>
                            <div class="w-100 text-end">
                                <span class="fs-12 text-dark">{{ $activeMembers ?? $totalMembers }}</span>
                                <span class="fs-11 text-muted">({{ $totalMembers > 0 ? round((($activeMembers ?? $totalMembers) / $totalMembers) * 100) : 0 }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-2 ht-3">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $totalMembers > 0 ? round((($activeMembers ?? $totalMembers) / $totalMembers) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Contributions -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-dollar-sign"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark">KES <span class="counter">{{ number_format($totalContributions, 0) }}</span></div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">Total Contributions</h3>
                            </div>
                        </div>
                        <a href="{{ route('contributions.index') }}" class="">
                            <i class="feather-more-vertical"></i>
                        </a>
                    </div>
                    <div class="pt-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="{{ route('contributions.index') }}" class="fs-12 fw-medium text-muted text-truncate-1-line">This Month</a>
                            <div class="w-100 text-end">
                                <span class="fs-12 text-dark">KES {{ number_format($monthlyContributions ?? 0, 0) }}</span>
                                <span class="fs-11 text-muted">({{ $totalContributions > 0 ? round((($monthlyContributions ?? 0) / $totalContributions) * 100) : 0 }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-2 ht-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalContributions > 0 ? min(round((($monthlyContributions ?? 0) / $totalContributions) * 100), 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Loans -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-briefcase"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark"><span class="counter">{{ $activeLoansCount }}</span>/<span class="counter">{{ $totalLoansCount ?? $activeLoansCount }}</span></div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">Active Loans</h3>
                            </div>
                        </div>
                        <a href="{{ route('loans.index') }}" class="">
                            <i class="feather-more-vertical"></i>
                        </a>
                    </div>
                    <div class="pt-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="{{ route('loans.index') }}" class="fs-12 fw-medium text-muted text-truncate-1-line">Disbursed</a>
                            <div class="w-100 text-end">
                                <span class="fs-12 text-dark">{{ $activeLoansCount }} Active</span>
                                <span class="fs-11 text-muted">({{ ($totalLoansCount ?? $activeLoansCount) > 0 ? round(($activeLoansCount / ($totalLoansCount ?? $activeLoansCount)) * 100) : 0 }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-2 ht-3">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($totalLoansCount ?? $activeLoansCount) > 0 ? round(($activeLoansCount / ($totalLoansCount ?? $activeLoansCount)) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Disbursed Amount -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-activity"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark">KES <span class="counter">{{ number_format($activeLoansAmount, 0) }}</span></div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">Loans Disbursed</h3>
                            </div>
                        </div>
                        <a href="{{ route('loans.index') }}" class="">
                            <i class="feather-more-vertical"></i>
                        </a>
                    </div>
                    <div class="pt-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="{{ route('loans.index') }}" class="fs-12 fw-medium text-muted text-truncate-1-line">Outstanding Balance</a>
                            <div class="w-100 text-end">
                                <span class="fs-12 text-dark">KES {{ number_format($outstandingBalance ?? $activeLoansAmount, 0) }}</span>
                                <span class="fs-11 text-muted">({{ $activeLoansAmount > 0 ? round((($outstandingBalance ?? $activeLoansAmount) / $activeLoansAmount) * 100) : 0 }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-2 ht-3">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $activeLoansAmount > 0 ? min(round((($outstandingBalance ?? $activeLoansAmount) / $activeLoansAmount) * 100), 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashbook Snapshot -->
    <div class="row g-3 mb-2">
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar-text avatar-md bg-soft-success text-success"><i class="feather-trending-up"></i></div>
                    <div>
                        <div class="fs-14 fw-semibold text-muted">Total Income</div>
                        <div class="fs-5 fw-bold text-success">KES {{ number_format($totalIncome, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar-text avatar-md bg-soft-danger text-danger"><i class="feather-trending-down"></i></div>
                    <div>
                        <div class="fs-14 fw-semibold text-muted">Total Expenditure</div>
                        <div class="fs-5 fw-bold text-danger">KES {{ number_format($totalExpenditure, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar-text avatar-md bg-soft-warning text-warning"><i class="feather-alert-triangle"></i></div>
                    <div>
                        <div class="fs-14 fw-semibold text-muted">Overdue / Pending</div>
                        <div class="fs-5 fw-bold text-dark">{{ $overdueLoansCount }} overdue · {{ $pendingLoansCount }} pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar-text avatar-md bg-soft-primary text-primary"><i class="feather-briefcase"></i></div>
                    <div>
                        <div class="fs-14 fw-semibold text-muted">Cash on Hand (est.)</div>
                        <div class="fs-5 fw-bold {{ $cashOnHand >= 0 ? 'text-primary' : 'text-danger' }}">KES {{ number_format($cashOnHand, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->    <div class="row">
        <!-- Recent Contributions -->
        <div class="col-xxl-6">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Recent Contributions</h5>
                    <div class="card-header-action">
                        <div class="card-header-btn">
                            <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                            </div>
                        </div>
                        <a href="{{ route('contributions.index') }}" class="btn btn-sm btn-light-primary">View All</a>
                    </div>
                </div>
                <div class="card-body custom-card-action p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Member</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentContributions as $contribution)
                                    <tr>
                                        <td>{{ $contribution->paid_at->format('Y-m-d') }}</td>
                                        <td>{{ $contribution->member->full_name }}</td>
                                        <td class="text-end fw-bold">
                                            KES {{ number_format($contribution->shares + $contribution->welfare + $contribution->merry_go_round, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No contributions yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Loans -->
        <div class="col-xxl-6">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Recent Loans</h5>
                    <div class="card-header-action">
                        <div class="card-header-btn">
                            <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                            </div>
                        </div>
                        <a href="{{ route('loans.index') }}" class="btn btn-sm btn-light-primary">View All</a>
                    </div>
                </div>
                <div class="card-body custom-card-action p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Member</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLoans as $loan)
                                    <tr>
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
                                            <span class="badge bg-soft-{{ $badgeClass }} text-{{ $badgeClass }}">{{ ucfirst($loan->status) }}</span>
                                        </td>
                                        <td>{{ $loan->member->full_name }}</td>
                                        <td class="text-end fw-bold">KES {{ number_format($loan->amount, 2) }}</td>
                                        <td>{{ $loan->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No loans yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
