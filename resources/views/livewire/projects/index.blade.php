<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Projects</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Projects</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="{{ route('projects.create') }}" class="btn btn-primary"><i class="feather-plus me-2"></i>New Project</a>
    </div>
    @endsection

    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm w-auto" placeholder="Search projects..." wire:model.live.debounce.300ms="search">
            <select class="form-select form-select-sm w-auto" wire:model.live="statusFilter">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="on_hold">On Hold</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <select class="form-select form-select-sm w-auto ms-auto" wire:model.live="perPage">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th class="text-end">Income (Withdrawals)</th>
                            <th class="text-end">Spent</th>
                            <th class="text-end">Returned</th>
                            <th class="text-end">Balance</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            @php
                                $income   = (float) $project->withdrawals_sum_amount;
                                $spent    = (float) $project->expenditures_sum_amount;
                                $returned = (float) $project->cash_returns_sum_amount;
                                $balance  = $income - $spent - $returned;
                                $statusColor = match($project->status) {
                                    'completed'   => 'success',
                                    'in_progress' => 'primary',
                                    'pending'     => 'warning',
                                    'on_hold'     => 'info',
                                    'cancelled'   => 'danger',
                                    default       => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark">{{ $project->name }}</a>
                                    @if($project->start_date)
                                        <div class="small text-muted">{{ $project->start_date->format('M Y') }}</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-soft-{{ $statusColor }} text-{{ $statusColor }}">{{ str_replace('_', ' ', ucfirst($project->status)) }}</span></td>
                                <td class="text-end">KES {{ number_format($income, 2) }}</td>
                                <td class="text-end text-danger">KES {{ number_format($spent, 2) }}</td>
                                <td class="text-end text-success">KES {{ number_format($returned, 2) }}</td>
                                <td class="text-end fw-bold {{ $balance >= 0 ? 'text-primary' : 'text-danger' }}">KES {{ number_format($balance, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('projects.show', $project) }}" class="text-info me-2" title="View"><i class="feather-eye"></i></a>
                                    <a href="{{ route('projects.edit', $project) }}" class="text-primary me-2" title="Edit"><i class="feather-edit-2"></i></a>
                                    <a href="javascript:void(0)" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project and unlink its transactions?" class="text-danger" title="Delete"><i class="feather-trash-2"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No projects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $projects->links() }}</div>
    </div>
</div>
