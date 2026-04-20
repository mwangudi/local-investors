<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_APPLIED = 'applied';
    const STATUS_APPROVED = 'approved';
    const STATUS_DISBURSED = 'disbursed';
    const STATUS_REPAID = 'repaid';

    protected $fillable = [
        'member_id',
        'amount',
        'interest_percent',
        'term_months',
        'disbursed_at',
        'due_at',
        'repaid',
        'repaid_amount',
        'status',
    ];

    protected $casts = [
        'disbursed_at' => 'date',
        'due_at' => 'date',
        'repaid' => 'boolean',
        'amount' => 'decimal:2',
        'repaid_amount' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function approvals()
    {
        return $this->hasMany(LoanApproval::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function getStandardInterestAttribute(): float
    {
        return round(($this->interest_percent / 100) * $this->amount, 2);
    }

    public function getOverduePenaltyAttribute(): float
    {
        if ($this->is_overdue) {
            return round($this->amount * 0.30, 2);
        }
        return 0;
    }

    public function getTotalPayableAttribute(): float
    {
        return $this->amount + $this->standard_interest + $this->overdue_penalty;
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function getTotalRepaidAttribute()
    {
        return $this->repayments()->sum('amount');
    }
    public function getBalanceAttribute()
    {
        if ($this->status === 'repaid') {
            return 0;
        }

        $principal = $this->amount;
        $interest = ($this->interest_percent / 100) * $principal;
        $totalDue = $principal + $interest;

        return $totalDue - $this->total_repaid;
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->due_at) {
            return false;
        }

        // Overdue only after the end of the due month
        $dueEndOfMonth = \Carbon\Carbon::parse($this->due_at)->endOfMonth();
        return now()->gt($dueEndOfMonth) && $this->balance > 0;
    }

    public function getOverdueAmountAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return $this->balance * 0.30; // 30% overdue interest
    }
}
