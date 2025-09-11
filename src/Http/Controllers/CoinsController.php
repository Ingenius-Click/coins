<?php

namespace Ingenius\Coins\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Ingenius\Auth\Helpers\AuthHelper;
use Ingenius\Coins\Actions\CreateCoinAction;
use Ingenius\Coins\Actions\DeleteCoinAction;
use Ingenius\Coins\Actions\ListCoinsAction;
use Ingenius\Coins\Actions\SetMainCoinAction;
use Ingenius\Coins\Actions\UpdateCoinAction;
use Ingenius\Coins\Http\Requests\CoinRequest;
use Ingenius\Coins\Models\Coin;

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

        return response()->api('Coins retrieved successfully', $coins);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CoinRequest $request, CreateCoinAction $createCoinAction): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'create', Coin::class);

        $coin = $createCoinAction($request->validated());

        return response()->api('Coin created successfully', $coin, 201);
    }

    /**
     * Show the specified resource.
     */
    public function show(Coin $coin): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'view', $coin);

        return response()->api('Coin retrieved successfully', $coin);
    }

    public function showByCode(string $code): JsonResponse
    {
        $coin = Coin::where('code', $code)->firstOrFail();

        return response()->api('Coin retrieved successfully', $coin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CoinRequest $request, Coin $coin, UpdateCoinAction $updateCoinAction): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'update', $coin);

        $coin = $updateCoinAction($coin, $request->validated());

        return response()->api('Coin updated successfully', $coin);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coin $coin, DeleteCoinAction $deleteCoinAction): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'delete', $coin);

        $deleteCoinAction($coin);

        return response()->api('Coin deleted successfully', null, 204);
    }

    /**
     * Set the specified coin as main.
     */
    public function setMain($coin, SetMainCoinAction $setMainCoinAction): JsonResponse
    {
        $coin = $setMainCoinAction($coin);

        return response()->api('Coin set as main successfully', $coin);
    }
}
