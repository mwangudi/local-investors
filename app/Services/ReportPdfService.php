<?php

namespace App\Services;

use TCPDF;

class ReportPdfService
{
    protected TCPDF $pdf;
    protected string $logoPath;

    // Brand colours
    protected array $orange   = [230, 126, 34];   // Header / accent
    protected array $darkBlue = [44, 62, 80];      // Dark text
    protected array $rowAlt   = [255, 248, 235];   // Light amber alternating row
    protected array $white    = [255, 255, 255];
    protected array $border   = [220, 180, 120];   // Amber border

    public function __construct()
    {
        $this->logoPath = public_path('assets/images/logo.png');

        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->pdf->SetCreator('Local Investors');
        $this->pdf->SetAuthor('Local Investors');
        $this->pdf->SetMargins(12, 10, 12);
        $this->pdf->SetAutoPageBreak(true, 15);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }

    public function generate(string $reportType, $reportData, string $startDate, string $endDate, string $loanStatus = ''): string
    {
        $this->pdf->SetTitle($this->getTitle($reportType));
        $this->pdf->AddPage();

        $this->renderLogo();
        $this->renderReportTitle($reportType, $startDate, $endDate, $loanStatus);

        match ($reportType) {
            'members'           => $this->renderMembersReport($reportData),
            'contributions'     => $this->renderContributionsReport($reportData),
            'loans'             => $this->renderLoansReport($reportData),
            'financial_summary' => $this->renderFinancialSummary($reportData),
            default             => null,
        };

        $this->renderPageFooter();

        return $this->pdf->Output('', 'S');
    }

    // ─── SHARED LAYOUT HELPERS ──────────────────────────────────────

    protected function getTitle(string $type): string
    {
        return match ($type) {
            'members'           => 'Members Report',
            'contributions'     => 'Contributions Report',
            'loans'             => 'Loans Report',
            'financial_summary' => 'Financial Summary',
            default             => 'Report',
        };
    }

    protected function renderLogo(): void
    {
        if (file_exists($this->logoPath)) {
            $pageW = $this->pdf->getPageWidth();
            $imgW  = 60;
            $x     = ($pageW - $imgW) / 2;
            $this->pdf->Image($this->logoPath, $x, 12, $imgW, 0, 'PNG');
            $this->pdf->Ln(25);
        }
    }

    protected function renderReportTitle(string $reportType, string $startDate, string $endDate, string $loanStatus): void
    {
        // Horizontal rule
        $this->pdf->SetDrawColor(...$this->orange);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(12, $this->pdf->GetY(), 198, $this->pdf->GetY());
        $this->pdf->Ln(6);

        // Title
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetTextColor(...$this->darkBlue);
        $this->pdf->Cell(0, 10, $this->getTitle($reportType), 0, 1, 'C');

        // Subtitle
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(120, 120, 120);
        $subtitle = "Period: {$startDate}  to  {$endDate}";
        if ($reportType === 'loans' && $loanStatus) {
            $subtitle .= '  |  Status: ' . ucfirst($loanStatus);
        }
        $this->pdf->Cell(0, 5, $subtitle, 0, 1, 'C');
        $this->pdf->Ln(3);
    }

