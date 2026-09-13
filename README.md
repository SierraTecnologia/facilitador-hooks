![Facilitador Hooks](https://raw.githubusercontent.com/sierratecnologia/facilitador-hooks/master/logo.png)

<p align="center">
<a href="https://travis-ci.org/sierratecnologia/facilitador-hooks"><img src="https://travis-ci.org/sierratecnologia/facilitador-hooks.svg?branch=master" alt="Build Status"></a>
<a href="https://styleci.io/repos/76975411/shield?style=flat"><img src="https://styleci.io/repos/76975411/shield?style=flat" alt="Build Status"></a>
<a href="https://packagist.org/packages/sierratecnologia/facilitador-hooks"><img src="https://poser.pugx.org/sierratecnologia/facilitador-hooks/downloads.svg?format=flat" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/sierratecnologia/facilitador-hooks"><img src="https://poser.pugx.org/sierratecnologia/facilitador-hooks/v/stable.svg?format=flat" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/sierratecnologia/facilitador-hooks"><img src="https://poser.pugx.org/sierratecnologia/facilitador-hooks/license.svg?format=flat" alt="License"></a>
</p>

Made with ❤️ by [Mark Topper](https://marktopper.com)

# Facilitador Hooks

[Hooks](https://github.com/sierratecnologia/hooks) system integrated into [Facilitador](https://github.com/the-control-group/facilitador).

# Installation

Install using composer:

```
composer require sierratecnologia/facilitador-hooks
```

Laravel 13 discovers the service provider automatically. If you need to register it explicitly:

```php
'providers' => [
    Hooks\FacilitadorHooksServiceProvider::class,
],
```

In order for Facilitador to automatically check for updates of hooks, add the following to your console kernel:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('hook:check')->sundays()->at('03:00');
}
```

That's it! You can now visit your Facilitador admin panel and see a new menu item called `Hooks` have been added.
