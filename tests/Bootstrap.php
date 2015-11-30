<?php
ini_set('error_reporting', E_ALL);
$files = [__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../autoload.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $loader = require $file;
        unset($loader, $file, $files);
        return;
    }
}
throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');