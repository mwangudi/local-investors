<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $income ? 'Edit Income' : 'Add Income' }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('incomes.index') }}">Incomes</a></li>
            <li class="breadcrumb-item">{{ $income ? 'Edit' : 'Add' }}</li>
        </ul>
    </div>
    @endsection

    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <form wire:submit="save" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('received_at') is-invalid @enderror" wire:model="received_at">
                                @error('received_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category') is-invalid @enderror" wire:model="category">
                                    <option value="">Select Category</option>
                                    <option value="loan_interest">Loan Interest</option>
                                    <option value="loan_repayment">Loan Repayment</option>
                                    <option value="fine">Fine</option>
                                    <option value="registration">Registration</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6" wire:ignore>
                                <label class="form-label">Member</label>
                                <select class="form-select select2 @error('member_id') is-invalid @enderror" 
                                        id="member_id"
                                        data-placeholder="Search member..."
                                        onchange="@this.set('member_id', this.value)">
                                    <option value="">Select Member (Optional)</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ $member_id == $member->id ? 'selected' : '' }}>{{ $member->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('member_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" wire:model="amount">
                                </div>
                                @error('amount') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6" wire:ignore>
                                <label class="form-label">Linked Loan</label>
                                <select class="form-select select2 @error('loan_id') is-invalid @enderror" 
                                        id="loan_id"
                                        data-placeholder="Search loan..."
                                        onchange="@this.set('loan_id', this.value)">
                                    <option value="">Select Loan (Optional)</option>
                                    @foreach($loans as $loan)
                                        <option value="{{ $loan->id }}" {{ $loan_id == $loan->id ? 'selected' : '' }}>#{{ $loan->id }} - {{ $loan->member->full_name }} (KES {{ number_format($loan->amount, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('loan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fine Type</label>
                                <input type="text" class="form-control @error('fine_type') is-invalid @enderror" wire:model="fine_type" placeholder="e.g., Late Payment">
                                @error('fine_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" wire:model="description" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save me-2"></i>{{ $income ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('incomes.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
