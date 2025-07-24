<?php

namespace Ingenius\Coins\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Ingenius\Coins\Models\Coin;

class DeleteCoinAction
{
    /**
     * Delete a coin
     *
     * @param Coin $coin
     * @param bool $force Force deletion even if it's the main coin
     * @return bool
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function __invoke(Coin $coin, bool $force = false): bool
    {
        // Check if it's the main coin and not forced
        if ($coin->main && !$force) {
            throw ValidationException::withMessages([
                'id' => ['Cannot delete the main coin. Set another coin as main first.'],
            ]);
        }

        return $coin->delete();
    }
}
