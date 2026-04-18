<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Notifications\Notifiable;

class Member extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'join_date',
        'is_active',
        'notification_preference',
    ];

    protected $casts = [
        'join_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function routeNotificationForSms()
    {
        return $this->phone;
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
