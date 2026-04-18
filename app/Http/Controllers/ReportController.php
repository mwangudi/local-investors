<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Generate PDF report for loans with filter support.
     */
    public function loansReport(Request $request)
    {
        $query = Loan::with('member');

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date range filter if provided
        if ($request->filled('from')) {
            $query->whereDate('disbursed_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('disbursed_at', '<=', $request->to);
        }

        // Handle tab filters (e.g., "needs_approval")
        if ($request->filled('tab') && $request->tab === 'needs_approval') {
            $memberId = auth()->user()->member_id;
            if ($memberId) {
                $query->whereIn('status', [Loan::STATUS_APPLIED, Loan::STATUS_APPROVED])
                    ->where('member_id', '!=', $memberId)
                    ->whereDoesntHave('approvals', fn($q) => $q->where('member_id', $memberId));
            }
        }

        $loans = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.loans-report', [
            'loans' => $loans,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="loans-report-' . now()->format('Y-m-d') . '.pdf"');
    }
}
