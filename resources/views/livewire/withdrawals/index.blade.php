<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Withdrawals</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Withdrawals</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="{{ route('withdrawals.create') }}" class="btn btn-primary">
            <i class="feather-plus me-2"></i><span>Add Withdrawal</span>
        </a>
    </div>
    @endsection

    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-body p-0">
                    <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span>Show</span>
                            <select class="form-select form-select-sm" style="width: auto;" wire:model.live="perPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span>Search:</span>
                            <input type="text" class="form-control form-control-sm" style="width: 200px;" 
                                   wire:model.live.debounce.300ms="search" placeholder="Search...">
                        </div>
                    </div>
                    

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Member</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdrawal)
                                    <tr>
                                        <td>{{ $withdrawal->withdrawn_at ? $withdrawal->withdrawn_at->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $withdrawal->member ? $withdrawal->member->full_name : '-' }}</td>
                                        <td>{{ Str::limit($withdrawal->description, 40) }}</td>
                                        <td class="text-end fw-bold">KES {{ number_format($withdrawal->amount, 2) }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('withdrawals.edit', $withdrawal) }}" class="avatar-text avatar-md">
                                                    <i class="feather-edit-3"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="avatar-text avatar-md text-danger" 
                                                   wire:click="delete({{ $withdrawal->id }})"
                                                   wire:confirm="Delete this withdrawal?">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No withdrawals recorded</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($withdrawals->hasPages())
                        <div class="p-3 border-top">{{ $withdrawals->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
