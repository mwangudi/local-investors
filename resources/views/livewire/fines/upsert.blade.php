<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $fine ? 'Edit Fine' : 'Record Fine' }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('fines.index') }}">Fines</a></li>
            <li class="breadcrumb-item">{{ $fine ? 'Edit' : 'Create' }}</li>
        </ul>
    </div>
    @endsection

    <div class="card">
        <div class="card-body">
            <form wire:submit="save" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Member</label>
                        <select class="form-select @error('member_id') is-invalid @enderror" wire:model="member_id">
                            <option value="">Select member</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                            @endforeach
                        </select>
                        @error('member_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fine Type</label>
                        <select class="form-select @error('fine_type') is-invalid @enderror" wire:model.live="fine_type">
                            <option value="late_attendance">Late attendance (KES 100)</option>
                            <option value="absent">Full absence (KES 200)</option>
                            <option value="late_contribution">Late contribution</option>
                            <option value="late_repayment">Late loan repayment</option>
                            <option value="other">Other</option>
                        </select>
                        @error('fine_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount (KES)</label>
                        <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" wire:model="amount">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control @error('received_at') is-invalid @enderror" wire:model="received_at">
                        @error('received_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description (optional)</label>
                        <textarea class="form-control" rows="2" wire:model="description"></textarea>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="feather-save me-2"></i>Save</button>
                    <a href="{{ route('fines.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
