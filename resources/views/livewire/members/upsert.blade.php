<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $member ? 'Edit Member' : 'Add Member' }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            <li class="breadcrumb-item">{{ $member ? 'Edit' : 'Add' }}</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="{{ route('members.index') }}" class="btn btn-light-brand">
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
                    <h5 class="card-title mb-0">Member Information</h5>
                </div>
                <div class="card-body">
                    <form wire:submit="save" novalidate>
                        <div class="row g-3">
                            <!-- First Name -->
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" wire:model="first_name" placeholder="Enter first name">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                       id="last_name" wire:model="last_name" placeholder="Enter last name">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" wire:model="email" placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" wire:model="phone" placeholder="Enter phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Join Date -->
                            <div class="col-md-6">
                                <label for="join_date" class="form-label">Join Date</label>
                                <input type="date" class="form-control @error('join_date') is-invalid @enderror" 
                                       id="join_date" wire:model="join_date">
                                @error('join_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notification Preference -->
                            <div class="col-md-6">
                                <label for="notification_preference" class="form-label">Notification Preference <span class="text-danger">*</span></label>
                                <select class="form-select @error('notification_preference') is-invalid @enderror" id="notification_preference" wire:model="notification_preference">
                                    <option value="sms">SMS</option>
                                    <option value="email">Email</option>
                                    <option value="both">Both</option>
                                    <option value="none">None</option>
                                </select>
                                @error('notification_preference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status (Active) -->
                            <div class="col-12">
                                <div class="form-check form-switch custom-switch-v1">
                                    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" 
                                           id="is_active" wire:model="is_active">
                                    <label class="form-check-label" for="is_active">Active Member</label>
                                </div>
                                @error('is_active')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save me-2"></i>{{ $member ? 'Update Member' : 'Create Member' }}
                            </button>
                            <a href="{{ route('members.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
