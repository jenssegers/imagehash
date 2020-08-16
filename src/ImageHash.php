<?php namespace Jenssegers\ImageHash;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use RuntimeException;

class ImageHash
{
    /**
     * @var Implementation
     */
    protected $implementation;

    /**
     * @var Image
     */
    private $driver;

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
    public function hash($image): Hash
    {
        $image = $this->driver->make($image);

        return $this->implementation->hash($image);
    }

    /**
     * Compare 2 images and get the hamming distance.
     * @param mixed $resource1
     * @param mixed $resource2
     * @return int
     */
    public function compare($resource1, $resource2): int
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
        return $this->driver->make($data);
    }

    protected function defaultImplementation(): Implementation
    {
        return new DifferenceHash();
    }

    protected function defaultDriver(): ImageManager
    {
        if (extension_loaded('imagick')) {
            return new ImageManager(['driver' => 'imagick']);
        }
        
        if (extension_loaded('gd')) {
            return new ImageManager(['driver' => 'gd']);
        }

        throw new RuntimeException('Please install GD or ImageMagick');
    }
}
