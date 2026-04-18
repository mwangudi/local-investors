<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashReturn extends Model
{
    protected $fillable = [
        'amount',
        'returned_at',
        'description',
        'project_id',
    ];

    protected $casts = [
        'returned_at' => 'date',
        'amount' => 'integer',
    ];

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
