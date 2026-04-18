<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $project->name }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item">{{ $project->name }}</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex gap-2">
            <a href="javascript:window.print()" class="btn btn-light-brand"><i class="feather-printer me-2"></i>Print</a>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-light-brand"><i class="feather-edit me-2"></i>Edit</a>
            <a href="{{ route('projects.index') }}" class="btn btn-light-brand"><i class="feather-arrow-left me-2"></i>Back</a>
        </div>
    </div>
    @endsection

    <!-- Summary Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Total Income</small>
                <div class="fs-5 fw-bold text-dark">KES {{ number_format($totalIncome, 2) }}</div>
                <small class="text-muted">{{ $withdrawals->count() }} withdrawal(s) from Zimele</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Total Expenditure</small>
                <div class="fs-5 fw-bold text-danger">KES {{ number_format($totalExpenditure, 2) }}</div>
                <small class="text-muted">{{ $expenditures->count() }} item(s)</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Returned to Zimele</small>
                <div class="fs-5 fw-bold text-success">KES {{ number_format($totalReturned, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card"><div class="card-body">
                <small class="text-muted">Balance</small>
                <div class="fs-5 fw-bold {{ $balance >= 0 ? 'text-primary' : 'text-danger' }}">KES {{ number_format($balance, 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Income (Withdrawals) -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Income (Zimele Withdrawals)</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>#</th><th>Description</th><th>Date</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $i => $w)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $w->description }}</td>
                                        <td>{{ $w->withdrawn_at?->format('Y-m-d') }}</td>
                                        <td class="text-end">{{ number_format($w->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No withdrawals recorded.</td></tr>
                                @endforelse
                            </tbody>
                            @if($withdrawals->isNotEmpty())
                                <tfoot>
                                    <tr class="fw-bold"><td colspan="3">Total Income</td><td class="text-end">KES {{ number_format($totalIncome, 2) }}</td></tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spending by Category -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Spending by Category</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Category</th><th class="text-end">Amount</th><th class="text-end">%</th></tr></thead>
                            <tbody>
                                @forelse($byCategory as $cat => $amount)
                                    <tr>
                                        <td>{{ $cat ?: 'Uncategorised' }}</td>
                                        <td class="text-end">{{ number_format($amount, 2) }}</td>
                                        <td class="text-end">{{ $totalExpenditure > 0 ? number_format(($amount / $totalExpenditure) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No expenditures.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Expenditures -->
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Expenditure Detail</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>#</th><th>Description</th><th>Category</th><th>Date</th><th class="text-end">Amount</th></tr>
                            </thead>
                            <tbody>
                                @forelse($expenditures as $i => $e)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $e->description }}</td>
                                        <td><span class="badge bg-soft-info text-info">{{ $e->category ?: '-' }}</span></td>
                                        <td>{{ $e->spent_at?->format('Y-m-d') }}</td>
                                        <td class="text-end">{{ number_format($e->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No expenditures recorded.</td></tr>
                                @endforelse
                            </tbody>
                            @if($expenditures->isNotEmpty())
                                <tfoot>
                                    <tr class="fw-bold"><td colspan="4">Total Expenditure</td><td class="text-end text-danger">KES {{ number_format($totalExpenditure, 2) }}</td></tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Returns -->
        @if($cashReturns->isNotEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Cash Returns (Surplus returned to Zimele)</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Description</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                @foreach($cashReturns as $cr)
                                    <tr>
                                        <td>{{ $cr->description }}</td>
                                        <td>{{ $cr->returned_at?->format('Y-m-d') }}</td>
                                        <td class="text-end text-success">{{ number_format($cr->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold"><td colspan="2">Total Returned</td><td class="text-end text-success">KES {{ number_format($totalReturned, 2) }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
