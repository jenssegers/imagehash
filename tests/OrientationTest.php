<?php

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;

class OrientationTest extends PHPUnit_Framework_TestCase
{
    protected $precision = 5;

    public static function setUpBeforeClass()
    {
        if (extension_loaded('gmp')) {
            echo "INFO: gmp extension loaded \n";
        } else {
            echo "INFO: gmp extension not loaded \n";
        }
    }

    public function setUp()
    {
        $this->hashers = [
            new AverageHash,
            new DifferenceHash,
            new PerceptualHash,
        ];
    }

    public function testEqualHashes()
    {
        foreach ($this->hashers as $hasher) {
            $score = 0;
            $imageHash = new ImageHash($hasher);
            $imageHash->autorotate = true;
            $imageHash->autoflip = true;
            $images = glob('tests/images/orientation/match*');

            $hashes = [];
            foreach ($images as $image) {
                $hashes[$image] = $hash = $imageHash->hash($image);

                echo "[" . get_class($hasher) . "] $image = $hash \n";
            }

            foreach ($hashes as $image => $hash) {
                foreach ($hashes as $target => $compare) {
                    if ($target == $image) {
                        continue;
                    }

                    $distance = $imageHash->distance($hash, $compare);
                    $this->assertLessThan($this->precision, $distance, "[" . get_class($hasher) . "] $image ($hash) ^ $target ($compare)");
                    $score += $distance;

                    echo "[" . get_class($hasher) . "] $image ^ $target = $distance \n";
                }
            }

            echo  "[" . get_class($hasher) . "] Total score: $score \n";
        }
    }

    public function testDifferentHashes()
    {
        foreach ($this->hashers as $hasher) {
            $score = 0;
            $imageHash = new ImageHash($hasher);
            $imageHash->autorotate = true;
            $imageHash->autoflip = true;
            $images = glob('tests/images/orientation/mismatch*');

            $hashes = [];
            foreach ($images as $image) {
                $hashes[$image] = $hash = $imageHash->hash($image);

                echo "[" . get_class($hasher) . "] $image = $hash \n";
            }

            foreach ($hashes as $image => $hash) {
                foreach ($hashes as $target => $compare) {
                    if ($target == $image) {
                        continue;
                    }

                    $distance = $imageHash->distance($hash, $compare);
                    $this->assertGreaterThan($this->precision, $distance, "[" . get_class($hasher) . "] $image ($hash) ^ $target ($compare)");
                    $score += $distance;

                    echo "[" . get_class($hasher) . "] $image ^ $target = $distance \n";
                }
            }

            echo  "[" . get_class($hasher) . "] Total score: $score \n";
        }
    }
}
