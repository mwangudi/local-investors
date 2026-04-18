<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'address',
        'city',
        'country',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
