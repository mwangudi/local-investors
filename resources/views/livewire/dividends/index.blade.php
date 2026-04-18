<div>
    @section('pageHeader')
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Dividends / Share-out</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Dividends</li>
        </ul>
    </div>
    @endsection

    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Share-out Calculator</h6></div>
        <div class="card-body">
            <form wire:submit="calculate" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <input type="number" min="2000" max="2100" class="form-control @error('year') is-invalid @enderror" wire:model="year">
                        @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Surplus to share (KES)</label>
                        <input type="number" step="0.01" class="form-control @error('surplus') is-invalid @enderror" wire:model="surplus" placeholder="e.g. 100000">
                        @error('surplus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Basis</label>
                        <select class="form-select" wire:model="basis">
                            <option value="shares">Shares only</option>
                            <option value="total">Shares + Welfare + Merry-go-round</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-play me-2"></i>Calculate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($calculated && count($results))
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">Results — {{ $year }} ({{ count($results) }} members)</h6>
                <div class="d-flex gap-2">
                    <button wire:click="exportCsv" class="btn btn-sm btn-light-brand"><i class="feather-download me-1"></i>CSV</button>
                    <button onclick="window.print()" class="btn btn-sm btn-light-brand"><i class="feather-printer me-1"></i>Print</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th class="text-end">Contribution ({{ $basis }})</th>
                                <th class="text-end">%</th>
                                <th class="text-end">Dividend (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $r)
                                <tr>
                                    <td>{{ $r['name'] }}</td>
                                    <td class="text-end">{{ number_format($r['score'], 2) }}</td>
                                    <td class="text-end">{{ number_format($r['percent'], 2) }}%</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($r['dividend'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-end">{{ number_format(collect($results)->sum('score'), 2) }}</td>
                                <td class="text-end">100.00%</td>
                                <td class="text-end text-success">{{ number_format(collect($results)->sum('dividend'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @elseif($calculated)
        <div class="alert alert-info">No contributions recorded for {{ $year }}.</div>
    @endif
</div>
