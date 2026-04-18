<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'contribution_period',
        'shares',
        'welfare',
        'merry_go_round',
        'penalty',
        'penalty_type',
        'type',
        'notes',
        'paid_at',
        'payment_method',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'contribution_period' => 'date',
        'shares' => 'decimal:2',
        'welfare' => 'decimal:2',
        'merry_go_round' => 'decimal:2',
        'penalty' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
