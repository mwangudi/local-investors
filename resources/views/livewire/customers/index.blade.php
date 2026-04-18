<div>
    <!-- [ page-header ] start -->
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Customers</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Customers</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                    <i class="feather-bar-chart"></i>
                </a>
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                        <i class="feather-filter"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', '')">
                            <i class="feather-eye me-3"></i>
                            <span>All</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', 'active')">
                            <i class="feather-user-check me-3"></i>
                            <span>Active</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', 'inactive')">
                            <i class="feather-user-minus me-3"></i>
                            <span>Inactive</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" wire:click="$set('statusFilter', 'pending')">
                            <i class="feather-clock me-3"></i>
                            <span>Pending</span>
                        </a>
                    </div>
                </div>
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                        <i class="feather-paperclip"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-pdf me-3"></i>
                            <span>PDF</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-csv me-3"></i>
                            <span>CSV</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-xml me-3"></i>
                            <span>XML</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-printer me-3"></i>
                            <span>Print</span>
                        </a>
                    </div>
                </div>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    <span>Create Customer</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
    @endsection
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-body p-0">
                    <!-- Search and Per Page -->
                    <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span>Show</span>
                            <select class="form-select form-select-sm" style="width: auto;" wire:model.live="perPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>entries</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span>Search:</span>
                            <input type="text" class="form-control form-control-sm" style="width: 200px;" 
                                   wire:model.live.debounce.300ms="search" placeholder="Search customers...">
                        </div>
                    </div>
                    

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="customerList">
                            <thead>
                                <tr>
                                    <th class="wd-30">
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" class="custom-control-input" id="checkAllCustomer">
                                            <label class="custom-control-label" for="checkAllCustomer"></label>
                                        </div>
                                    </th>
                                    <th wire:click="sortBy('name')" style="cursor: pointer;">
                                        Customer
                                        @if($sortField === 'name')
                                            <i class="feather-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('email')" style="cursor: pointer;">
                                        Email
                                        @if($sortField === 'email')
                                            <i class="feather-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th>Company</th>
                                    <th wire:click="sortBy('phone')" style="cursor: pointer;">
                                        Phone
                                        @if($sortField === 'phone')
                                            <i class="feather-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                        Date
                                        @if($sortField === 'created_at')
                                            <i class="feather-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                    <tr class="single-item">
                                        <td>
                                            <div class="item-checkbox ms-1">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input checkbox" id="checkBox_{{ $customer->id }}">
                                                    <label class="custom-control-label" for="checkBox_{{ $customer->id }}"></label>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('customers.edit', $customer) }}" class="hstack gap-3">
                                                <div class="avatar-image avatar-md bg-primary text-white d-flex align-items-center justify-content-center rounded">
                                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="text-truncate-1-line">{{ $customer->name }}</span>
                                                </div>
                                            </a>
                                        </td>
                                        <td><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></td>
                                        <td>{{ $customer->company ?? '-' }}</td>
                                        <td>
                                            @if($customer->phone)
                                                <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $customer->created_at->format('Y-m-d, h:iA') }}</td>
                                        <td>
                                            <x-status-badge :status="$customer->status" />
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('customers.edit', $customer) }}" class="avatar-text avatar-md" title="View/Edit">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('customers.edit', $customer) }}">
                                                                <i class="feather feather-edit-3 me-3"></i>
                                                                <span>Edit</span>
                                                            </a>
                                                        </li>
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="javascript:void(0)" 
                                                               wire:click="delete({{ $customer->id }})"
                                                               wire:confirm="Are you sure you want to delete this customer?">
                                                                <i class="feather feather-trash-2 me-3"></i>
                                                                <span>Delete</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="feather-users fs-1 mb-3 d-block"></i>
                                            No customers found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($customers->hasPages())
                        <div class="p-3 border-top">
                            {{ $customers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
