<?php

namespace Ingenius\Coins\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Ingenius\Coins\Models\Coin;

class UpdateCoinAction
{
    /**
     * Update a coin
     *
     * @param Coin $coin
     * @param array $data
     * @return Coin
     * @throws ModelNotFoundException
     */
    public function __invoke(Coin $coin, array $data): Coin
    {
        return DB::transaction(function () use ($coin, $data) {
            // If this coin is being set as main, unset any existing main coin
            if (isset($data['main']) && $data['main'] && !$coin->main) {
                Coin::where('main', true)->update(['main' => false]);
            }

            $coin->update($data);
            return $coin->fresh();
        });
    }
}