    protected function renderPageFooter(): void
    {
        $this->pdf->Ln(8);
        $this->pdf->SetFont('helvetica', 'I', 7);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 5, 'Generated on ' . now()->format('d M Y, H:i') . '  |  Local Investors Management Platform', 0, 1, 'C');
    }

    /**
     * Render a row of summary cards (like the screenshot).
     */
    protected function renderSummaryCards(array $cards): void
    {
        $count  = count($cards);
        $margin = 12;
        $gap    = 3;
        $usable = 210 - ($margin * 2) - ($gap * ($count - 1));
        $cardW  = $usable / $count;

        $startX = $margin;
        $startY = $this->pdf->GetY();

        foreach ($cards as $card) {
            $this->pdf->SetXY($startX, $startY);

            // Card border
            $this->pdf->SetDrawColor(...$this->border);
            $this->pdf->SetLineWidth(0.3);
            $this->pdf->RoundedRect($startX, $startY, $cardW, 18, 1.5, '1111', 'D');

            // Label
            $this->pdf->SetXY($startX, $startY + 2);
            $this->pdf->SetFont('helvetica', '', 7);
            $this->pdf->SetTextColor(130, 130, 130);
            $this->pdf->Cell($cardW, 5, strtoupper($card['label']), 0, 1, 'C');

            // Value
            $this->pdf->SetX($startX);
            $this->pdf->SetFont('helvetica', 'B', 11);
            $this->pdf->SetTextColor(...$this->darkBlue);
            $this->pdf->Cell($cardW, 7, $card['value'], 0, 1, 'C');

            $startX += $cardW + $gap;
        }

        $this->pdf->SetY($startY + 22);
    }

    /**
     * Render a colourful table header row (orange background, white text).
     */
    protected function renderTableHeader(array $headers, array $widths): void
    {
        $this->pdf->SetFont('helvetica', 'B', 7);
        $this->pdf->SetFillColor(...$this->orange);
        $this->pdf->SetTextColor(...$this->white);
        $this->pdf->SetDrawColor(...$this->orange);

        foreach ($headers as $idx => $header) {
            $this->pdf->Cell($widths[$idx], 7, strtoupper($header), 1, 0, 'C', true);
        }
        $this->pdf->Ln();
        $this->pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Check if we need a page break and re-render headers.
     */
    protected function checkPageBreak(array $headers, array $widths, float $threshold = 265): void
    {
        if ($this->pdf->GetY() > $threshold) {
            $this->pdf->AddPage();
            $this->renderTableHeader($headers, $widths);
        }
    }

    // ─── LOANS REPORT ───────────────────────────────────────────────

    protected function renderLoansReport($data): void
    {
        // Calculate summary
        $totalLoans    = count($data);
        $totalDisbursed = 0; $totalInterest = 0; $totalRepaid = 0;
        foreach ($data as $row) {
            $totalDisbursed += $row['amount'];
            $interest = $row['amount'] * ($row['interest'] / 100);
            $totalInterest  += $interest;
            $totalRepaid    += ($row['amount'] + $interest) - $row['balance'];
        }

        $this->renderSummaryCards([
            ['label' => 'Total Loans',    'value' => (string) $totalLoans],
            ['label' => 'Total Disbursed','value' => 'KES ' . number_format($totalDisbursed, 2)],
            ['label' => 'Total Interest', 'value' => 'KES ' . number_format($totalInterest, 2)],
            ['label' => 'Total Repaid',   'value' => 'KES ' . number_format($totalRepaid, 2)],
        ]);

        $headers = ['#', 'Member', 'Amount', 'Interest', 'Total Payable', 'Repaid', 'Balance', 'Status', 'Due'];
        $widths  = [8, 32, 22, 18, 25, 22, 22, 18, 20];

        $this->renderTableHeader($headers, $widths);
        $this->pdf->SetFont('helvetica', '', 7.5);

        $i = 1;
        foreach ($data as $row) {
            $this->checkPageBreak($headers, $widths);

            $fill = $i % 2 === 0;
            if ($fill) {
                $this->pdf->SetFillColor(...$this->rowAlt);
            } else {
                $this->pdf->SetFillColor(...$this->white);
            }

            $this->pdf->SetDrawColor(230, 220, 200);
            $interest  = $row['amount'] * ($row['interest'] / 100);
            $payable   = $row['amount'] + $interest;
            $repaid    = $payable - $row['balance'];

            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Cell($widths[0], 7, $i, 'LR', 0, 'C', true);
            $this->pdf->Cell($widths[1], 7, $row['member'], 'LR', 0, 'L', true);
            $this->pdf->Cell($widths[2], 7, number_format($row['amount'], 2), 'LR', 0, 'R', true);
            $this->pdf->Cell($widths[3], 7, number_format($interest, 2), 'LR', 0, 'R', true);
            $this->pdf->Cell($widths[4], 7, number_format($payable, 2), 'LR', 0, 'R', true);
            $this->pdf->Cell($widths[5], 7, number_format($repaid, 2), 'LR', 0, 'R', true);
            $this->pdf->Cell($widths[6], 7, number_format($row['balance'], 2), 'LR', 0, 'R', true);

            // Status with colour
            $statusLower = strtolower($row['status']);
            $statusColor = match($statusLower) {
                'disbursed'          => [230, 126, 34],
                'applied'            => [52, 152, 219],
                'approved'           => [46, 204, 113],
                'repaid'             => [149, 165, 166],
                default              => [0, 0, 0],
            };
            $statusLabel = match($statusLower) {
                'disbursed' => 'Ongoing',
                'repaid'    => 'Paid',
                default     => ucfirst($statusLower),
            };
            $this->pdf->SetTextColor(...$statusColor);
            $this->pdf->SetFont('helvetica', 'B', 7.5);
            $this->pdf->Cell($widths[7], 7, $statusLabel, 'LR', 0, 'C', true);
            $this->pdf->SetFont('helvetica', '', 7.5);
            $this->pdf->SetTextColor(0, 0, 0);

            $this->pdf->Cell($widths[8], 7, $row['due'], 'LR', 1, 'C', true);
            $i++;
        }

        // Bottom border
        $this->pdf->SetDrawColor(...$this->orange);
        $this->pdf->Cell(array_sum($widths), 0, '', 'T');
    }

    // ─── MEMBERS REPORT ─────────────────────────────────────────────

    protected function renderMembersReport($data): void
    {
        $totalMembers = count($data);
        $activeCount  = collect($data)->where('status', 'Active')->count();
        $totalContrib = collect($data)->sum('total_contributions');

        $this->renderSummaryCards([
            ['label' => 'Total Members',  'value' => (string) $totalMembers],
            ['label' => 'Active Members', 'value' => (string) $activeCount],
            ['label' => 'Total Contributions', 'value' => 'KES ' . number_format($totalContrib, 2)],
        ]);

        $headers = ['#', 'Name', 'Email', 'Phone', 'Joined', 'Status', 'Contributions', 'Loans'];
        $widths  = [8, 30, 42, 28, 20, 16, 25, 16];

        $this->renderTableHeader($headers, $widths);
        $this->pdf->SetFont('helvetica', '', 7.5);

        $i = 1;
        foreach ($data as $row) {
            $this->checkPageBreak($headers, $widths);

            $fill = $i % 2 === 0;
            $this->pdf->SetFillColor(...($fill ? $this->rowAlt : $this->white));
            $this->pdf->SetDrawColor(230, 220, 200);

            $this->pdf->Cell($widths[0], 7, $i, 'LR', 0, 'C', true);
            $this->pdf->Cell($widths[1], 7, $row['name'], 'LR', 0, 'L', true);
            $this->pdf->Cell($widths[2], 7, $row['email'] ?? '-', 'LR', 0, 'L', true);
            $this->pdf->Cell($widths[3], 7, $row['phone'] ?? '-', 'LR', 0, 'L', true);
            $this->pdf->Cell($widths[4], 7, $row['joined'], 'LR', 0, 'C', true);

            // Status colour
            $isActive = $row['status'] === 'Active';
            $this->pdf->SetTextColor(...($isActive ? [46, 204, 113] : [231, 76, 60]));
            $this->pdf->SetFont('helvetica', 'B', 7.5);
            $this->pdf->Cell($widths[5], 7, $row['status'], 'LR', 0, 'C', true);
            $this->pdf->SetFont('helvetica', '', 7.5);
            $this->pdf->SetTextColor(0, 0, 0);

            $this->pdf->Cell($widths[6], 7, 'KES ' . number_format($row['total_contributions'], 2), 'LR', 0, 'R', true);
            $this->pdf->Cell($widths[7], 7, $row['active_loans'], 'LR', 1, 'C', true);
            $i++;
        }

        $this->pdf->SetDrawColor(...$this->orange);
        $this->pdf->Cell(array_sum($widths), 0, '', 'T');
    }

    // ─── CONTRIBUTIONS REPORT ───────────────────────────────────────

    protected function renderContributionsReport($data): void
    {
        $totShares = collect($data)->sum('shares');
        $totWelfare = collect($data)->sum('welfare');
        $totMgr = collect($data)->sum('merry_go_round');
        $totAll = collect($data)->sum('total');

        $this->renderSummaryCards([
            ['label' => 'Total Records', 'value' => (string) count($data)],
            ['label' => 'Shares',        'value' => 'KES ' . number_format($totShares, 2)],
            ['label' => 'Welfare',       'value' => 'KES ' . number_format($totWelfare, 2)],
            ['label' => 'Grand Total',   'value' => 'KES ' . number_format($totAll, 2)],
        ]);

        $headers = ['#', 'Month', 'Paid On', 'Member', 'Method', 'Shares', 'Welfare', 'MGR', 'Total'];
        $widths  = [7, 18, 18, 30, 22, 18, 18, 24, 25];

        $this->renderTableHeader($headers, $widths);
        $this->pdf->SetFont('helvetica', '', 7.5);

        $i = 1;
        foreach ($data as $row) {
            $this->checkPageBreak($headers, $widths);

            $fill = $i % 2 === 0;
            $this->pdf->SetFillColor(...($fill ? $this->rowAlt : $this->white));
            $this->pdf->SetDrawColor(230, 220, 200);

            $this->pdf->Cell($widths[0], 7, $i, 'LR', 0, 'C', true);
            $this->pdf->Cell($widths[1], 7, substr($row['for_month'], 0, 8), 'LR', 0, 'C', true);
            $this->pdf->Cell($widths[2], 7, $row['date'], 'LR', 0, 'C', true);

            // Dynamically clip member name if too long
            $memberName = strlen($row['member']) > 15 ? substr($row['member'], 0, 15) . '.' : $row['member'];
            $this->pdf->Cell($widths[3], 7, $memberName, 'LR', 0, 'L', true);

            $methodMap = ['mpesa' => 'M-PESA', 'zimele' => 'Zimele', 'merry_go_round' => 'Mgr', 'cash' => 'Cash', 'bank' => 'Bank'];
            $methodLabel = $methodMap[strtolower($row['payment_method'] ?? '')] ?? ucfirst($row['payment_method'] ?? 'N/A');
            $this->pdf->Cell($widths[4], 7, $methodLabel, 'LR', 0, 'C', true);

            $this->pdf->Cell($widths[5], 7, number_format($row['shares'], 2), 'LR', 0, 'R', true);
            $this->pdf->Cell($widths[6], 7, number_format($row['welfare'], 2), 'LR', 0, 'R', true);
            $this->pdf->Cell($widths[7], 7, number_format($row['merry_go_round'], 2), 'LR', 0, 'R', true);

            $this->pdf->SetFont('helvetica', 'B', 7.2);
            $this->pdf->Cell($widths[8], 7, number_format($row['total'], 2), 'LR', 1, 'R', true);
            $this->pdf->SetFont('helvetica', '', 7.2);
            $i++;
        }

        // Totals row
        $this->pdf->SetFont('helvetica', 'B', 7.2);
        $this->pdf->SetFillColor(...$this->orange);
        $this->pdf->SetTextColor(...$this->white);
        $this->pdf->Cell($widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4], 7, 'TOTALS', 1, 0, 'R', true);
        $this->pdf->Cell($widths[5], 7, number_format($totShares, 2), 1, 0, 'R', true);
        $this->pdf->Cell($widths[6], 7, number_format($totWelfare, 2), 1, 0, 'R', true);
        $this->pdf->Cell($widths[7], 7, number_format($totMgr, 2), 1, 0, 'R', true);
        $this->pdf->Cell($widths[8], 7, number_format($totAll, 2), 1, 1, 'R', true);
        $this->pdf->SetTextColor(0, 0, 0);
    }

    // ─── FINANCIAL SUMMARY ──────────────────────────────────────────

    protected function renderFinancialSummary($data): void
    {
        $netIncome = ($data['incomes'] ?? 0) - ($data['expenditures'] ?? 0);
        $contribTotal = ($data['contributions']['shares'] ?? 0) + ($data['contributions']['welfare'] ?? 0) + ($data['contributions']['merry_go_round'] ?? 0);

        $this->renderSummaryCards([
            ['label' => 'Contributions', 'value' => 'KES ' . number_format($contribTotal, 2)],
            ['label' => 'Incomes',       'value' => 'KES ' . number_format($data['incomes'] ?? 0, 2)],
            ['label' => 'Expenditures',  'value' => 'KES ' . number_format($data['expenditures'] ?? 0, 2)],
            ['label' => 'Net Income',    'value' => 'KES ' . number_format($netIncome, 2)],
        ]);

        $colW = [90, 90];

        // ── Contributions Section ──
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(...$this->darkBlue);
        $this->pdf->Cell(0, 8, 'Contributions Breakdown', 0, 1, 'L');

        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(...$this->orange);
        $this->pdf->SetTextColor(...$this->white);
        $this->pdf->Cell($colW[0], 7, 'CATEGORY', 1, 0, 'L', true);
        $this->pdf->Cell($colW[1], 7, 'AMOUNT (KES)', 1, 1, 'R', true);
        $this->pdf->SetTextColor(0, 0, 0);

        $items = [
            'Shares'         => $data['contributions']['shares'] ?? 0,
            'Welfare'        => $data['contributions']['welfare'] ?? 0,
            'Merry-Go-Round' => $data['contributions']['merry_go_round'] ?? 0,
        ];
        $this->pdf->SetFont('helvetica', '', 9);
        $row = 0;
        foreach ($items as $label => $value) {
            $this->pdf->SetFillColor(...($row % 2 ? $this->rowAlt : $this->white));
            $this->pdf->SetDrawColor(230, 220, 200);
            $this->pdf->Cell($colW[0], 7, $label, 'LR', 0, 'L', true);
            $this->pdf->Cell($colW[1], 7, number_format($value, 2), 'LR', 1, 'R', true);
            $row++;
        }
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(...$this->orange);
        $this->pdf->SetTextColor(...$this->white);
        $this->pdf->Cell($colW[0], 7, 'TOTAL', 1, 0, 'L', true);
        $this->pdf->Cell($colW[1], 7, number_format($contribTotal, 2), 1, 1, 'R', true);
        $this->pdf->SetTextColor(0, 0, 0);

        $this->pdf->Ln(6);

        // ── Loans Section ──
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(...$this->darkBlue);
        $this->pdf->Cell(0, 8, 'Loans Overview', 0, 1, 'L');

        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(...$this->orange);
        $this->pdf->SetTextColor(...$this->white);
        $this->pdf->Cell($colW[0], 7, 'METRIC', 1, 0, 'L', true);
        $this->pdf->Cell($colW[1], 7, 'VALUE', 1, 1, 'R', true);
        $this->pdf->SetTextColor(0, 0, 0);

        $loanItems = [
            'Loans Disbursed'    => $data['loans']['disbursed_count'] ?? 0,
            'Disbursed Amount'   => 'KES ' . number_format($data['loans']['disbursed_amount'] ?? 0, 2),
            'Active Loan Balance'=> 'KES ' . number_format($data['loans']['active_balance'] ?? 0, 2),
        ];
        $this->pdf->SetFont('helvetica', '', 9);
        $row = 0;
        foreach ($loanItems as $label => $value) {
            $this->pdf->SetFillColor(...($row % 2 ? $this->rowAlt : $this->white));
            $this->pdf->SetDrawColor(230, 220, 200);
            $this->pdf->Cell($colW[0], 7, $label, 'LR', 0, 'L', true);
            $this->pdf->Cell($colW[1], 7, is_numeric($value) ? number_format($value) : $value, 'LR', 1, 'R', true);
            $row++;
        }
        $this->pdf->SetDrawColor(...$this->orange);
        $this->pdf->Cell(array_sum($colW), 0, '', 'T');

        $this->pdf->Ln(6);

        // ── Income & Expenditure Section ──
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(...$this->darkBlue);
        $this->pdf->Cell(0, 8, 'Income & Expenditure', 0, 1, 'L');

        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetFillColor(...$this->orange);
        $this->pdf->SetTextColor(...$this->white);
        $this->pdf->Cell($colW[0], 7, 'CATEGORY', 1, 0, 'L', true);
        $this->pdf->Cell($colW[1], 7, 'AMOUNT (KES)', 1, 1, 'R', true);
        $this->pdf->SetTextColor(0, 0, 0);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetFillColor(...$this->white);
        $this->pdf->SetDrawColor(230, 220, 200);
        $this->pdf->Cell($colW[0], 7, 'Total Incomes', 'LR', 0, 'L', true);
        $this->pdf->Cell($colW[1], 7, number_format($data['incomes'] ?? 0, 2), 'LR', 1, 'R', true);
        $this->pdf->SetFillColor(...$this->rowAlt);
        $this->pdf->Cell($colW[0], 7, 'Total Expenditures', 'LR', 0, 'L', true);
        $this->pdf->Cell($colW[1], 7, number_format($data['expenditures'] ?? 0, 2), 'LR', 1, 'R', true);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(...$this->orange);
        $this->pdf->SetTextColor(...$this->white);
        $this->pdf->Cell($colW[0], 7, 'NET', 1, 0, 'L', true);
        $this->pdf->Cell($colW[1], 7, number_format($netIncome, 2), 1, 1, 'R', true);
        $this->pdf->SetTextColor(0, 0, 0);
    }
}
