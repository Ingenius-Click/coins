<?php

namespace Ingenius\Coins\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ingenius\Coins\Database\Factories\CoinFactory;
use Ingenius\Coins\Enums\CoinPosition;

class Coin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'short_name',
        'symbol',
        'position',
        'active',
        'main',
        'exchange_rate',
        'exchange_rate_history',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'position' => CoinPosition::class,
        'exchange_rate_history' => 'array',
    ];

    protected static function newFactory(): CoinFactory
    {
        return CoinFactory::new();
    }
}
