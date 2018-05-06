<?php

require __DIR__ . '/../vendor/autoload.php';

echo 'INFO: GMP installed:' . (extension_loaded('gmp') ? 'yes' : 'no') . PHP_EOL;
echo 'INFO: GD installed:' . (extension_loaded('gd') ? 'yes' : 'no') . PHP_EOL;
echo 'INFO: ImageMagick installed:' . (extension_loaded('imagick') ? 'yes' : 'no') . PHP_EOL;
