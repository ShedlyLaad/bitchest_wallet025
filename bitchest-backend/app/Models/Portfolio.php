<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crypto_currency_id',
        // 'euro_balance',
        'total_crypto_value',
    ];

    protected $casts = [
        // 'euro_balance' => 'decimal:2',
        'total_crypto_value' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crypto()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
