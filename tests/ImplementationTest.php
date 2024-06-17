<?php

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementation;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\BlockHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ImplementationTest extends TestCase
{
    private int $threshold = 10;

    private bool $debug = true;

    public static function provideImplementations(): array
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

    protected function getSimilarImages(): array
	{
        return glob(__DIR__ . '/images/forest/*');
    }

    protected function getDifferentImages(): array
	{
        return glob(__DIR__ . '/images/office/*');
    }

	#[DataProvider('provideImplementations')]
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

	#[DataProvider('provideImplementations')]
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

    protected function debug(string $message): void {
        if ($this->debug) {
            echo PHP_EOL . $message;
        }
    }
}
