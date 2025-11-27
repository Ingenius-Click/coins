<?php

namespace Ingenius\Coins\Configuration;

use Ingenius\Coins\Services\CurrencyServices;
use Ingenius\Core\Interfaces\StoreConfigurationInterface;

class CurrentCoinStoreConfiguration implements StoreConfigurationInterface
{
    /**
     * Get the configuration key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return 'current_coin';
    }

    /**
     * Get the configuration value.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        // Get the main coin from the database
        $currencyMetadata = get_currency_metadata();

        if (!$currencyMetadata) {
            return null;
        }

        return $currencyMetadata;
    }

    /**
     * Get the package name that provides this configuration.
     *
     * @return string
     */
    public function getPackageName(): string
    {
        return 'coins';
    }

    /**
     * Get the priority for this configuration (higher number = higher priority).
     *
     * @return int
     */
    public function getPriority(): int
    {
        // High priority since this overrides the base_coin from core settings
        return 101;
    }

    /**
     * Check if this configuration is available/enabled.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        // Check if there's a main coin configured
        return get_current_currency() !== null;
    }
}
