<?php

namespace Ingenius\Coins\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Ingenius\Coins\Models\Coin;

class ListCoinsAction
{
    /**
     * List all coins with optional filtering
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function __invoke(array $filters = []): LengthAwarePaginator
    {
        $query = Coin::query();

        // Apply filters if provided
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['main'])) {
            $query->where('main', $filters['main']);
        }

        return $query->latest()->paginate(
            $filters['per_page'] ?? 15
        );
    }
}
