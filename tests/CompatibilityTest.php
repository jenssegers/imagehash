<?php

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use PHPUnit\Framework\TestCase;

class CompatibilityTest extends TestCase
{
    public function preCalculatedImageHashes()
    {
        return [
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-copyright.jpg',
                '3c3e0e3e3e1e1e1e',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-high.jpg',
                '3c3e0e1a3a1e1e1e',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-low.jpg',
                '3c3e0e1a3a1e1e1e',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-thumb.jpg',
                '3c3e0e1a3a1e1e1e',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-copyright.jpg',
                'f0fceed4d2ecfcf4',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-high.jpg',
                'f0fceef6f2ecfcf4',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-low.jpg',
                'f0fceef6f2ecfcf4',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-thumb.jpg',
                'f0fceef6f2ecfcf4',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/office/tumblr_ndyfdoR6Wp1tubinno1_1280.jpg',
                'fffcf6f781000000',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/office/tumblr_ndyfnr7lk21tubinno1_1280.jpg',
                '80acac2c098f1f17',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/office/tumblr_ndyfq386o41tubinno1_1280.jpg',
                '787878434b371707',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/office/tumblr_ndyfdoR6Wp1tubinno1_1280.jpg',
                '9458101353c3c146',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/office/tumblr_ndyfnr7lk21tubinno1_1280.jpg',
                '69684858535b7575',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/office/tumblr_ndyfq386o41tubinno1_1280.jpg',
                'e1e1e2a7bbaf6faf',
            ],
        ];
    }

    /**
     * @dataProvider preCalculatedImageHashes
     */
    public function testCompatibility($implementation, $path, $precalculated)
    {
        $implementation = new $implementation();
        $hasher = new ImageHash($implementation);

        $hash = $hasher->hash($path);
        $this->assertEquals($precalculated, $hash);
    }
}
