<?php include 'vendor/autoload.php';

use phpseclib\Math\BigInteger;

$x = new BigInteger(-4051207553416522877);

echo $x->toHex();
