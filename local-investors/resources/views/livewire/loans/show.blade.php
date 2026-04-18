<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Loan Details #{{ $loan->id }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('loans.index') }}">Loans</a></li>
            <li class="breadcrumb-item">Details</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                @if($loan->status == 'applied')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveLoanModal">
                        <i class="feather-check me-2"></i><span>Approve</span>
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectLoanModal">
                        <i class="feather-x me-2"></i><span>Reject</span>
                    </button>
                @endif
                @if($loan->status == 'approved')
                    <button wire:click="disburse" wire:confirm="Are you sure you want to disburse this loan?" class="btn btn-success">
                        <i class="feather-check-circle me-2"></i>
                        <span>Disburse Loan</span>
                    </button>
                @endif
                <a href="{{ route('loans.edit', $loan) }}" class="btn btn-light-brand">
                    <i class="feather-edit me-2"></i>
                    <span>Edit</span>
                </a>
                <a href="{{ route('loans.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back to List</span>
                </a>
            </div>
        </div>
    </div>
    @endsection

    <div class="row">
        <!-- Loan Details -->
        <div class="col-lg-4">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0">Loan Summary</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                         <div class="avatar-image avatar-lg bg-soft-primary text-primary d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3">
                            {{ strtoupper(substr($loan->member->first_name ?? 'U', 0, 1) . substr($loan->member->last_name ?? 'M', 0, 1)) }}
                        </div>
                        <h5 class="mb-1">{{ $loan->member->full_name ?? 'Unknown Member' }}</h5>
                        <p class="text-muted">{{ $loan->member->email ?? '' }}</p>
                    </div>

                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Status</span>
                         @php
                            $badgeClass = match($loan->status) {
                                'applied' => 'warning',
                                'approved' => 'info',
                                'disbursed' => 'primary',
                                'repaid' => 'success',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-soft-{{ $badgeClass }} text-{{ $badgeClass }}">{{ ucfirst($loan->status) }}</span>
                    </div>

                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Principal</span>
                        <span class="fw-bold">KES {{ number_format($loan->amount, 2) }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Interest ({{ $loan->interest_percent }}%)</span>
                        <span class="fw-bold">KES {{ number_format(($loan->amount * $loan->interest_percent / 100), 2) }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Total Payable</span>
                        <span class="fw-bold text-dark">KES {{ number_format($loan->amount + ($loan->amount * $loan->interest_percent / 100), 2) }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Total Paid</span>
                        <span class="fw-bold text-success">KES {{ number_format($loan->total_repaid, 2) }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between pb-2">
                        <span class="text-muted">Balance Due</span>
                        <span class="fw-bold text-danger">KES {{ number_format($loan->balance, 2) }}</span>
                    </div>
                </div>
            </div>

                $pct      = $required > 0 ? min(100, round($current / $required * 100)) : 0;
            @endphp
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Approvals ({{ $current }}/{{ $required }})</h6></div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $pct }}%;"></div>
                    </div>
                    @forelse($approvals as $a)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="small"><i class="feather-check-circle text-success me-1"></i>{{ $a->member->full_name ?? 'Committee' }}</span>
                            <span class="small text-muted">{{ $a->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No approvals yet.</p>
                    @endforelse
                </div>
            </div>
            <div class="card">
                 <div class="card-body">
                    <h6 class="mb-3">Dates</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Applied</small>
                            <span class="fw-bold">{{ $loan->created_at->format('Y-m-d') }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Disbursed</small>
                            <span class="fw-bold">{{ $loan->disbursed_at ? $loan->disbursed_at->format('Y-m-d') : '-' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Due Date</small>
                            <span class="fw-bold">{{ $loan->due_at ? $loan->due_at->format('Y-m-d') : '-' }}</span>
                        </div>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Repayments -->
        <div class="col-lg-8">
            <!-- Record Repayment Form -->
            @if($loan->balance > 0 && $loan->status == 'disbursed')
            <div class="card mb-4 stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0">Record Repayment</h5>
                </div>
                <div class="card-body">
                    <form wire:submit="recordRepayment" novalidate>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" wire:model="repaymentDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control" wire:model="repaymentAmount">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Method</label>
                                <select class="form-select" wire:model="repaymentMethod">
                                    <option value="cash">Cash</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="feather-plus me-1"></i> Record
                                </button>
                            </div>
                            <div class="col-12">
                                @error('repaymentAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Amortisation schedule -->
            @if(in_array($loan->status, ['approved','disbursed','repaid']) && $loan->term_months > 0)
                @php
                    $principal = (float) $loan->amount;
                    $totalInterest = $principal * ($loan->interest_percent / 100);
                    $totalDue = $principal + $totalInterest;
                    $monthly  = round($totalDue / max(1, $loan->term_months), 2);
                    $start    = $loan->disbursed_at ?: now();
                    $remainingPaid = (float) $loan->total_repaid;
                @endphp
                <div class="card mb-4 stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Repayment Schedule ({{ $loan->term_months }} months)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Installment</th>
                                        <th class="text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 1; $i <= $loan->term_months; $i++)
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($start)->copy()->addMonths($i);
                                            $coveredByPaid = $remainingPaid >= $monthly;
                                            $partial = !$coveredByPaid && $remainingPaid > 0;
                                            $paidThis = min($remainingPaid, $monthly);
                                            $remainingPaid -= $paidThis;
                                            $isOverdue = $dueDate->isPast() && !$coveredByPaid;
                                        @endphp
                                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                            <td>{{ $i }}</td>
                                            <td>{{ $dueDate->format('Y-m-d') }}</td>
                                            <td class="text-end">KES {{ number_format($monthly, 2) }}</td>
                                            <td class="text-end">
                                                @if($coveredByPaid)
                                                    <span class="badge bg-soft-success text-success">Paid</span>
                                                @elseif($partial)
                                                    <span class="badge bg-soft-warning text-warning">Partial (KES {{ number_format($paidThis, 2) }})</span>
                                                @elseif($isOverdue)
                                                    <span class="badge bg-soft-danger text-danger">Overdue</span>
                                                @else
                                                    <span class="badge bg-soft-secondary text-secondary">Upcoming</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- History -->
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0">Repayment History</h5>
                </div><div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Notes</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($repayments as $repayment)
                                    <tr>
                                        <td>{{ $repayment->paid_at->format('Y-m-d') }}</td>
                                        <td>{{ ucfirst($repayment->payment_method) }}</td>
                                        <td>{{ $repayment->notes ?? '-' }}</td>
                                        <td class="text-end fw-bold">KES {{ number_format($repayment->amount, 2) }}</td>
                                        <td class="text-end">
                                            <a href="javascript:void(0)" class="text-danger" 
                                               wire:click="deleteRepayment({{ $repayment->id }})"
                                               wire:confirm="Delete this repayment?">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No repayments recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Loan Modal -->
    <div class="modal fade" id="approveLoanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Loan #{{ $loan->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Approve the loan of <strong>KES {{ number_format($loan->amount, 2) }}</strong> for {{ $loan->member->full_name ?? 'member' }}?</p>
                    <label class="form-label">Remark (optional)</label>
                    <textarea class="form-control" rows="3" wire:model.defer="approvalRemark" placeholder="e.g. Approved at committee meeting"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="approve" data-bs-dismiss="modal">
                        <i class="feather-check me-1"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Loan Modal -->
    <div class="modal fade" id="rejectLoanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Loan #{{ $loan->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Reject this loan application?</p>
                    <label class="form-label">Reason</label>
                    <textarea class="form-control" rows="3" wire:model.defer="approvalRemark" placeholder="Reason for rejection"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="reject" data-bs-dismiss="modal">
                        <i class="feather-x me-1"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>