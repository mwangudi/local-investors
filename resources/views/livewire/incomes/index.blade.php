<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Incomes</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Incomes</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="{{ route('incomes.create') }}" class="btn btn-primary">
            <i class="feather-plus me-2"></i><span>Add Income</span>
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
                                    <th>Category</th>
                                    <th>Member</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomes as $income)
                                    <tr>
                                        <td>{{ $income->received_at ? $income->received_at->format('Y-m-d') : '-' }}</td>
                                        <td><span class="badge bg-soft-info text-info">{{ $income->category ?? '-' }}</span></td>
                                        <td>{{ $income->member ? $income->member->full_name : '-' }}</td>
                                        <td>{{ Str::limit($income->description, 30) }}</td>
                                        <td class="text-end fw-bold">KES {{ number_format($income->amount, 2) }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('incomes.edit', $income) }}" class="avatar-text avatar-md">
                                                    <i class="feather-edit-3"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="avatar-text avatar-md text-danger" 
                                                   wire:click="delete({{ $income->id }})"
                                                   wire:confirm="Delete this income record?">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No incomes recorded</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($incomes->hasPages())
                        <div class="p-3 border-top">{{ $incomes->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
