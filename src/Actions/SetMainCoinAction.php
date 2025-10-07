<?php

namespace Ingenius\Coins\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Ingenius\Coins\Models\Coin;

class SetMainCoinAction
{
    /**
     * Set a coin as the main coin and recalculate all exchange rates
     *
     * @param int $id
     * @return Coin
     * @throws ModelNotFoundException
     */
    public function __invoke(int $id): Coin
    {
        $newMainCoin = Coin::findOrFail($id);

        // Only proceed if the coin isn't already the main one
        if (!$newMainCoin->main) {
            // Store the new main coin's current exchange rate before making it main
            $newMainCoinOldRate = $newMainCoin->exchange_rate;

            // Get all other coins that need rate recalculation
            $otherCoins = Coin::where('id', '!=', $newMainCoin->id)->get();

            DB::transaction(function () use ($newMainCoin, $newMainCoinOldRate, $otherCoins) {
                // Unset any existing main coin
                Coin::where('main', true)->update(['main' => false]);

                // Set this coin as main and exchange rate to 1
                $newMainCoin->update([
                    'main' => true,
                    'exchange_rate' => 1
                ]);

                // Recalculate exchange rates for all other coins
                foreach ($otherCoins as $coin) {
                    // Apply formula: new_rate = 1/((new_main_old_rate)/(other_coin_old_rate))
                    // Or simplified: new_rate = (other_coin_old_rate / new_main_old_rate)
                    $newRate =  round($coin->exchange_rate / $newMainCoinOldRate, 4);

                    // Update the coin with new exchange rate
                    $coin->update(['exchange_rate' => $newRate]);
                }
            });
        }

        return $newMainCoin->fresh();
    }
}
