<?php

namespace Ingenius\Coins\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Ingenius\Coins\Enums\CoinPosition;
use Ingenius\Coins\Models\Coin;

class CoinFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Coin::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->currencyCode(),
            'short_name' => $this->faker->currencyCode(),
            'symbol' => $this->faker->randomElement(['$', '€', '£', '¥']),
            'position' => $this->faker->randomElement([CoinPosition::FRONT, CoinPosition::BACK]),
            'active' => $this->faker->boolean(),
            'main' => false,
            'exchange_rate' => $this->faker->randomFloat(2, 0.1, 10),
            'exchange_rate_history' => null,
        ];
    }
}
