<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Fines</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Fines</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="{{ route('fines.create') }}" class="btn btn-primary"><i class="feather-plus me-2"></i>New Fine</a>
    </div>
    @endsection

    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm w-auto" placeholder="Search member or type..." wire:model.live.debounce.300ms="search">
            <select class="form-select form-select-sm w-auto ms-auto" wire:model.live="perPage">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fines as $fine)
                            <tr>
                                <td>{{ $fine->received_at?->format('Y-m-d') }}</td>
                                <td>{{ $fine->member?->full_name ?? '-' }}</td>
                                <td><span class="badge bg-soft-warning text-warning">{{ $fine->fine_type }}</span></td>
                                <td>{{ $fine->description ?? '-' }}</td>
                                <td class="text-end fw-bold">KES {{ number_format($fine->amount, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('fines.edit', $fine) }}" class="text-primary me-2"><i class="feather-edit-2"></i></a>
                                    <a href="javascript:void(0)" wire:click="delete({{ $fine->id }})" wire:confirm="Delete this fine?" class="text-danger"><i class="feather-trash-2"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No fines recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $fines->links() }}</div>
    </div>
</div>
