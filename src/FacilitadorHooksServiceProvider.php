<?php

namespace FacilitadorHooks;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Hooks\Events\Setup;
use Hooks\HooksServiceProvider;
use Facilitador\Facades\Facilitador;

class FacilitadorHooksServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    #[\Override]
    public function register()
    {
        $configPath = dirname(__DIR__).'/publishable/config/facilitador-hooks.php';

        $this->mergeConfigFrom($configPath, 'facilitador-hooks');

        // Register the HooksServiceProvider
        $this->app->register(HooksServiceProvider::class);

        if (!$this->enabled()) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$configPath => config_path('facilitador-hooks.php')],
                'facilitador-hooks-config'
            );
        }

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'facilitador-hooks');
    }

    /**
     * Bootstrap the application services.
     *
     * @param \Illuminate\Events\Dispatcher $events
     *
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        if (!$this->enabled()) {
            return;
        }

        if (\Illuminate\Support\Facades\Config::get('facilitador-hooks.add-route', true)) {
            $events->listen('facilitador.admin.routing', [$this, 'addHookRoute']);
        }

        if (\Illuminate\Support\Facades\Config::get('facilitador-hooks.add-hook-menu-item', true)) {
            $events->listen(Setup::class, [$this, 'addHookMenuItem']);
        }

        if (\Illuminate\Support\Facades\Config::get('facilitador-hooks.add-hook-permissions', true)) {
            $events->listen(Setup::class, [$this, 'addHookPermissions']);
        }

        if (\Illuminate\Support\Facades\Config::get('facilitador-hooks.publish-vendor-files', true)) {
            $events->listen(Setup::class, [$this, 'publishVendorFiles']);
        }
    }

    public function addHookRoute($router): void
    {
        $namespacePrefix = '\\FacilitadorHooks\\Controllers\\';

        $router->get('hooks', ['uses' => $namespacePrefix.'HooksController@index', 'as' => 'hooks']);
        $router->get('hooks/{name}/enable', ['uses' => $namespacePrefix.'HooksController@enable', 'as' => 'hooks.enable']);
        $router->get('hooks/{name}/disable', ['uses' => $namespacePrefix.'HooksController@disable', 'as' => 'hooks.disable']);
        $router->get('hooks/{name}/update', ['uses' => $namespacePrefix.'HooksController@update', 'as' => 'hooks.update']);
        $router->post('hooks', ['uses' => $namespacePrefix.'HooksController@install', 'as' => 'hooks.install']);
        $router->delete('hooks/{name}', ['uses' => $namespacePrefix.'HooksController@uninstall', 'as' => 'hooks.uninstall']);
    }

    /**
     * @return void
     */
    public function addHookMenuItem()
    {
        $menu = Facilitador::model('Menu')::where('name', 'admin')->first();

        if (is_null($menu)) {
            return;
        }

        $parentId = null;

        $toolsMenuItem = Facilitador::model('MenuItem')::where('menu_id', $menu->id)
            ->where('title', 'Tools')
            ->first();

        if ($toolsMenuItem) {
            $parentId = $toolsMenuItem->id;
        }

        $menuItem = Facilitador::model('MenuItem')::firstOrNew(
            [
            'menu_id' => $menu->id,
            'title'   => 'Hooks',
            'url'     => '',
            'route'   => 'facilitador.hooks',
            ]
        );

        if (!$menuItem->exists) {
            $menuItem->fill(
                [
                'target'     => '_self',
                'icon_class' => 'facilitador-hook',
                'color'      => null,
                'parent_id'  => $parentId,
                'order'      => 13,
                ]
            )->save();
        }
    }

    public function addHookPermissions(): void
    {
        Facilitador::model('Permission')::firstOrCreate(
            [
            'key'        => 'browse_hooks',
            'table_name' => null,
            ]
        );
    }

    public function publishVendorFiles(): void
    {
        Artisan::call('vendor:publish', ['--provider' => static::class]);
    }

    public function enabled()
    {
        if (\Illuminate\Support\Facades\Config::get('facilitador-hooks.enabled', true)) {
            return \Illuminate\Support\Facades\Config::get('hooks.enabled', true);
        }

        return \Illuminate\Support\Facades\Config::get('facilitador-hooks.enabled', true);
    }
}
