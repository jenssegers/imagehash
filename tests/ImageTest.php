<?php

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;

class ImageTest extends PHPUnit_Framework_TestCase
{
    protected $precision = 10;

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
            $images = glob('tests/images/forest/*');

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
            $images = glob('tests/images/office/*');

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

    public function testCompareEqual()
    {
        foreach ($this->hashers as $hasher) {
            $imageHash = new ImageHash($hasher);
            $images = glob('tests/images/forest/*');

            foreach ($images as $image) {
                foreach ($images as $target) {
                    if ($target == $image) {
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
            $images = glob('tests/images/office/*');

            foreach ($images as $image) {
                foreach ($images as $target) {
                    if ($target == $image) {
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
        $images = glob('tests/images/office/*');

        $hash = $imageHash->hash($images[0]);
        $this->assertTrue(ctype_xdigit($hash), $hash);
        $this->assertEquals(0, $imageHash->distance($hash, $hash));
    }

    public function testDecimalMode()
    {
        $imageHash = new ImageHash(new DifferenceHash(), ImageHash::DECIMAL);
        $images = glob('tests/images/office/*');

        $hash = $imageHash->hash($images[0]);
        $this->assertTrue(is_int($hash), $hash);
        $this->assertEquals(0, $imageHash->distance($hash, $hash));
    }

    // public function testThrowsUnexceptedValueException()
    // {
    //     $this->setExpectedException('UnexpectedValueException');

    //     $imageHash = new ImageHash;
    //     $imageHash->distance("a", "b");
    // }
}
