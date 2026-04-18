<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashReturn extends Model
{
    protected $fillable = [
        'amount',
        'returned_at',
        'description',
    ];

    protected $casts = [
        'returned_at' => 'date',
        'amount' => 'integer',
    ];
}
