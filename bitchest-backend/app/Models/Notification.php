<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'portfolio_id',
        'crypto_currency_id',
        'type',
        'title',
        'message',
        'crypto_symbol',
        'gain_loss',
        'gain_loss_percent',
        'current_price',
        'previous_price',
        'is_read',
        'read_at',
        'level',
        'level_name',
    ];

    protected $casts = [
        'gain_loss' => 'decimal:8',
        'gain_loss_percent' => 'decimal:2',
        'current_price' => 'decimal:8',
        'previous_price' => 'decimal:8',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'level' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function crypto()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id');
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}

