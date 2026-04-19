<?php

namespace App\Livewire\Reports;

use App\Models\Member;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Expenditure;
use App\Models\Income;
use App\Services\ReportPdfService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public $reportType = 'members';
    public $startDate;
    public $endDate;
    public $loanStatus = '';
    public $selectedMonths = [];
    public $selectedYear;
    
    // Report data
    public $reportData = [];
    public $reportGenerated = false;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->selectedYear = (int) Carbon::now()->format('Y');
        $this->selectedMonths = [(int) Carbon::now()->format('m')];
    }

    public function generateReport()
    {
        $this->reportGenerated = true;

        switch ($this->reportType) {
            case 'members':
                $this->reportData = $this->getMembersReport();
                break;
            case 'contributions':
                $this->reportData = $this->getContributionsReport();
                break;
            case 'loans':
                $this->reportData = $this->getLoansReport();
                break;
            case 'financial_summary':
                $this->reportData = $this->getFinancialSummary();
                break;
        }
    }

    protected function getMembersReport()
    {
        return Member::orderBy('first_name')
            ->get()
            ->map(function ($member) {
                return [
                    'name' => $member->full_name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'joined' => $member->join_date?->format('Y-m-d') ?? '-',
                    'status' => $member->is_active ? 'Active' : 'Inactive',
                    'total_contributions' => Contribution::where('member_id', $member->id)->sum('shares') + Contribution::where('member_id', $member->id)->sum('welfare') + Contribution::where('member_id', $member->id)->sum('merry_go_round'),
                    'active_loans' => Loan::where('member_id', $member->id)->where('status', 'disbursed')->count(),
                ];
            });
    }

    protected function getContributionsReport()
    {
        return Contribution::with('member')
            ->where(function ($query) {
                foreach ($this->selectedMonths as $month) {
                    $query->orWhere(function ($q) use ($month) {
                        $q->whereYear('contribution_period', $this->selectedYear)
                          ->whereMonth('contribution_period', $month);
                    });
                }
            })
            ->orderBy('contribution_period', 'desc')
            ->orderBy('paid_at', 'desc')
            ->get()
            ->map(function ($c) {
                return [
                    'for_month' => $c->contribution_period ? $c->contribution_period->format('M Y') : '-',
                    'date' => $c->paid_at?->format('Y-m-d'),
                    'member' => $c->member?->full_name ?? '-',
                    'payment_method' => $c->payment_method ?? 'Unknown',
                    'shares' => $c->shares,
                    'welfare' => $c->welfare,
                    'merry_go_round' => $c->merry_go_round,
                    'total' => $c->shares + $c->welfare + $c->merry_go_round,
                ];
            });
    }

    protected function getLoansReport()
    {
        return Loan::with('member')
            ->when($this->startDate && $this->endDate, function ($q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);
            })
            ->when($this->loanStatus, function ($q) {
                $q->where('status', $this->loanStatus);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'member' => $loan->member?->full_name ?? '-',
                    'amount' => $loan->amount,
                    'interest' => $loan->interest_percent,
                    'status' => ucfirst($loan->status),
                    'disbursed' => $loan->disbursed_at?->format('Y-m-d') ?? '-',
                    'due' => $loan->due_at?->format('Y-m-d') ?? '-',
                    'balance' => $loan->balance,
                ];
            });
    }

    protected function getFinancialSummary()
    {
        $start = $this->startDate;
        $end = $this->endDate;

        return [
            'contributions' => [
                'shares' => Contribution::whereBetween('paid_at', [$start, $end])->sum('shares'),
                'welfare' => Contribution::whereBetween('paid_at', [$start, $end])->sum('welfare'),
                'merry_go_round' => Contribution::whereBetween('paid_at', [$start, $end])->sum('merry_go_round'),
            ],
            'loans' => [
                'disbursed_count' => Loan::whereBetween('disbursed_at', [$start, $end])->count(),
                'disbursed_amount' => Loan::whereBetween('disbursed_at', [$start, $end])->sum('amount'),
                'active_balance' => Loan::where('status', 'disbursed')->get()->sum('balance'),
            ],
            'incomes' => Income::whereBetween('received_at', [$start, $end])->sum('amount'),
            'expenditures' => Expenditure::whereBetween('spent_at', [$start, $end])->sum('amount'),
        ];
    }

    public function exportCsv()
    {
        if (! $this->reportGenerated) {
            $this->generateReport();
        }

        $filename = $this->reportType . '-' . now()->format('YmdHis') . '.csv';
        $type     = $this->reportType;
        $data     = $this->reportData;

        return response()->streamDownload(function () use ($type, $data) {
            $out = fopen('php://output', 'w');

            if ($type === 'financial_summary') {
                fputcsv($out, ['Section', 'Metric', 'Value']);
                fputcsv($out, ['Contributions', 'Shares', $data['contributions']['shares'] ?? 0]);
                fputcsv($out, ['Contributions', 'Welfare', $data['contributions']['welfare'] ?? 0]);
                fputcsv($out, ['Contributions', 'Table Banking', $data['contributions']['merry_go_round'] ?? 0]);
                fputcsv($out, ['Loans', 'Disbursed Count', $data['loans']['disbursed_count'] ?? 0]);
                fputcsv($out, ['Loans', 'Disbursed Amount', $data['loans']['disbursed_amount'] ?? 0]);
                fputcsv($out, ['Loans', 'Active Balance', $data['loans']['active_balance'] ?? 0]);
                fputcsv($out, ['Incomes', 'Total', $data['incomes'] ?? 0]);
                fputcsv($out, ['Expenditures', 'Total', $data['expenditures'] ?? 0]);
            } elseif (! empty($data) && (is_array($data) || $data instanceof \Illuminate\Support\Collection)) {
                $rows = $data instanceof \Illuminate\Support\Collection ? $data->all() : $data;
                if (! empty($rows)) {
                    $first = (array) $rows[0];
                    fputcsv($out, array_keys($first));
                    foreach ($rows as $row) {
                        fputcsv($out, array_values((array) $row));
                    }
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf()
    {
        if (! $this->reportGenerated) {
            $this->generateReport();
        }

        $service  = new ReportPdfService();
        $pdfData  = $service->generate(
            $this->reportType,
            $this->reportData,
            $this->startDate,
            $this->endDate,
            $this->loanStatus
        );

        $filename = $this->reportType . '-' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function () use ($pdfData) {
            echo $pdfData;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        return view('livewire.reports.index');
    }
}
