<?php

spl_autoload_register(static function (string $class): void {
    if ($class === 'Facilitador\\Facades\\Facilitador') {
        require __DIR__.'/stubs/Facilitador.php';
    }
});

require dirname(__DIR__).'/vendor/autoload.php';
