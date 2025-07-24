<?php

namespace Ingenius\Coins\Actions;

use Illuminate\Support\Facades\DB;
use Ingenius\Coins\Models\Coin;

class CreateCoinAction
{
    /**
     * Create a new coin
     *
     * @param array $data
     * @return Coin
     */
    public function __invoke(array $data): Coin
    {
        // Start a transaction to ensure data consistency
        return DB::transaction(function () use ($data) {
            // If this coin is set as main, unset any existing main coin
            if (isset($data['main']) && $data['main']) {
                Coin::where('main', true)->update(['main' => false]);
            }

            // Create the new coin
            return Coin::create($data);
        });
    }
}
