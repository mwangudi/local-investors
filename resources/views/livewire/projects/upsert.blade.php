<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $project ? 'Edit Project' : 'New Project' }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item">{{ $project ? 'Edit' : 'Create' }}</li>
        </ul>
    </div>
    @endsection

    <div class="card">
        <div class="card-body">
            <form wire:submit="save" novalidate>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Project Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Nanyuki Trip">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" wire:model="start_date">
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" wire:model="due_date">
                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Budget (KES)</label>
                        <input type="number" step="0.01" class="form-control @error('budget') is-invalid @enderror" wire:model="budget" placeholder="Optional">
                        @error('budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" wire:model="description" placeholder="Project details..."></textarea>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="feather-save me-2"></i>Save</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
