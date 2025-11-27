<?php

namespace Ingenius\Coins\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Ingenius\Auth\Helpers\AuthHelper;
use Ingenius\Coins\Actions\CreateCoinAction;
use Ingenius\Coins\Actions\DeleteCoinAction;
use Ingenius\Coins\Actions\ListCoinsAction;
use Ingenius\Coins\Actions\SetMainCoinAction;
use Ingenius\Coins\Actions\UpdateCoinAction;
use Ingenius\Coins\Http\Requests\CoinRequest;
use Ingenius\Coins\Models\Coin;
use Ingenius\Coins\Services\CurrencyServices;

class CoinsController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ListCoinsAction $listCoinsAction): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'viewAny', Coin::class);

        $filters = $request->only(['active', 'main', 'per_page']);
        $coins = $listCoinsAction($filters);

        return Response::api('Coins retrieved successfully', $coins);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CoinRequest $request, CreateCoinAction $createCoinAction): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'create', Coin::class);

        $coin = $createCoinAction($request->validated());

        return Response::api('Coin created successfully', $coin, 201);
    }

    /**
     * Show the specified resource.
     */
    public function show(Coin $coin): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'view', $coin);

        return Response::api('Coin retrieved successfully', $coin);
    }

    public function showByCode(string $code): JsonResponse
    {
        $coin = Coin::where('code', $code)->firstOrFail();

        return Response::api('Coin retrieved successfully', $coin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CoinRequest $request, Coin $coin, UpdateCoinAction $updateCoinAction): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'update', $coin);

        $coin = $updateCoinAction($coin, $request->validated());

        return Response::api('Coin updated successfully', $coin);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coin $coin, DeleteCoinAction $deleteCoinAction): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'delete', $coin);

        $deleteCoinAction($coin);

        return Response::api('Coin deleted successfully', null, 204);
    }

    /**
     * Set the specified coin as main.
     */
    public function setMain($coin, SetMainCoinAction $setMainCoinAction): JsonResponse
    {
        $coin = $setMainCoinAction($coin);

        return Response::api('Coin set as main successfully', $coin);
    }

    /**
     * Set the current currency for the user session.
     * This endpoint allows frontend clients to switch the display currency.
     */
    public function setCurrency(Request $request, CurrencyServices $currencyServices): JsonResponse
    {
        $request->validate([
            'currency' => 'required|string|size:3',
        ]);

        $currencyCode = strtoupper($request->input('currency'));


        // Validate that the currency exists and is active
        $coin = Coin::where('short_name', $currencyCode)
            ->where('active', true)
            ->first();

        if (!$coin) {
            return Response::api('Invalid or inactive currency', null, 400);
        }

        // Store currency in session
        $currencyServices->setCurrencyIntoSession($currencyCode);

        return Response::api('Currency set successfully', [
            'short_name' => $coin->short_name,
            'name' => $coin->name,
            'symbol' => $coin->symbol,
            'position' => $coin->position->value,
            'exchange_rate' => $coin->exchange_rate,
        ]);
    }

    /**
     * Get the current currency for the user session.
     */
    public function getCurrentCurrency(CurrencyServices $currencyServices): JsonResponse
    {
        $currentCurrency = $currencyServices->getSystemCurrencyShortName();

        $coin = Coin::where('short_name', $currentCurrency)
            ->where('active', true)
            ->first();

        if (!$coin) {
            // Fallback to base currency
            $coin = $currencyServices->getBaseCurrency();
        }

        return Response::api('Current currency retrieved successfully', [
            'short_name' => $coin->short_name,
            'name' => $coin->name,
            'symbol' => $coin->symbol,
            'position' => $coin->position->value,
            'exchange_rate' => $coin->exchange_rate,
        ]);
    }
}
