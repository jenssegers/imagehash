<?php

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementation;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\BlockHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use PHPUnit\Framework\TestCase;

class ImplementationTest extends TestCase
{
    /**
     * @var int
     */
    private $threshold = 10;

    /**
     * @var bool
     */
    private $debug = true;

    public function provideImplementations()
    {
        return [
            [new AverageHash()],
            [new DifferenceHash()],
            [new PerceptualHash(32, PerceptualHash::AVERAGE)],
            [new PerceptualHash(32, PerceptualHash::MEDIAN)],
            [new BlockHash(8, BlockHash::QUICK)],
            [new BlockHash(8, BlockHash::PRECISE)],
        ];
    }

    /**
     * @return array
     */
    protected function getSimilarImages()
    {
        return glob(__DIR__ . '/images/forest/*');
    }

    /**
     * @return array
     */
    protected function getDifferentImages()
    {
        return glob(__DIR__ . '/images/office/*');
    }

    /**
     * @dataProvider provideImplementations
     * @param Implementation $implementation
     */
    public function testEqualHashes(Implementation $implementation)
    {
        $sum = 0;
        $imageHash = new ImageHash($implementation);

        $hashes = [];
        foreach ($this->getSimilarImages() as $image) {
            $hashes[$image] = $hash = $imageHash->hash($image);

            $this->debug("[" . get_class($implementation) . "] $image = $hash");
        }

        foreach ($hashes as $image => $hash) {
            foreach ($hashes as $target => $compare) {
                if ($target === $image) {
                    continue;
                }

                $distance = $imageHash->distance($hash, $compare);
                $this->assertLessThan($this->threshold, $distance, "[" . get_class($implementation) . "] $image ($hash) ^ $target ($compare)");
                $sum += $distance;

                $this->debug("[" . get_class($implementation) . "] $image ^ $target = $distance");
            }
        }

        $this->debug("[" . get_class($implementation) . "] Total score: $sum");
    }

    /**
     * @dataProvider provideImplementations
     * @param Implementation $implementation
     */
    public function testDifferentHashes(Implementation $implementation)
    {
        $sum = 0;
        $imageHash = new ImageHash($implementation);

        $hashes = [];
        foreach ($this->getDifferentImages() as $image) {
            $hashes[$image] = $hash = $imageHash->hash($image);

            $this->debug("[" . get_class($implementation) . "] $image = $hash");
        }

        foreach ($hashes as $image => $hash) {
            foreach ($hashes as $target => $compare) {
                if ($target === $image) {
                    continue;
                }

                $distance = $imageHash->distance($hash, $compare);
                $this->assertGreaterThan($this->threshold, $distance, "[" . get_class($implementation) . "] $image ($hash) ^ $target ($compare)");
                $sum += $distance;

                $this->debug("[" . get_class($implementation) . "] $image ^ $target = $distance");
            }
        }

        $this->debug("[" . get_class($implementation) . "] Total score: $sum");
    }

    protected function debug($message)
    {
        if ($this->debug) {
            echo PHP_EOL . $message;
        }
    }
}
