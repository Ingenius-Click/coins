<?php

namespace Ingenius\Coins\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ingenius\Coins\Services\CurrencyServices;
use Symfony\Component\HttpFoundation\Response;

/**
 * CurrencyMiddleware
 *
 * Determines which currency to use for the current request based on priority:
 * 1. X-Currency request header
 * 2. 'currency' query parameter
 * 3. Base currency (fallback)
 *
 * Sets the resolved currency on the CurrencyServices singleton for use throughout the request lifecycle.
 */
class CurrencyMiddleware
{
    public function __construct(
        protected CurrencyServices $currencyServices
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currency = $this->resolveCurrency($request);

        $this->currencyServices->setCurrentCurrency($currency);

        return $next($request);
    }

    /**
     * Resolve the currency for the current request based on priority.
     *
     * Priority order:
     * 1. Request header (X-Currency)
     * 2. Request parameter (currency)
     * 3. Base currency (fallback)
     *
     * @param Request $request
     * @return string Currency code (e.g., 'USD', 'EUR')
     */
    protected function resolveCurrency(Request $request): string
    {
        // Priority 1: Check request header
        if ($request->hasHeader('X-Currency')) {
            $currency = $request->header('X-Currency');
            if ($this->isValidCurrency($currency)) {
                return $currency;
            }
        }

        // Priority 2: Check request parameter
        if ($request->has('currency')) {
            $currency = $request->input('currency');
            if ($this->isValidCurrency($currency)) {
                return $currency;
            }
        }

        // Priority 3: Fallback to base currency
        return $this->currencyServices->getBaseCurrencyShortName();
    }

    /**
     * Validate if the currency code exists and is active.
     *
     * @param string|null $currencyCode
     * @return bool
     */
    protected function isValidCurrency(?string $currencyCode): bool
    {
        if (!$currencyCode) {
            return false;
        }

        try {
            return $this->currencyServices->getExchangeRate($currencyCode) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
