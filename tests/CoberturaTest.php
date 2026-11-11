<?php

namespace FacilitadorHooks\Tests;

use Carbon\Carbon;
use Facilitador\Facades\Facilitador;
use FacilitadorHooks\Controllers\HooksController;
use FacilitadorHooks\FacilitadorHooksServiceProvider;
use Hooks\Hooks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

/**
 * Cobre o provider e o controller de hooks.
 */
class CoberturaTest extends TestCase
{
    #[\Override]
    protected function getPackageProviders($app)
    {
        return [
            FacilitadorHooksServiceProvider::class,
        ];
    }

    #[\Override]
    protected function defineEnvironment($app): void
    {
        $app['config']->set('facilitador-hooks.enabled', true);
        $app['config']->set('hooks.enabled', true);
    }

    public function test_provider_registra_rotas_menu_e_permissoes(): void
    {
        $provider = $this->app->getProvider(FacilitadorHooksServiceProvider::class);

        $this->assertTrue($provider->enabled());

        $router = $this->app['router'];
        $provider->addHookRoute($router);
        $this->assertNotNull($router->getRoutes()->getByName('hooks'));

        Facilitador::$withMenu = false;
        $provider->addHookMenuItem();

        Facilitador::$withMenu = true;
        Facilitador::$withTools = true;
        Facilitador::$itemExists = false;
        $provider->addHookMenuItem();

        Facilitador::$itemExists = true;
        $provider->addHookMenuItem();
        $provider->addHookPermissions();

        $this->app['config']->set('hooks.enabled', false);
        $this->assertFalse($provider->enabled());

        $this->app['config']->set('facilitador-hooks.enabled', false);
        $this->assertFalse($provider->enabled());

        $again = new FacilitadorHooksServiceProvider($this->app);
        $again->register();
        $again->boot($this->app['events']);

        $this->app['config']->set('facilitador-hooks.enabled', true);
        $provider->publishVendorFiles();
    }

    public function test_controller_lista_e_altera_hooks(): void
    {
        $hooks = $this->createMock(Hooks::class);
        $hooks->method('getLastRemoteCheck')->willReturn(null);
        $hooks->expects($this->once())->method('setLastRemoteCheck')->with($this->isInstanceOf(Carbon::class));
        $hooks->expects($this->once())->method('remakeJson');
        $hooks->method('hooks')->willReturn([]);
        $hooks->expects($this->once())->method('install')->with('demo');
        $hooks->expects($this->once())->method('uninstall')->with('demo');
        $hooks->expects($this->once())->method('update')->with('demo', null);
        $hooks->expects($this->once())->method('enable')->with('demo');
        $hooks->expects($this->once())->method('disable')->with('demo');

        $request = Request::create('/hooks', 'POST', ['name' => 'demo']);
        $request->headers->set('referer', 'https://app.test/hooks?ok=1');

        $controller = new class($hooks, $request) extends HooksController
        {
            #[\Override]
            public function authorize($ability, $arguments = [])
            {
                return true;
            }
        };
        $view = $controller->index($request);
        $this->assertSame('facilitador-hooks::browse', $view->name());

        foreach (['install', 'uninstall', 'update', 'enable', 'disable'] as $action) {
            $response = $action === 'install' ? $controller->install() : $controller->{$action}('demo');
            $this->assertInstanceOf(RedirectResponse::class, $response);
            $this->assertStringContainsString('message=', $response->getTargetUrl());
        }
    }

    public function test_indice_reusa_a_data_da_ultima_consulta(): void
    {
        $hooks = $this->createMock(Hooks::class);
        $hooks->method('getLastRemoteCheck')->willReturn(Carbon::now()->subDays(12));
        $hooks->expects($this->never())->method('setLastRemoteCheck');
        $hooks->method('hooks')->willReturn([]);

        $request = Request::create('/hooks', 'GET');
        $controller = new class($hooks, $request) extends HooksController
        {
            #[\Override]
            public function authorize($ability, $arguments = [])
            {
                return true;
            }
        };
        $view = $controller->index($request);

        $this->assertGreaterThanOrEqual(11, (int) abs($view->getData()['daysSinceLastCheck']));
    }
}
