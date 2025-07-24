<?php

namespace Ingenius\Coins\Services;

use Illuminate\Http\Request;
use Ingenius\Coins\Enums\CoinPosition;
use Ingenius\Coins\Models\Coin;

class CurrencyServices
{
    public static function formatCurrency(int $amount, string $currency_short_name): string
    {
        $currency = Coin::where('short_name', $currency_short_name)->first();

        if (!$currency) {
            throw new \Exception('Currency not found');
        }

        $numberFormatted = number_format($amount / 100.0, 2, '.', ',');

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
        return Coin::where('short_name', session()->get('current_currency'))->first();
    }

    public static function getCurrencyShortNameFromSession(): ?string
    {
        return session()->get('current_currency');
    }

    public static function getCurrencyFromRequest(Request $request): ?Coin
    {
        return Coin::where('short_name', $request->get('currency'))->first();
    }

    public static function getCurrencyShortNameFromRequest(Request $request): ?string
    {
        return $request->get('currency');
    }

    public static function getBaseCurrency(): ?Coin
    {
        return Coin::where('main', true)->first();
    }

    public static function getBaseCurrencyShortName(): ?string
    {
        return Coin::where('main', true)?->first()->short_name;
    }

    public static function getExchangeRate(string $currency_short_name): ?float
    {
        $currency = Coin::where('short_name', $currency_short_name)->first();

        return $currency->exchange_rate;
    }
}
