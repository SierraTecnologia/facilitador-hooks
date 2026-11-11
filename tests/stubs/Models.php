<?php

namespace FacilitadorHooks\Tests\Stubs;

use Facilitador\Facades\Facilitador;

/**
 * Consulta falsa de menu.
 */
class MenuStub
{
    public int $id = 7;

    public static function where(string $column, mixed $value = null): self
    {
        return new self();
    }

    public function first(): ?self
    {
        return Facilitador::$withMenu ? $this : null;
    }
}

/**
 * Consulta falsa de item de menu.
 */
class MenuItemQuery
{
    public function where(string $column, mixed $value = null): self
    {
        return $this;
    }

    public function first(): ?MenuItemStub
    {
        return Facilitador::$withTools ? new MenuItemStub() : null;
    }
}

class MenuItemStub
{
    public int $id = 3;

    public bool $exists = true;

    public static function where(string $column, mixed $value = null): MenuItemQuery
    {
        return new MenuItemQuery();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function firstOrNew(array $attributes): self
    {
        $item = new self();
        $item->exists = Facilitador::$itemExists;

        return $item;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function fill(array $attributes): self
    {
        return $this;
    }

    public function save(): bool
    {
        return true;
    }
}

/**
 * Consulta falsa de permissão.
 */
class PermissionStub
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function firstOrCreate(array $attributes): self
    {
        return new self();
    }
}
