<?php

namespace FacilitadorHooks\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Hooks\Hooks;

class HooksController extends Controller
{
    use AuthorizesRequests;

    protected $hooks;
    protected $request;

    public function __construct(Hooks $hooks, Request $request)
    {
        $this->hooks = $hooks;
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Check permission
        $this->authorize('browse_hooks');

        $lastUpdated = $this->hooks->getLastRemoteCheck();

        if (is_null($lastUpdated)) {
            $lastUpdated = Carbon::now();
            $this->hooks->setLastRemoteCheck($lastUpdated);
            $this->hooks->remakeJson();
        }

        return view(
            'facilitador-hooks::browse', [
            'hooks'              => $this->hooks->hooks(),
            'daysSinceLastCheck' => $lastUpdated->diffInDays(Carbon::now()),
            ]
        );
    }

    /**
     * @return empty
     */
    public function install()
    {
        // Check permission
        $this->authorize('browse_hooks');

        $name = $this->request->get('name');
        $this->hooks->install($name);

        return $this->redirect("Hook [{$name}] have been installed!");
    }

    /**
     * @return empty
     */
    public function uninstall($name)
    {
        // Check permission
        $this->authorize('browse_hooks');

        $this->hooks->uninstall($name);

        return $this->redirect("Hook [{$name}] have been uninstalled!");
    }

    /**
     * @return empty
     */
    public function update($name)
    {
        // Check permission
        $this->authorize('browse_hooks');

        $this->hooks->update($name, null);

        return $this->redirect("Hook [{$name}] have been updated!");
    }

    /**
     * @return empty
     */
    public function enable($name)
    {
        // Check permission
        $this->authorize('browse_hooks');

        $this->hooks->enable($name);

        return $this->redirect("Hook [{$name}] have been enabled!");
    }

    /**
     * @return empty
     */
    public function disable($name)
    {
        // Check permission
        $this->authorize('browse_hooks');

        $this->hooks->disable($name);

        return $this->redirect("Hook [{$name}] have been disabled!");
    }

    /**
     * Volta para a página de origem com a mensagem na query string.
     */
    protected function redirect(string $message): RedirectResponse
    {
        $referer = $this->request->server('HTTP_REFERER');
        $location = head(explode('?', (string) $referer));

        return new RedirectResponse($location.'?message='.urlencode($message));
    }
}
