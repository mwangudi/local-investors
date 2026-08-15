<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Reports</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Reports</li>
        </ul>
    </div>
    @endsection

    <div class="row">
        <!-- Report Controls -->
        <div class="col-lg-12 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Generate Report</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3" wire:ignore>
                            <label class="form-label">Report Type</label>
                            <select class="form-select select2" 
                                    id="reportType"
                                    data-placeholder="Select report type..."
                                    onchange="@this.set('reportType', this.value)">
                                <option value="members" {{ $reportType === 'members' ? 'selected' : '' }}>Members Report</option>
                                <option value="contributions" {{ $reportType === 'contributions' ? 'selected' : '' }}>Contributions Report</option>
                                <option value="loans" {{ $reportType === 'loans' ? 'selected' : '' }}>Loans Report</option>
                                <option value="financial_summary" {{ $reportType === 'financial_summary' ? 'selected' : '' }}>Financial Summary</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" wire:model="startDate">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" wire:model="endDate">
                        </div>
                        @if($reportType === 'contributions')
                        <div class="col-md-2">
                            <label class="form-label">Year</label>
                            <select class="form-select" wire:model="selectedYear">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3" wire:ignore>
                            <label class="form-label">Select Months <span class="text-muted fs-11">(empty = whole year)</span></label>
                            <select class="form-select select2" 
                                    id="selectedMonths"
                                    multiple
                                    data-placeholder="Select months...">
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $idx => $monthName)
                                    <option value="{{ $idx + 1 }}" {{ in_array($idx + 1, $selectedMonths) ? 'selected' : '' }}>{{ $monthName }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        @if($reportType === 'loans')
                        <div class="col-md-2">
                            <label class="form-label">Loan Status</label>
                            <select class="form-select" wire:model="loanStatus">
                                <option value="">All Statuses</option>
                                <option value="applied">Applied</option>
                                <option value="approved">Approved</option>
                                <option value="disbursed">Disbursed</option>
                                <option value="repaid">Repaid</option>
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <button wire:click="generateReport" class="btn btn-primary w-100">
                                <i class="feather-file-text me-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Results -->
        @if($reportGenerated)
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        @switch($reportType)
                            @case('members') Members Report @break
                            @case('contributions') Contributions Report ({{ $selectedYear }} - {{ empty($selectedMonths) ? 'All months' : collect($selectedMonths)->sort()->map(fn($m) => \Carbon\Carbon::create()->month($m)->format('M'))->implode(', ') }}) @break
                            @case('loans') Loans Report @break
                            @case('financial_summary') Financial Summary ({{ $startDate }} to {{ $endDate }}) @break
                        @endswitch
                    </h5>
                    <div class="d-flex gap-2">
                        <button wire:click="exportPdf" class="btn btn-sm btn-light-brand">
                            <i class="feather-download me-1"></i>Download PDF
                        </button>
                        <button wire:click="exportCsv" class="btn btn-sm btn-light-brand">
                            <i class="feather-file me-1"></i>CSV
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @switch($reportType)
                        @case('members')
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Joined</th>
                                            <th>Status</th>
                                            <th class="text-end">Total Contributions</th>
                                            <th class="text-end">Active Loans</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportData as $row)
                                            <tr>
                                                <td>{{ $row['name'] }}</td>
                                                <td>{{ $row['email'] ?? '-' }}</td>
                                                <td>{{ $row['phone'] ?? '-' }}</td>
                                                <td>{{ $row['joined'] }}</td>
                                                <td>
                                                    <span class="badge bg-soft-{{ $row['status'] == 'Active' ? 'success' : 'secondary' }} text-{{ $row['status'] == 'Active' ? 'success' : 'secondary' }}">
                                                        {{ $row['status'] }}
                                                    </span>
                                                </td>
                                                <td class="text-end">KES {{ number_format($row['total_contributions'], 2) }}</td>
                                                <td class="text-end">{{ $row['active_loans'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center py-4 text-muted">No data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @break

                        @case('contributions')
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>For Month</th>
                                            <th>Paid On</th>
                                            <th>Member</th>
                                            <th class="text-end">Shares</th>
                                            <th class="text-end">Welfare</th>
                                            <th class="text-end">Table Banking</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalShares = 0; $totalWelfare = 0; $totalMerry = 0; @endphp
                                        @forelse($reportData as $row)
                                            @php
                                                $totalShares += $row['shares'];
                                                $totalWelfare += $row['welfare'];
                                                $totalMerry += $row['merry_go_round'];
                                            @endphp
                                            <tr>
                                                <td>{{ $row['for_month'] }}</td>
                                                <td>{{ $row['date'] }}</td>
                                                <td>{{ $row['member'] }}</td>
                                                <td class="text-end">KES {{ number_format($row['shares'], 2) }}</td>
                                                <td class="text-end">KES {{ number_format($row['welfare'], 2) }}</td>
                                                <td class="text-end">KES {{ number_format($row['merry_go_round'], 2) }}</td>
                                                <td class="text-end fw-bold">KES {{ number_format($row['total'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center py-4 text-muted">No contributions in period</td></tr>
                                        @endforelse
                                    </tbody>
                                    @if(count($reportData) > 0)
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <td colspan="3">Total</td>
                                            <td class="text-end">KES {{ number_format($totalShares, 2) }}</td>
                                            <td class="text-end">KES {{ number_format($totalWelfare, 2) }}</td>
                                            <td class="text-end">KES {{ number_format($totalMerry, 2) }}</td>
                                            <td class="text-end">KES {{ number_format($totalShares + $totalWelfare + $totalMerry, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                            @break

                        @case('loans')
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Member</th>
                                            <th class="text-end">Amount</th>
                                            <th>Interest</th>
                                            <th>Status</th>
                                            <th>Disbursed</th>
                                            <th>Due</th>
                                            <th class="text-end">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportData as $row)
                                            <tr>
                                                <td>{{ $row['id'] }}</td>
                                                <td>{{ $row['member'] }}</td>
                                                <td class="text-end">KES {{ number_format($row['amount'], 2) }}</td>
                                                <td>{{ $row['interest'] }}%</td>
                                                <td>
                                                    @php
                                                        $statusClass = match(strtolower($row['status'])) {
                                                            'applied' => 'warning',
                                                            'approved' => 'info',
                                                            'disbursed' => 'primary',
                                                            'repaid' => 'success',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge bg-soft-{{ $statusClass }} text-{{ $statusClass }}">{{ $row['status'] }}</span>
                                                </td>
                                                <td>{{ $row['disbursed'] }}</td>
                                                <td>{{ $row['due'] }}</td>
                                                <td class="text-end fw-bold">KES {{ number_format($row['balance'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center py-4 text-muted">No loans in period</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @break

                        @case('financial_summary')
                            <div class="p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card bg-soft-primary mb-0">
                                            <div class="card-body">
                                                <h6 class="text-primary">Contributions</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Shares:</span>
                                                    <strong>KES {{ number_format($reportData['contributions']['shares'] ?? 0, 2) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Welfare:</span>
                                                    <strong>KES {{ number_format($reportData['contributions']['welfare'] ?? 0, 2) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-soft-success mb-0">
                                            <div class="card-body">
                                                <h6 class="text-success">Loans Disbursed</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Count:</span>
                                                    <strong>{{ $reportData['loans']['disbursed_count'] ?? 0 }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Amount:</span>
                                                    <strong>KES {{ number_format($reportData['loans']['disbursed_amount'] ?? 0, 2) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-soft-warning mb-0">
                                            <div class="card-body text-center">
                                                <h6 class="text-warning">Active Loan Balance</h6>
                                                <h4 class="mb-0">KES {{ number_format($reportData['loans']['active_balance'] ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-soft-info mb-0">
                                            <div class="card-body text-center">
                                                <h6 class="text-info">Total Incomes</h6>
                                                <h4 class="mb-0">KES {{ number_format($reportData['incomes'] ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-soft-danger mb-0">
                                            <div class="card-body text-center">
                                                <h6 class="text-danger">Total Expenditures</h6>
                                                <h4 class="mb-0">KES {{ number_format($reportData['expenditures'] ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        function initMonthsSelect2() {
            var $el = $('#selectedMonths');
            if ($el.length && !$el.data('select2')) {
                $el.select2({
                    placeholder: 'Select months...',
                    closeOnSelect: false,
                    width: '100%'
                }).on('change', function () {
                    var values = $(this).val() || [];
                    @this.set('selectedMonths', values.map(Number));
                });
            }
        }

        // This script runs before Livewire's own scripts, so the hook must be registered in here.
        document.addEventListener('livewire:initialized', function () {
            initMonthsSelect2();

            Livewire.hook('morph.updated', () => {
                setTimeout(initMonthsSelect2, 50);
            });
        });
    </script>
</div>
