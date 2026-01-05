<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoCurrency extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'symbol'];

    public function priceRecords()
    {
        return $this->hasMany(CryptoPriceRecord::class, 'crypto_currency_id');
    }

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class, 'crypto_currency_id');
    }

}
