<?php

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementation;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /**
     * @var Implementation[]
     */
    private $hashers;

    private $precision = 10;

    public static function setUpBeforeClass()
    {
        echo 'INFO: GMP installed:' . (extension_loaded('gmp') ? 'yes' : 'no') . PHP_EOL;
        echo 'INFO: GD installed:' . (extension_loaded('gd') ? 'yes' : 'no') . PHP_EOL;
        echo 'INFO: ImageMagick installed:' . (extension_loaded('imagick') ? 'yes' : 'no') . PHP_EOL;
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
            $images = glob(__DIR__ . '/images/forest/*');

            $hashes = [];
            foreach ($images as $image) {
                $hashes[$image] = $hash = $imageHash->hash($image);

                echo "[" . get_class($hasher) . "] $image = $hash" . PHP_EOL;
            }

            foreach ($hashes as $image => $hash) {
                foreach ($hashes as $target => $compare) {
                    if ($target === $image) {
                        continue;
                    }

                    $distance = $imageHash->distance($hash, $compare);
                    $this->assertLessThan($this->precision, $distance, "[" . get_class($hasher) . "] $image ($hash) ^ $target ($compare)");
                    $score += $distance;

                    echo "[" . get_class($hasher) . "] $image ^ $target = $distance" . PHP_EOL;
                }
            }

            echo "[" . get_class($hasher) . "] Total score: $score" . PHP_EOL;
        }
    }

    public function testDifferentHashes()
    {
        foreach ($this->hashers as $hasher) {
            $score = 0;
            $imageHash = new ImageHash($hasher);
            $images = glob(__DIR__ . '/images/office/*');

            $hashes = [];
            foreach ($images as $image) {
                $hashes[$image] = $hash = $imageHash->hash($image);

                echo "[" . get_class($hasher) . "] $image = $hash" . PHP_EOL;
            }

            foreach ($hashes as $image => $hash) {
                foreach ($hashes as $target => $compare) {
                    if ($target === $image) {
                        continue;
                    }

                    $distance = $imageHash->distance($hash, $compare);
                    $this->assertGreaterThan($this->precision, $distance, "[" . get_class($hasher) . "] $image ($hash) ^ $target ($compare)");
                    $score += $distance;

                    echo "[" . get_class($hasher) . "] $image ^ $target = $distance" . PHP_EOL;
                }
            }

            echo "[" . get_class($hasher) . "] Total score: $score" . PHP_EOL;
        }
    }

    public function testCompareEqual()
    {
        foreach ($this->hashers as $hasher) {
            $imageHash = new ImageHash($hasher);
            $images = glob(__DIR__ . '/images/forest/*');

            foreach ($images as $image) {
                foreach ($images as $target) {
                    if ($target === $image) {
                        continue;
                    }

                    $distance = $imageHash->compare($image, $target);
                    $this->assertLessThan($this->precision, $distance, "[" . get_class($hasher) . "] $image <=> $target");
                }
            }
        }
    }

    public function testCompareDifferent()
    {
        foreach ($this->hashers as $hasher) {
            $imageHash = new ImageHash($hasher);
            $images = glob(__DIR__ . '/images/office/*');

            foreach ($images as $image) {
                foreach ($images as $target) {
                    if ($target === $image) {
                        continue;
                    }

                    $distance = $imageHash->compare($image, $target);
                    $this->assertGreaterThan($this->precision, $distance, "[" . get_class($hasher) . "] $image <=> $target");
                }
            }
        }
    }

    public function testHexadecimalMode()
    {
        $imageHash = new ImageHash(new DifferenceHash(), ImageHash::HEXADECIMAL);
        $images = glob(__DIR__ . '/images/office/*');

        $hash = $imageHash->hash($images[0]);
        $this->assertTrue(ctype_xdigit($hash), $hash);
        $this->assertEquals(0, $imageHash->distance($hash, $hash));
    }

    public function testDecimalMode()
    {
        $imageHash = new ImageHash(new DifferenceHash(), ImageHash::DECIMAL);
        $images = glob(__DIR__ . '/images/office/*');

        $hash = $imageHash->hash($images[0]);
        $this->assertInternalType('int', $hash);
        $this->assertEquals(0, $imageHash->distance($hash, $hash));
    }
}
