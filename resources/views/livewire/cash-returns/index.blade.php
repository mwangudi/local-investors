<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Cash Returns</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Cash Returns</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="{{ route('cash-returns.create') }}" class="btn btn-primary">
            <i class="feather-plus me-2"></i><span>Add Cash Return</span>
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
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cashReturns as $cashReturn)
                                    <tr>
                                        <td>{{ $cashReturn->returned_at ? $cashReturn->returned_at->format('Y-m-d') : '-' }}</td>
                                        <td>{{ Str::limit($cashReturn->description, 50) }}</td>
                                        <td class="text-end fw-bold">KES {{ number_format($cashReturn->amount, 2) }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('cash-returns.edit', $cashReturn) }}" class="avatar-text avatar-md">
                                                    <i class="feather-edit-3"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="avatar-text avatar-md text-danger" 
                                                   wire:click="delete({{ $cashReturn->id }})"
                                                   wire:confirm="Delete this cash return?">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No cash returns recorded</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($cashReturns->hasPages())
                        <div class="p-3 border-top">{{ $cashReturns->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
