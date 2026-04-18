<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Apply for a Loan</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Portal</a></li>
            <li class="breadcrumb-item">Apply</li>
        </ul>
    </div>
    @endsection

    <div class="card">
        <div class="card-body">
            <form wire:submit="submit" novalidate>
                <div class="mb-3">
                    <label class="form-label">Amount (KES)</label>
                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" wire:model="amount" placeholder="e.g. 10000">
                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Term (months)</label>
                    <input type="number" class="form-control @error('term_months') is-invalid @enderror" wire:model="term_months" min="1" max="60">
                    @error('term_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason (optional)</label>
                    <textarea class="form-control" rows="3" wire:model="reason" placeholder="Purpose of the loan"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="feather-send me-2"></i>Submit Application</button>
                    <a href="{{ route('portal.dashboard') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
