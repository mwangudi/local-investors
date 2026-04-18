<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Statement — {{ $member->full_name }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            @role('admin|treasurer')
                <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            @endrole
            <li class="breadcrumb-item">Statement</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="javascript:window.print()" class="btn btn-light-brand"><i class="feather-printer me-2"></i>Print / Save PDF</a>
    </div>
    @endsection

    <!-- Summary -->
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Total Contributions</small>
                <div class="fs-5 fw-bold text-dark">KES {{ number_format($totalContributed, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Net Savings</small>
                <div class="fs-5 fw-bold {{ $netSavings >= 0 ? 'text-success' : 'text-danger' }}">KES {{ number_format($netSavings, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Active Loan Balance</small>
                <div class="fs-5 fw-bold text-danger">KES {{ number_format($activeLoansBalance, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Fines / Penalties</small>
                <div class="fs-5 fw-bold text-warning">KES {{ number_format($totalPenalties, 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Contributions -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Contributions</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Shares</th>
                                    <th class="text-end">Welfare</th>
                                    <th class="text-end">Merry-go-round</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contributions as $c)
                                    <tr>
                                        <td>{{ $c->paid_at?->format('Y-m-d') }}</td>
                                        <td class="text-end">{{ number_format($c->shares, 2) }}</td>
                                        <td class="text-end">{{ number_format($c->welfare, 2) }}</td>
                                        <td class="text-end">{{ number_format($c->merry_go_round, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No contributions yet.</td></tr>
                                @endforelse
                            </tbody>
                            @if($contributions->isNotEmpty())
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td>Total</td>
                                        <td class="text-end">{{ number_format($totalShares, 2) }}</td>
                                        <td class="text-end">{{ number_format($totalWelfare, 2) }}</td>
                                        <td class="text-end">{{ number_format($totalMerry, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loans -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Loans</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
                                        <td>
                                            @role('admin|treasurer')
                                                <a href="{{ route('loans.show', $loan) }}">#{{ $loan->id }}</a>
                                            @else
                                                #{{ $loan->id }}
                                            @endrole
                                        </td>
                                        <td><span class="badge bg-soft-info text-info">{{ ucfirst($loan->status) }}</span></td>
                                        <td class="text-end">{{ number_format($loan->amount, 2) }}</td>
                                        <td class="text-end">{{ number_format($loan->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No loans yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fines -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Fines &amp; Penalties</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Date</th><th>Type</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                @forelse($fines as $f)
                                    <tr>
                                        <td>{{ $f->received_at?->format('Y-m-d') }}</td>
                                        <td>{{ $f->fine_type ?: ($f->description ?: '-') }}</td>
                                        <td class="text-end">{{ number_format($f->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No fines recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Repayments -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Loan Repayments</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Date</th><th>Loan</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                @forelse($repayments as $r)
                                    <tr>
                                        <td>{{ $r->paid_at?->format('Y-m-d') }}</td>
                                        <td>#{{ $r->loan_id }}</td>
                                        <td>{{ ucfirst($r->payment_method ?? $r->method ?? '-') }}</td>
                                        <td class="text-end">{{ number_format($r->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No repayments yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
