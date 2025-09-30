<?php

namespace Ingenius\Coins\Configuration;

use Ingenius\Coins\Models\Coin;
use Ingenius\Core\Interfaces\StoreConfigurationInterface;

class AvailableCoinsStoreConfiguration implements StoreConfigurationInterface {

    public function getKey(): string
    {
        return 'available_coins';
    }

    public function getValue(): mixed
    {
        // This configuration does not hold a specific value.
        return Coin::where('active', true)->get()->map(function ($coin) {
            return [
                'short_name' => $coin->short_name,
                'name' => $coin->name,
                'symbol' => $coin->symbol,
                'position' => $coin->position->value,
                'exchange_rate' => $coin->exchange_rate,
            ];
        })->toArray();
    }

    public function getPackageName(): string
    {
        return 'coins';
    }

    public function getPriority(): int
    {
        return 101; // Medium priority
    }

    public function isAvailable(): bool
    {
        // This configuration is always available.
        return true;
    }

}