<?php

namespace Ingenius\Coins\Services;

use Illuminate\Http\Request;
use Ingenius\Coins\Enums\CoinPosition;
use Ingenius\Coins\Models\Coin;

class CurrencyServices
{
    /**
     * The currency code resolved for the current request, set by CurrencyMiddleware.
     */
    protected ?string $currentCurrency = null;

    /**
     * Request-scoped cache for Coin models to prevent repeated database queries.
     * Indexed by currency short_name for O(1) lookups.
     *
     * @var array<string, Coin|null>
     */
    protected static array $currencyCache = [];

    /**
     * Cache for the base currency to avoid repeated queries.
     *
     * @var Coin|null|false False means "not loaded yet", null means "doesn't exist"
     */
    protected static $baseCurrencyCache = false;

    /**
     * Set the current currency for this request.
     * Called once by CurrencyMiddleware after resolving the currency from the request.
     */
    public function setCurrentCurrency(string $code): void
    {
        $this->currentCurrency = $code;
    }

    /**
     * Get a currency from cache or database.
     * This method ensures we only query the database once per currency per request.
     *
     * @param string $shortName Currency short name (e.g., 'USD', 'EUR')
     * @return Coin|null
     */
    protected static function getCachedCurrency(string $shortName): ?Coin
    {
        // Check if we've already loaded this currency
        if (!array_key_exists($shortName, self::$currencyCache)) {
            // Load from database and cache the result (even if null)
            self::$currencyCache[$shortName] = Coin::where('short_name', $shortName)->first();
        }

        return self::$currencyCache[$shortName];
    }

    /**
     * Get the base currency from cache or database.
     *
     * @return Coin|null
     */
    protected static function getCachedBaseCurrency(): ?Coin
    {
        // Check if we've already loaded the base currency
        if (self::$baseCurrencyCache === false) {
            self::$baseCurrencyCache = Coin::where('main', true)->first();
        }

        return self::$baseCurrencyCache ?: null;
    }

    /**
     * Pre-warm the cache with commonly used currencies.
     * Call this early in the request lifecycle (e.g., in middleware or order retrieval)
     * to batch-load currencies and avoid N+1 queries.
     *
     * @param array $currencyCodes Array of currency short names to preload
     * @return void
     */
    public static function warmCache(array $currencyCodes): void
    {
        // Filter out currencies we've already cached
        $uncachedCodes = array_filter($currencyCodes, fn($code) => !array_key_exists($code, self::$currencyCache));

        if (empty($uncachedCodes)) {
            return;
        }

        // Batch load all uncached currencies in a single query
        $currencies = Coin::whereIn('short_name', $uncachedCodes)->get();

        // Cache the results
        foreach ($currencies as $currency) {
            self::$currencyCache[$currency->short_name] = $currency;
        }

        // Mark missing currencies as null
        foreach ($uncachedCodes as $code) {
            if (!array_key_exists($code, self::$currencyCache)) {
                self::$currencyCache[$code] = null;
            }
        }
    }

    /**
     * Clear the cache. Useful for testing or long-running processes.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$currencyCache = [];
        self::$baseCurrencyCache = false;
    }
    /**
     * Get the current currency for the active request.
     * Returns the currency set by CurrencyMiddleware, or falls back to base currency.
     *
     * @return string Currency short name (e.g., 'USD', 'EUR')
     */
    public function getCurrentCurrency(): string
    {
        return $this->currentCurrency ?? static::getBaseCurrencyShortName() ?? 'USD';
    }

    /**
     * Get metadata for the current currency.
     *
     * @return array
     */
    public function getCurrentCurrencyMetadata(): array
    {
        $currencyCode = $this->getCurrentCurrency();
        $coin = static::getCachedCurrency($currencyCode);

        if (!$coin) {
            $coin = static::getBaseCurrency();
        }

        return [
            'short_name' => $coin->short_name,
            'name' => $coin->name,
            'symbol' => $coin->symbol,
            'position' => $coin->position->value,
            'exchange_rate' => $coin->exchange_rate,
        ];
    }

    /**
     * Convert amount from one currency to another.
     * Hook handler for 'currency.convert'.
     *
     * @param int $amountInCents Amount in cents (source currency)
     * @param array $context Context with 'to_currency' and optional 'from_currency'
     * @return int Converted amount in cents
     */
    public function convertAmount(int $amountInCents, array $context): int
    {
        $toCurrency = $context['to_currency'] ?? $this->getCurrentCurrency();
        $fromCurrency = $context['from_currency'] ?? static::getBaseCurrencyShortName();

        // If converting to same currency, return as-is
        if ($toCurrency === $fromCurrency) {
            return $amountInCents;
        }

        // Get exchange rates
        $fromRate = static::getExchangeRate($fromCurrency) ?? 1.0;
        $toRate = static::getExchangeRate($toCurrency) ?? 1.0;

        // Convert: amount_in_base = amount / from_rate
        // amount_in_target = amount_in_base * to_rate
        // Simplified: amount_in_target = amount * (to_rate / from_rate)
        $conversionRate = $toRate / $fromRate;

        return (int) round($amountInCents * $conversionRate);
    }

    /**
     * Format amount with currency symbol.
     * Hook handler for 'currency.format'.
     *
     * @param int $amountInCents
     * @param array $context
     * @return string
     */
    public function formatAmountWithCurrency(int $amountInCents, array $context): string
    {
        $currencyCode = $context['currency_code'] ?? $this->getCurrentCurrency();
        return static::formatCurrency($amountInCents, $currencyCode);
    }
    public static function formatCurrency(int $amount, string $currency_short_name): string
    {
        $currency = self::getCachedCurrency($currency_short_name);

        $numberFormatted = number_format($amount / 100.0, 2, '.', ',');

        // If currency doesn't exist (e.g., deleted), fallback to short_name prefix
        if (!$currency) {
            return $currency_short_name . ' ' . $numberFormatted;
        }

        if ($currency->position == CoinPosition::BACK) {
            return $numberFormatted . $currency->symbol;
        }

        return $currency->symbol . $numberFormatted;
    }

    public static function getCurrencyFromRequest(Request $request): ?Coin
    {
        $currencyCode = $request->get('currency');
        return $currencyCode ? self::getCachedCurrency($currencyCode) : null;
    }

    public static function getCurrencyShortNameFromRequest(Request $request): ?string
    {
        return $request->get('currency');
    }

    public static function getBaseCurrency(): ?Coin
    {
        return self::getCachedBaseCurrency();
    }

    public static function getBaseCurrencyShortName(): ?string
    {
        return self::getCachedBaseCurrency()?->short_name;
    }

    public static function getExchangeRate(string $currency_short_name): ?float
    {
        $currency = self::getCachedCurrency($currency_short_name);

        return $currency?->exchange_rate;
    }
}
