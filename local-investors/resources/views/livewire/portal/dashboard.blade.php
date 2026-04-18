<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Welcome, {{ $member->first_name }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">Portal</li>
            <li class="breadcrumb-item">Dashboard</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="{{ route('portal.statement') }}" class="btn btn-light-brand me-2"><i class="feather-file-text me-2"></i>My Statement</a>
        <a href="{{ route('portal.apply-loan') }}" class="btn btn-primary"><i class="feather-plus me-2"></i>Apply for Loan</a>
    </div>
    @endsection

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <small class="text-muted">Total Contributions</small>
                <div class="fs-4 fw-bold text-dark">KES {{ number_format($totalContributions, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <small class="text-muted">Active Loan Balance</small>
                <div class="fs-4 fw-bold text-danger">KES {{ number_format($activeLoan?->balance ?? 0, 2) }}</div>
                @if($activeLoan)
                    <small class="text-muted d-block">Due {{ optional($activeLoan->due_at)->format('Y-m-d') ?? '-' }}</small>
                @endif
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <small class="text-muted">Fines / Penalties</small>
                <div class="fs-4 fw-bold text-warning">KES {{ number_format($totalFines, 2) }}</div>
            </div></div>
        </div>
    </div>

    @if($pendingLoan)
        <div class="alert alert-warning">
            <i class="feather-clock me-2"></i>
            You have a pending loan application of <strong>KES {{ number_format($pendingLoan->amount, 2) }}</strong> awaiting committee approval.
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Quick links</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="{{ route('portal.statement') }}" class="btn btn-light-brand w-100"><i class="feather-file-text me-2"></i>View statement</a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('portal.apply-loan') }}" class="btn btn-light-brand w-100"><i class="feather-plus-circle me-2"></i>Apply for a loan</a>
                </div>
                <div class="col-md-4">
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="btn btn-light w-100"><i class="feather-log-out me-2"></i>Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
