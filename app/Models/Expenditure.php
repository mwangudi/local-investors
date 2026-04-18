<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenditure extends Model
{
    protected $fillable = [
        'description',
        'amount',
        'spent_at',
        'category',
    ];

    protected $casts = [
        'spent_at' => 'date',
        'amount' => 'decimal:2',
    ];
}
