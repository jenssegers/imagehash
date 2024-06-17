<?php namespace Jenssegers\ImageHash;

use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use RuntimeException;

class ImageHash
{
    protected Implementation $implementation;

    private ImageManager $driver;

    public function __construct(
        Implementation $implementation = null,
        ImageManager $driver = null
    ) {
        $this->implementation = $implementation ?: $this->defaultImplementation();
        $this->driver = $driver ?: $this->defaultDriver();
    }

    /**
     * Calculate a perceptual hash of an image.
     * @param mixed $image
     * @return Hash
     */
    public function hash(mixed $image): Hash
    {
        $image = $this->driver->read($image);

        return $this->implementation->hash($image);
    }

    /**
     * Compare 2 images and get the hamming distance.
     * @param mixed $resource1
     * @param mixed $resource2
     * @return int
     */
    public function compare(mixed $resource1, mixed $resource2): int
    {
        $hash1 = $this->hash($resource1);
        $hash2 = $this->hash($resource2);

        return $this->distance($hash1, $hash2);
    }

    public function distance(Hash $hash1, Hash $hash2): int
    {
        return $hash1->distance($hash2);
    }

    protected function createResource(string $data): Image
    {
        return $this->driver->read($data);
    }

    protected function defaultImplementation(): Implementation
    {
        return new DifferenceHash();
    }

    protected function defaultDriver(): ImageManager
    {
        if (extension_loaded('imagick')) {
            return new ImageManager(new ImagickDriver());
        }

        if (extension_loaded('gd')) {
            return new ImageManager(new GdDriver());
        }

        throw new RuntimeException('Please install GD or ImageMagick');
    }
}
