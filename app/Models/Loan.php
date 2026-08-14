<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_APPLIED = 'applied';
    const STATUS_APPROVED = 'approved';
    const STATUS_DISBURSED = 'disbursed';
    const STATUS_REPAID = 'repaid';

    protected $fillable = [
        'member_id',
        'parent_loan_id',
        'reference',
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

    // Set during performInsert rather than by a model event, so seeders that mute
    // events (WithoutModelEvents) still get a reference.
    public $usesUniqueIds = true;

    public function uniqueIds(): array
    {
        return ['reference'];
    }

    public function newUniqueId(): string
    {
        return self::generateReference();
    }

    // Loans are addressed by reference (e.g. XTR883) rather than by id.
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public static function generateReference(): string
    {
        do {
            $letters = '';
            for ($i = 0; $i < 3; $i++) {
                $letters .= chr(random_int(65, 90));
            }

            $reference = $letters . random_int(100, 999);
        } while (DB::table('loans')->where('reference', $reference)->exists());

        return $reference;
    }

    // SQL counterpart of is_overdue: the whole due month must have passed.
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_at')
            ->whereDate('due_at', '<', now()->startOfMonth())
            ->where('status', '!=', self::STATUS_REPAID);
    }

    public function parentLoan()
    {
        return $this->belongsTo(self::class, 'parent_loan_id');
    }

    public function rolloverLoan()
    {
        return $this->hasOne(self::class, 'parent_loan_id');
    }

    public function getWasRolledOverAttribute(): bool
    {
        return $this->rolloverLoan()->exists();
    }

    public function getCanBeRolledOverAttribute(): bool
    {
        return $this->status === self::STATUS_DISBURSED
            && $this->balance > 0
            && ! $this->was_rolled_over;
    }

    /**
     * Re-issue this loan's outstanding balance (principal + interest less repayments)
     * as a new loan and close this one, as done for the June 2026 rollovers.
     *
     * @throws \DomainException when there is nothing to roll over.
     */
    public function rollOverBalance(string $disbursedAt, string $dueAt): self
    {
        // Read the balance first: it reports 0 once the loan is closed below.
        $balance = round((float) $this->balance, 2);

        if ($this->status !== self::STATUS_DISBURSED) {
            throw new \DomainException('Only a disbursed loan can be rolled over.');
        }

        if ($balance <= 0) {
            throw new \DomainException('This loan has no outstanding balance to roll over.');
        }

        if ($this->was_rolled_over) {
            throw new \DomainException('This loan has already been rolled over.');
        }

        return DB::transaction(function () use ($balance, $disbursedAt, $dueAt) {
            $replacement = self::create([
                'member_id'        => $this->member_id,
                'parent_loan_id'   => $this->id,
                'amount'           => $balance,
                'interest_percent' => $this->interest_percent,
                'term_months'      => $this->term_months,
                'disbursed_at'     => $disbursedAt,
                'due_at'           => $dueAt,
                'repaid'           => false,
                'repaid_amount'    => 0,
                'status'           => self::STATUS_DISBURSED,
            ]);

            $this->update([
                'repaid'        => true,
                'repaid_amount' => $this->total_repaid,
                'status'        => self::STATUS_REPAID,
            ]);

            return $replacement;
        });
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
        // Use the eager-loaded aggregate when present, so listings avoid a query per row.
        if (array_key_exists('repayments_sum_amount', $this->attributes)) {
            return (float) $this->attributes['repayments_sum_amount'];
        }

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
