<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $contribution ? 'Edit Contribution' : 'Add Contribution' }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contributions.index') }}">Contributions</a></li>
            <li class="breadcrumb-item">{{ $contribution ? 'Edit' : 'Add' }}</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="{{ route('contributions.index') }}" class="btn btn-light-brand">
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
                    <h5 class="card-title mb-0">Contribution Details</h5>
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

                            <!-- Date -->
                            <div class="col-md-6">
                                <label for="paid_at" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('paid_at') is-invalid @enderror" 
                                       id="paid_at" wire:model="paid_at">
                                @error('paid_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12"><hr></div>

                            <!-- Shares -->
                            <div class="col-md-3">
                                <label for="shares" class="form-label">Shares <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control @error('shares') is-invalid @enderror" 
                                           id="shares" wire:model="shares">
                                    @error('shares')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Welfare -->
                            <div class="col-md-3">
                                <label for="welfare" class="form-label">Welfare <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control @error('welfare') is-invalid @enderror" 
                                           id="welfare" wire:model="welfare">
                                    @error('welfare')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Merry Go Round -->
                            <div class="col-md-3">
                                <label for="merry_go_round" class="form-label">Merry Go Round <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control @error('merry_go_round') is-invalid @enderror" 
                                           id="merry_go_round" wire:model="merry_go_round">
                                    @error('merry_go_round')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Penalty -->
                            <div class="col-md-3">
                                <label for="penalty" class="form-label">Penalty <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control @error('penalty') is-invalid @enderror" 
                                           id="penalty" wire:model="penalty">
                                    @error('penalty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Penalty Type -->
                            @if($penalty > 0)
                            <div class="col-md-6">
                                <label for="penalty_type" class="form-label">Penalty Type</label>
                                <input type="text" class="form-control @error('penalty_type') is-invalid @enderror" 
                                       id="penalty_type" wire:model="penalty_type" placeholder="Reason for penalty">
                                @error('penalty_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            <!-- Notes -->
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" wire:model="notes" rows="2" placeholder="Optional notes"></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save me-2"></i>{{ $contribution ? 'Update Contribution' : 'Create Contribution' }}
                            </button>
                            <a href="{{ route('contributions.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
