<?php

require __DIR__ . '/../vendor/autoload.php';

echo 'INFO: GMP installed: ' . (extension_loaded('gmp') ? '✅' : '❌') . PHP_EOL;
echo 'INFO: GD installed: ' . (extension_loaded('gd') ? '✅' : '❌') . PHP_EOL;
echo 'INFO: ImageMagick installed: ' . (extension_loaded('imagick') ? '✅' : '❌') . PHP_EOL;
