<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Chama Settings</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
        </ul>
    </div>
    @endsection

    <div class="row">
        <div class="col-lg-8">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Loan & Finance Settings</h5>
                </div>
                <div class="card-body">

                    <form wire:submit="save" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Standard Interest Rate (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('standard_interest_percent') is-invalid @enderror" 
                                           wire:model="standard_interest_percent">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Applied to new loans</small>
                                @error('standard_interest_percent') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Overdue Penalty Rate (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('overdue_penalty_percent') is-invalid @enderror" 
                                           wire:model="overdue_penalty_percent">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Applied after grace period</small>
                                @error('overdue_penalty_percent') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Default Loan Duration (Months)</label>
                                <input type="number" class="form-control @error('loan_duration_months') is-invalid @enderror" 
                                       wire:model="loan_duration_months">
                                @error('loan_duration_months') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Grace Period (Days)</label>
                                <input type="number" class="form-control @error('grace_period_days') is-invalid @enderror" 
                                       wire:model="grace_period_days">
                                <small class="text-muted">Days after due date before penalty</small>
                                @error('grace_period_days') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Minimum Loan Approvals</label>
                                <input type="number" class="form-control @error('min_loan_approvals') is-invalid @enderror" 
                                       wire:model="min_loan_approvals">
                                <small class="text-muted">Required approvals before disbursement</small>
                                @error('min_loan_approvals') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Quick Info</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <strong>Interest Calculation:</strong>
                            <p class="text-muted mb-0 small">Interest is calculated as a percentage of the principal amount.</p>
                        </li>
                        <li class="mb-3">
                            <strong>Overdue Penalty:</strong>
                            <p class="text-muted mb-0 small">Applied to unpaid loans after the grace period ends.</p>
                        </li>
                        <li>
                            <strong>Loan Approvals:</strong>
                            <p class="text-muted mb-0 small">Number of committee approvals required before a loan can be disbursed.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
