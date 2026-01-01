<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PENDING_VALIDATION = 'pending_validation';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'euro_balance',
        'must_change_password',
        'profile_picture',
        'profile_banner',
    ];

    protected $casts = [
        'euro_balance' => 'decimal:2',
        'must_change_password' => 'boolean',
    ];

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isClient(): bool { return $this->role === 'client'; }

    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isPendingValidation(): bool { return $this->status === self::STATUS_PENDING_VALIDATION; }
    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }
    public function isBlocked(): bool { return $this->status === self::STATUS_BLOCKED; }
    public function mustChangePassword(): bool { return (bool) $this->must_change_password; }
}
