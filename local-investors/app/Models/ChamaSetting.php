<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChamaSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'standard_interest_percent',
        'overdue_penalty_percent',
        'loan_duration_months',
        'grace_period_days',
        'min_loan_approvals',
    ];

    public static function current()
    {
        return self::first() ?? self::create([]);
    }

    public function getStandardInterestAttribute(): float
    {
        $rate = ChamaSetting::current()->standard_interest_percent;
        return round($this->amount * ($rate / 100), 2);
    }

    public function getOverduePenaltyAttribute(): float
    {
        $settings = ChamaSetting::current();
        $rate = $settings->overdue_penalty_percent;

        // grace period logic
        $overdueDate = $this->due_at->copy()->addDays($settings->grace_period_days);

        if (!$this->repaid && now()->gt($overdueDate)) {
            return round($this->amount * ($rate / 100), 2);
        }

        return 0;
    }

}
