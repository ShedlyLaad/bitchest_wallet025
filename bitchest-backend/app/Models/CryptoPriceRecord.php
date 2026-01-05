<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CryptoPriceRecord extends Model
{
    use HasFactory;

    protected $table = 'crypto_price_records';

    protected $fillable = [
        'crypto_currency_id',
        'price',
        'recorded_at',
    ];

    protected $casts = [
        'price' => 'decimal:8',
        'recorded_at' => 'datetime',
    ];

    public function crypto()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id');
    }
}

