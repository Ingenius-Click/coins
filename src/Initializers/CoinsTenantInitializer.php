<?php

namespace Ingenius\Coins\Initializers;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Ingenius\Coins\Actions\CreateCoinAction;
use Ingenius\Coins\Enums\CoinPosition;
use Ingenius\Core\Interfaces\TenantInitializer;
use Ingenius\Core\Models\Tenant;

class CoinsTenantInitializer implements TenantInitializer
{
    /**
     * Predefined currencies
     */
    protected const CURRENCIES = [
        'USD' => [
            'name' => 'US Dollar',
            'short_name' => 'USD',
            'symbol' => '$',
            'position' => CoinPosition::FRONT,
        ],
        'EUR' => [
            'name' => 'Euro',
            'short_name' => 'EUR',
            'symbol' => '€',
            'position' => CoinPosition::BACK,
        ],
    ];

    /**
     * Create a new initializer instance.
     */
    public function __construct(
        protected CreateCoinAction $createCoinAction
    ) {}

    /**
     * Initialize a new tenant with required coins data
     *
     * @param Tenant $tenant
     * @param Command $command
     * @return void
     */
    public function initialize(Tenant $tenant, Command $command): void
    {
        // Create main coin based on user input
        $this->createMainCoin($command);
    }

    public function initializeViaRequest(Tenant $tenant, Request $request): void
    {
        $coinData = self::CURRENCIES['USD'];

        if ($request->custom_main_coin) {
            $coinData = [
                'name' => $request->main_coin_name,
                'short_name' => $request->main_coin_short_name,
                'symbol' => $request->main_coin_symbol,
                'position' => $request->main_coin_position,
            ];
        }

        $coinData['active'] = true;
        $coinData['main'] = true;
        $coinData['exchange_rate'] = 1;

        ($this->createCoinAction)($coinData);
    }

    public function rules(): array
    {
        return [
            'custom_main_coin' => 'required|boolean',
            'main_coin_name' => 'required_if:custom_main_coin,true|string|max:255',
            'main_coin_short_name' => 'required_if:custom_main_coin,true|string|max:255',
            'main_coin_symbol' => 'required_if:custom_main_coin,true|string|max:255',
            'main_coin_position' => 'required_if:custom_main_coin,true|string|in:' . implode(',', [CoinPosition::FRONT->value, CoinPosition::BACK->value]),
        ];
    }

    /**
     * Get the priority of this initializer
     * Higher priority initializers run first
     *
     * @return int
     */
    public function getPriority(): int
    {
        // Coins should run after auth but before payforms
        return 90;
    }

    /**
     * Get the name of this initializer
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Currency Setup';
    }

    /**
     * Get the package name of this initializer
     *
     * @return string
     */
    public function getPackageName(): string
    {
        return 'coins';
    }

    /**
     * Create main coin based on user input
     * 
     * @param Command $command
     * @return void
     */
    protected function createMainCoin(Command $command): void
    {
        $command->info('Setting up main currency...');

        // Ask user to select a predefined currency or create a custom one
        $currencyOptions = array_merge(['Custom'], array_keys(self::CURRENCIES));
        $selectedCurrency = $command->choice('Select main currency', $currencyOptions, 'USD');

        $coinData = [];

        if ($selectedCurrency === 'Custom') {
            // Prompt for custom currency details
            $coinData = [
                'name' => $command->ask('Currency name', 'Custom Currency'),
                'short_name' => $command->ask('Currency short name (e.g. USD)', 'XYZ'),
                'symbol' => $command->ask('Currency symbol', '$'),
                'position' => $command->choice(
                    'Symbol position',
                    ['front' => 'Before amount (e.g. $100)', 'back' => 'After amount (e.g. 100€)'],
                    'front'
                ) === 'front' ? CoinPosition::FRONT : CoinPosition::BACK,
            ];
        } else {
            // Use predefined currency
            $coinData = self::CURRENCIES[$selectedCurrency];
        }

        // Add common properties
        $coinData['active'] = true;
        $coinData['main'] = true;
        $coinData['exchange_rate'] = 1;

        // Create the coin
        ($this->createCoinAction)($coinData);

        $command->info("Main currency '{$coinData['name']}' ({$coinData['short_name']}) created successfully");
    }
}
