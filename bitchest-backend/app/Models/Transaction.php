<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'type',
        'quantity', 'price_at_transaction', 'euro_amount'
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price_at_transaction' => 'decimal:8',
        'euro_amount' => 'decimal:2'
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

}
