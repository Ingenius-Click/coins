<?php

namespace Ingenius\Coins\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ingenius\Coins\Models\Coin;

class ShowCoinAction
{
    /**
     * Get a specific coin by ID
     *
     * @param int $id
     * @return Coin
     * @throws ModelNotFoundException
     */
    public function __invoke(int $id): Coin
    {
        return Coin::findOrFail($id);
    }
}
