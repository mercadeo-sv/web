<?php
spl_autoload_register(function ($class) {
    // PHPSpreadsheet
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    $base_dir = __DIR__ . '/phpoffice/PhpSpreadsheet/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // PSR Simple Cache
    $prefix = 'Psr\\SimpleCache\\';
    $base_dir = __DIR__ . '/psr/simple-cache/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // PSR Container
    $prefix = 'Psr\\Container\\';
    $base_dir = __DIR__ . '/psr/container/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
