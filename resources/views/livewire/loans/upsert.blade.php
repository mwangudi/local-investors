<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $loan ? 'Edit Loan' : 'Apply Loan' }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
            <li class="breadcrumb-item">{{ $loan ? 'Edit' : 'Apply' }}</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="{{ route('loans.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back to List</span>
                </a>
            </div>
        </div>
    </div>
    @endsection

    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0">Loan Application Details</h5>
                </div>
                <div class="card-body">
                    <form wire:submit="save" novalidate>
                        <div class="row g-3">
                            <!-- Member -->
                            <div class="col-md-6" wire:ignore>
                                <label for="member_id" class="form-label">Member <span class="text-danger">*</span></label>
                                <select class="form-select select2 @error('member_id') is-invalid @enderror" 
                                        id="member_id" 
                                        data-placeholder="Search member..."
                                        onchange="@this.set('member_id', this.value)">
                                    <option value="">Select Member</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ $member_id == $member->id ? 'selected' : '' }}>{{ $member->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('member_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model.live="status">
                                    <option value="applied">Applied</option>
                                    <option value="approved">Approved</option>
                                    <option value="disbursed">Disbursed</option>
                                    <option value="repaid">Repaid</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12"><hr></div>

                            <!-- Amount -->
                            <div class="col-md-4">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" wire:model="amount">
                                </div>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Interest Percent -->
                            <div class="col-md-4">
                                <label for="interest_percent" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('interest_percent') is-invalid @enderror" 
                                       id="interest_percent" wire:model="interest_percent">
                                @error('interest_percent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Term Months -->
                            <div class="col-md-4">
                                <label for="term_months" class="form-label">Term (Months) <span class="text-danger">*</span></label>
                                <input type="number" step="1" class="form-control @error('term_months') is-invalid @enderror" 
                                       id="term_months" wire:model="term_months">
                                @error('term_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Disbursed Date (Conditional) -->
                            @if(in_array($status, ['disbursed', 'repaid']))
                            <div class="col-md-6">
                                <label for="disbursed_at" class="form-label">Disbursement Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('disbursed_at') is-invalid @enderror" 
                                       id="disbursed_at" wire:model="disbursed_at">
                                @error('disbursed_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save me-2"></i>{{ $loan ? 'Update Loan' : 'Apply Loan' }}
                            </button>
                            <a href="{{ route('loans.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
