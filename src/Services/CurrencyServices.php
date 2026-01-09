<?php

namespace Ingenius\Coins\Services;

use Illuminate\Http\Request;
use Ingenius\Coins\Enums\CoinPosition;
use Ingenius\Coins\Models\Coin;

class CurrencyServices
{
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
     * Checks request attributes (set by middleware), session, then falls back to base currency.
     *
     * @return string Currency short name (e.g., 'USD', 'EUR')
     */
    public static function getCurrentCurrency(): string
    {
        // Priority 1: Check request attributes (set by CurrencyMiddleware)
        $request = app(Request::class);
        if ($request && $request->attributes->has('current_currency')) {
            return $request->attributes->get('current_currency');
        }

        // Priority 2: Check session
        $fromSession = self::getCurrencyShortNameFromSession();
        if ($fromSession) {
            return $fromSession;
        }

        // Priority 3: Fallback to base currency
        return self::getBaseCurrencyShortName() ?? 'USD';
    }

    /**
     * Get metadata for the current currency.
     *
     * @return array
     */
    public static function getCurrentCurrencyMetadata(): array
    {
        $currencyCode = self::getCurrentCurrency();
        $coin = self::getCachedCurrency($currencyCode);

        if (!$coin) {
            $coin = self::getBaseCurrency();
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
    public static function convertAmount(int $amountInCents, array $context): int
    {
        $toCurrency = $context['to_currency'] ?? self::getCurrentCurrency();
        $fromCurrency = $context['from_currency'] ?? self::getBaseCurrencyShortName();

        // If converting to same currency, return as-is
        if ($toCurrency === $fromCurrency) {
            return $amountInCents;
        }

        // Get exchange rates
        $fromRate = self::getExchangeRate($fromCurrency) ?? 1.0;
        $toRate = self::getExchangeRate($toCurrency) ?? 1.0;

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
    public static function formatAmountWithCurrency(int $amountInCents, array $context): string
    {
        $currencyCode = $context['currency_code'] ?? self::getCurrentCurrency();
        return self::formatCurrency($amountInCents, $currencyCode);
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

    public static function setCurrencyIntoSession(string $currency_short_name): void
    {
        session()->put('current_currency', $currency_short_name);
    }

    public static function getSystemCurrencyShortName(): ?string
    {
        $fromSession = self::getCurrencyShortNameFromSession();

        if ($fromSession) {
            return $fromSession;
        }

        return self::getBaseCurrencyShortName();
    }

    public static function getCurrencyFromSession(): ?Coin
    {
        $currencyCode = session()->get('current_currency');
        return $currencyCode ? self::getCachedCurrency($currencyCode) : null;
    }

    public static function getCurrencyShortNameFromSession(): ?string
    {
        return session()->get('current_currency');
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
