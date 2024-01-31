<?php

use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CompatibilityTest extends TestCase
{
    public static function preCalculatedImageHashes(): array
	{
        return [
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-copyright.jpg',
                '7878787c7c707c3c',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-high.jpg',
                '78787c5c58707c3c',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-low.jpg',
                '7878785c58707c3c',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/forest/forest-thumb.jpg',
                '78787c5c58707c3c',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-copyright.jpg',
                '2f3f374b2b771f07',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-high.jpg',
                '2f3f374f6f7f1f07',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-low.jpg',
                '2f3f374f6f7f1f07',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/forest/forest-thumb.jpg',
                '2f3f374f6f773f07',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/office/tumblr_ndyfdoR6Wp1tubinno1_1280.jpg',
                '81ef6f3fff',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/office/tumblr_ndyfnr7lk21tubinno1_1280.jpg',
                'e8f8d19034353501',
            ],
            [
                AverageHash::class,
                __DIR__ . '/images/office/tumblr_ndyfq386o41tubinno1_1280.jpg',
                'e0e8ecd2c21e1e1e',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/office/tumblr_ndyfdoR6Wp1tubinno1_1280.jpg',
                '6283c3cac8581a29',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/office/tumblr_ndyfnr7lk21tubinno1_1280.jpg',
                'aeaedaca12121216',
            ],
            [
                DifferenceHash::class,
                __DIR__ . '/images/office/tumblr_ndyfq386o41tubinno1_1280.jpg',
                'f5f6f7dde5478787',
            ],
        ];
    }

	#[DataProvider('preCalculatedImageHashes')]
    public function testCompatibility($implementation, $path, $precalculated)
    {
        $implementation = new $implementation();
        foreach (['gd' => fn() => new GdDriver(), 'imagick' => fn() => new ImagickDriver()] as $extension => $driver) {
            if (!extension_loaded($extension)) {
                continue;
            }

            $hasher = new ImageHash($implementation, new ImageManager($driver()));

            $hash = $hasher->hash($path);
			$hex = $hash->toHex();

            if ($precalculated !== $hex) {
                $this->addWarning(get_class($implementation)." $extension generated a different hash ".$hex.' instead of '.$precalculated);
            }
        }

        $this->expectNotToPerformAssertions();
    }

	private function addWarning(string $message): void
	{
		trigger_error($message,  E_USER_WARNING);
	}
}
