<?php

namespace Ingenius\Coins\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Ingenius\Coins\Features\CreateCoinFeature;
use Ingenius\Coins\Features\DeleteCoinFeature;
use Ingenius\Coins\Features\ListCoinsFeature;
use Ingenius\Coins\Features\SetMainCoinFeature;
use Ingenius\Coins\Features\UpdateCoinFeature;
use Ingenius\Coins\Features\ViewCoinFeature;
use Ingenius\Core\Services\FeatureManager;
use Ingenius\Core\Traits\RegistersConfigurations;
use Ingenius\Core\Traits\RegistersMigrations;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Ingenius\Coins\Initializers\CoinsTenantInitializer;
use Ingenius\Core\Support\TenantInitializationManager;
use Ingenius\Coins\Configuration\CoinStoreConfiguration;
use Ingenius\Core\Services\StoreConfigurationManager;

class CoinsServiceProvider extends ServiceProvider
{
    use RegistersMigrations, RegistersConfigurations;

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // Register migrations with the registry
        $this->registerMigrations(__DIR__ . '/../../database/migrations', 'coins');

        // Check if there's a tenant migrations directory and register it
        $tenantMigrationsPath = __DIR__ . '/../../database/migrations/tenant';
        if (is_dir($tenantMigrationsPath)) {
            $this->registerTenantMigrations($tenantMigrationsPath, 'coins');
        }

        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerCoinsConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register tenant initializer
        $this->registerTenantInitializer();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/coins.php', 'coins');

        // Register main configuration with the registry
        $this->registerConfig(__DIR__ . '/../../config/coins.php', 'coins', 'coins');

        // Register the route service provider
        $this->app->register(RouteServiceProvider::class);

        // Register the permission service provider
        $this->app->register(PermissionServiceProvider::class);

        // Register the event service provider
        $this->app->register(EventServiceProvider::class);

        $this->app->afterResolving(FeatureManager::class, function (FeatureManager $manager) {
            $manager->register(new ListCoinsFeature());
            $manager->register(new CreateCoinFeature());
            $manager->register(new ViewCoinFeature());
            $manager->register(new UpdateCoinFeature());
            $manager->register(new DeleteCoinFeature());
            $manager->register(new SetMainCoinFeature());
        });

        // Register store configuration extension
        $this->app->afterResolving(StoreConfigurationManager::class, function (StoreConfigurationManager $manager) {
            $manager->register(new CoinStoreConfiguration());
        });
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'coins');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../../resources/lang');
    }

    /**
     * Register additional coin configs.
     */
    protected function registerCoinsConfig(): void
    {
        $configPath = __DIR__ . '/../../config';

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());

                    // Skip the main config file as it's already registered in the register method
                    if ($config === 'coins.php') {
                        continue;
                    }

                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', 'coins.' . $config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = implode('.', $normalized);

                    // Register with the ConfigRegistry
                    $this->registerConfig($file->getPathname(), $key, 'coins');

                    // Also publish the config file
                    $this->publishes([$file->getPathname() => config_path($config)], 'config');

                    // And merge it into the current config
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'coins');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/coins'),
        ], 'coins-views');

        Blade::componentNamespace('Ingenius\\Coins\\View\\Components', 'coins');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Register tenant initializer
     */
    protected function registerTenantInitializer(): void
    {
        $this->app->afterResolving(TenantInitializationManager::class, function (TenantInitializationManager $manager) {
            $initializer = $this->app->make(CoinsTenantInitializer::class);
            $manager->register($initializer);
        });
    }
}
