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
        $permissionsManager->register(
            CoinsPermissions::COINS_VIEW,
            'View coins',
            $this->moduleName,
            'tenant',
            __('coins::permissions.display_names.view_coins'),
            __('coins::permissions.groups.coins')
        );

        $permissionsManager->register(
            CoinsPermissions::COINS_CREATE,
            'Create coins',
            $this->moduleName,
            'tenant',
            __('coins::permissions.display_names.create_coins'),
            __('coins::permissions.groups.coins')
        );

        $permissionsManager->register(
            CoinsPermissions::COINS_EDIT,
            'Edit coins',
            $this->moduleName,
            'tenant',
            __('coins::permissions.display_names.edit_coins'),
            __('coins::permissions.groups.coins')
        );

        $permissionsManager->register(
            CoinsPermissions::COINS_DELETE,
            'Delete coins',
            $this->moduleName,
            'tenant',
            __('coins::permissions.display_names.delete_coins'),
            __('coins::permissions.groups.coins')
        );

        $permissionsManager->register(
            CoinsPermissions::COINS_SET_MAIN,
            'Set main coin',
            $this->moduleName,
            'tenant',
            __('coins::permissions.display_names.set_main_coin'),
            __('coins::permissions.groups.coins')
        );
    }
}
