<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Loan {{ $loan->reference }}</h5>
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
                @if($loan->status == 'disbursed')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markRepaidModal">
                        <i class="feather-award me-2"></i><span>Mark as Repaid</span>
                    </button>
                @endif
                @if($loan->can_be_rolled_over)
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rollOverModal">
                        <i class="feather-refresh-cw me-2"></i><span>Roll Over Balance</span>
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
            <div class="card mb-3">
                <div class="card-header py-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-image bg-soft-primary text-primary d-flex align-items-center justify-content-center rounded-circle" style="width:32px;height:32px;font-size:12px;">
                            {{ strtoupper(substr($loan->member->first_name ?? 'U', 0, 1) . substr($loan->member->last_name ?? 'M', 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fs-13">{{ $loan->member->full_name ?? 'Unknown Member' }}</h6>
                            <small class="text-muted">{{ $loan->member->email ?? '' }}</small>
                        </div>
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
                        <span class="badge bg-soft-{{ $badgeClass }} text-{{ $badgeClass }} ms-auto">{{ ucfirst($loan->status) }}</span>
                    </div>
                </div>
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between border-bottom py-1"><small class="text-muted">Principal</small><small class="fw-bold">KES {{ number_format($loan->amount, 2) }}</small></div>
                    <div class="d-flex justify-content-between border-bottom py-1"><small class="text-muted">Interest ({{ $loan->interest_percent }}%)</small><small class="fw-bold">KES {{ number_format(($loan->amount * $loan->interest_percent / 100), 2) }}</small></div>
                    <div class="d-flex justify-content-between border-bottom py-1"><small class="text-muted">Total Payable</small><small class="fw-bold text-dark">KES {{ number_format($loan->amount + ($loan->amount * $loan->interest_percent / 100), 2) }}</small></div>
                    <div class="d-flex justify-content-between border-bottom py-1"><small class="text-muted">Total Paid</small><small class="fw-bold text-success">KES {{ number_format($loan->total_repaid, 2) }}</small></div>
                    <div class="d-flex justify-content-between py-1"><small class="text-muted">Balance Due</small><small class="fw-bold text-danger">KES {{ number_format($loan->balance, 2) }}</small></div>
                    @if($loan->parentLoan)
                        <div class="d-flex justify-content-between border-top py-1">
                            <small class="text-muted">Rolled over from</small>
                            <small><a href="{{ route('loans.show', $loan->parentLoan) }}">{{ $loan->parentLoan->reference }}</a></small>
                        </div>
                    @endif
                    @if($loan->rolloverLoan)
                        <div class="d-flex justify-content-between border-top py-1">
                            <small class="text-muted">Balance re-issued as</small>
                            <small><a href="{{ route('loans.show', $loan->rolloverLoan) }}">{{ $loan->rolloverLoan->reference }}</a></small>
                        </div>
                    @endif
                    <div class="row g-2 border-top pt-2 mt-1">
                        <div class="col-6">
                            <small class="text-muted d-block">Applied</small>
                            <small class="fw-bold">{{ $loan->created_at->format('d M Y') }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Disbursed</small>
                            <small class="fw-bold">{{ $loan->disbursed_at ? $loan->disbursed_at->format('d M Y') : '-' }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Due</small>
                            <small class="fw-bold {{ $loan->is_overdue ? 'text-danger' : '' }}">
                                {{ $loan->due_at ? $loan->due_at->format('d M Y') : '-' }}
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Term</small>
                            <small class="fw-bold">{{ $loan->term_months }} months</small>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $pct      = $required > 0 ? min(100, round($current / $required * 100)) : 0;
            @endphp
            <div class="card mb-3">
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
        </div>

        <!-- Repayments -->
        <div class="col-lg-8">
            <!-- Record Repayment Form -->
            @if($loan->balance > 0 && $loan->status == 'disbursed')
            <div class="card mb-3">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Record Repayment</h6>
                </div>
                <div class="card-body py-2">
                    {{-- Theme control heights differ per type; pin them so the row lines up. --}}
                    <style>
                        .repayment-form .form-control,
                        .repayment-form .form-select,
                        .repayment-form .input-group-text,
                        .repayment-form .btn { height: 46px; }
                    </style>
                    <form wire:submit="recordRepayment" class="repayment-form" novalidate>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Date</label>
                                <input type="date" class="form-control" wire:model="repaymentDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" step="0.01" class="form-control" wire:model="repaymentAmount">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Method</label>
                                <select class="form-select" wire:model="repaymentMethod">
                                    <option value="mpesa">M-PESA (to Treasurer)</option>
                                    <option value="cash">Cash</option>
                                    <option value="zimele">Zimele</option>
                                    <option value="merry_go_round">Merry-Go-Round</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Notes</label>
                                <input type="text" class="form-control" wire:model="repaymentNotes" placeholder="Optional">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1 d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="feather-plus me-1"></i>Record
                                </button>
                            </div>
                        </div>
                        @error('repaymentAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </form>
                </div>
            </div>
            @endif

            <!-- Repayment schedule: lump-sum at end of term -->
            @if(in_array($loan->status, ['approved','disbursed','repaid']) && $loan->term_months > 0)
                @php
                    $principal      = (float) $loan->amount;
                    $totalInterest  = $principal * ($loan->interest_percent / 100);
                    $totalDue       = $principal + $totalInterest;
                    $start          = $loan->disbursed_at ?: now();
                    // The recorded due date governs; fall back to the term only when it is missing.
                    $dueDate        = $loan->due_at ?: \Carbon\Carbon::parse($start)->copy()->addMonths($loan->term_months);
                    $totalRepaid    = (float) $loan->total_repaid;
                    $fullyPaid      = $totalRepaid >= $totalDue;
                    $partiallyPaid  = !$fullyPaid && $totalRepaid > 0;
                    $isOverdue      = $loan->is_overdue;
                @endphp
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0">Repayment Schedule (due after {{ $loan->term_months }} months)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th>Due Date</th>
                                        <th class="text-end">Principal</th>
                                        <th class="text-end">Interest ({{ $loan->interest_percent }}%)</th>
                                        <th class="text-end">Total Due</th>
                                        <th class="text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                        <td>{{ $dueDate->format('d M Y') }}</td>
                                        <td class="text-end">KES {{ number_format($principal, 2) }}</td>
                                        <td class="text-end">KES {{ number_format($totalInterest, 2) }}</td>
                                        <td class="text-end fw-bold">KES {{ number_format($totalDue, 2) }}</td>
                                        <td class="text-end">
                                            @if($fullyPaid)
                                                <span class="badge bg-soft-success text-success">Paid</span>
                                            @elseif($partiallyPaid)
                                                <span class="badge bg-soft-warning text-warning">Partial (KES {{ number_format($totalRepaid, 2) }})</span>
                                            @elseif($isOverdue)
                                                <span class="badge bg-soft-danger text-danger">Overdue</span>
                                            @else
                                                <span class="badge bg-soft-secondary text-secondary">Upcoming</span>
                                            @endif
                                        </td>
                                    </tr>
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
                                        <td>{{ $repayment->method ? ucfirst($repayment->method) : '-' }}</td>
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

    {{-- The theme blurs .nxl-container while a modal is open, so modals are teleported out of it.
         @teleport moves a single root element, hence the wrapper. --}}
    @teleport('body')
    <div>
    <!-- Approve Loan Modal -->
    <div class="modal fade" id="approveLoanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Loan {{ $loan->reference }}</h5>
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
                    <h5 class="modal-title">Reject Loan {{ $loan->reference }}</h5>
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

    <!-- Roll Over Balance Modal -->
    <div class="modal fade" id="rollOverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Roll Over Balance of Loan {{ $loan->reference }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        The outstanding balance will be re-issued as a new loan for
                        {{ $loan->member->full_name ?? 'this member' }}, and this loan will be closed.
                    </p>
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <small class="text-muted">Total payable</small>
                        <small class="fw-bold">KES {{ number_format($loan->total_payable, 2) }}</small>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <small class="text-muted">Already repaid</small>
                        <small class="fw-bold text-success">KES {{ number_format($loan->total_repaid, 2) }}</small>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-1 mb-3">
                        <small class="text-muted">New loan amount</small>
                        <small class="fw-bold text-danger">KES {{ number_format($loan->balance, 2) }}</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Disbursed on</label>
                            <input type="date" class="form-control" wire:model="rolloverDisbursedAt">
                            @error('rolloverDisbursedAt') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Due on</label>
                            <input type="date" class="form-control" wire:model="rolloverDueAt">
                            @error('rolloverDueAt') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        Interest of {{ $loan->interest_percent }}% will apply to the new loan.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" wire:click="rollOverBalance" data-bs-dismiss="modal">
                        <i class="feather-refresh-cw me-1"></i> Roll Over
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark as Repaid Modal -->
    <div class="modal fade" id="markRepaidModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Loan {{ $loan->reference }} as Repaid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">This will mark the loan as fully repaid without creating repayment records.</p>
                    <div class="mb-3">
                        <label class="form-label">Repaid Amount (KES)</label>
                        <div class="input-group">
                            <span class="input-group-text">KES</span>
                            <input type="number" step="0.01" class="form-control" wire:model="markRepaidAmount">
                        </div>
                        @error('markRepaidAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Principal: KES {{ number_format($loan->amount, 2) }}</span>
                        <span>Balance: KES {{ number_format($loan->balance, 2) }}</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="markAsRepaid" data-bs-dismiss="modal">
                        <i class="feather-check me-1"></i> Mark as Repaid
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    @endteleport
</div>