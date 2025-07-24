<?php

namespace Ingenius\Coins\Providers;

use Illuminate\Support\ServiceProvider;
use Ingenius\Coins\Constants\CoinsPermissions;
use Ingenius\Core\Support\PermissionsManager;
use Ingenius\Core\Traits\RegistersConfigurations;

class PermissionServiceProvider extends ServiceProvider
{
    use RegistersConfigurations;

    /**
     * The module name.
     *
     * @var string
     */
    protected string $moduleName = 'Coins';

    /**
     * Boot the application events.
     */
    public function boot(PermissionsManager $permissionsManager): void
    {
        $this->registerPermissions($permissionsManager);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Register module-specific permission config
        $configPath = __DIR__ . '/../../config/permission.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'coins.permission');
            $this->registerConfig($configPath, 'coins.permission', 'coins');
        }
    }

    /**
     * Register the module's permissions.
     */
    protected function registerPermissions(PermissionsManager $permissionsManager): void
    {
        // Register Coins module permissions
        $permissionsManager->registerMany([
            CoinsPermissions::COINS_VIEW => 'View coins',
            CoinsPermissions::COINS_CREATE => 'Create coins',
            CoinsPermissions::COINS_EDIT => 'Edit coins',
            CoinsPermissions::COINS_DELETE => 'Delete coins',
            CoinsPermissions::COINS_SET_MAIN => 'Set main coin',
        ], $this->moduleName, 'tenant');
    }
}
