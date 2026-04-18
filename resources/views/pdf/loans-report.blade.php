<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loans Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 20px 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f59e0b;
        }
        .header img {
            max-height: 60px;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #92400e;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .report-title {
            font-size: 14px;
            color: #92400e;
            text-align: center;
            margin-bottom: 15px;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background: #fffbeb;
            border-radius: 4px;
            border: 1px solid #fcd34d;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }
        .summary-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #92400e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #f59e0b;
            color: white;
            padding: 5px 4px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            padding: 4px;
            border-bottom: 1px solid #fcd34d;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background: #fffbeb;
        }
        .status-applied { color: #805ad5; }
        .status-approved { color: #3182ce; }
        .status-disbursed { color: #38a169; }
        .status-repaid { color: #718096; }
        .status-rejected { color: #e53e3e; }
        .status-overdue { color: #dc2626; font-weight: bold; }
        .status-ongoing { color: #16a34a; }
        .text-right { text-align: right; }
        th.text-right { text-align: right; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #92400e;
            padding: 10px;
            border-top: 2px solid #f59e0b;
            background: #fffbeb;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo">
    </div>

    <h2 class="report-title">Loans Report</h2>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Loans</div>
                <div class="summary-value">{{ $loans->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Disbursed</div>
                <div class="summary-value">KES {{ number_format($loans->sum('amount'), 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Interest</div>
                <div class="summary-value">KES {{ number_format($loans->sum('standard_interest'), 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Repaid</div>
                <div class="summary-value">KES {{ number_format($loans->sum('total_repaid'), 2) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Member</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Interest</th>
                <th class="text-right">Total Payable</th>
                <th class="text-right">Repaid</th>
                <th class="text-right">Balance</th>
                <th>Status</th>
                <th>Applied</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $index => $loan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $loan->member?->full_name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($loan->amount, 2) }}</td>
                <td class="text-right">{{ number_format($loan->standard_interest, 2) }}</td>
                <td class="text-right">{{ number_format($loan->total_payable, 2) }}</td>
                <td class="text-right">{{ number_format($loan->total_repaid, 2) }}</td>
                <td class="text-right">{{ number_format($loan->balance, 2) }}</td>
                <td>
                    @if($loan->balance <= 0)
                        <span class="status-repaid">Paid</span>
                    @elseif($loan->status === 'disbursed' && !$loan->repaid)
                        @if($loan->is_overdue)
                            <span class="status-overdue">Overdue</span>
                        @else
                            <span class="status-ongoing">Ongoing</span>
                        @endif
                    @else
                        <span class="status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
                    @endif
                </td>
                <td>{{ $loan->created_at?->format('Y-m-d') ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Loans Report - Generated on {{ now()->format('d M Y, h:i A') }} | {{ config('app.name') }} - Confidential
    </div>
</body>
</html>
