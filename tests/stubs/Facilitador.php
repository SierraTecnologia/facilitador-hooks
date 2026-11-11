<?php

namespace Facilitador\Facades;

require_once __DIR__.'/Models.php';

/**
 * Substituto do Facilitador só para a suíte deste pacote.
 */
class Facilitador
{
    public static bool $withMenu = false;

    public static bool $withTools = false;

    public static bool $itemExists = true;

    public static function model(string $name): string
    {
        return match ($name) {
            'Menu' => \FacilitadorHooks\Tests\Stubs\MenuStub::class,
            'MenuItem' => \FacilitadorHooks\Tests\Stubs\MenuItemStub::class,
            default => \FacilitadorHooks\Tests\Stubs\PermissionStub::class,
        };
    }
}
