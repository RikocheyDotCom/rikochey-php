<?php

/*
 * Autoloader for projects without Composer — a WordPress plugin, a legacy script:
 *
 *     require __DIR__.'/rikochey-php/autoload.php';
 *
 * With Composer, the package's PSR-4 autoloading makes this file unnecessary.
 */

spl_autoload_register(static function (string $class): void {
    $prefix = 'Rikochey\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $path = __DIR__.'/src/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';

    if (is_file($path)) {
        require $path;
    }
});
