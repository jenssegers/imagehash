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

    /**
     * @param Implementation $implementation
     * @param ImageManager $driver
     */
    public function __construct(
        Implementation $implementation = null,
        ImageManager $driver = null
    ) {
        $this->implementation = $implementation ?: $this->defaultImplementation();
        $this->driver = $driver ?: $this->defaultDriver();
    }

    /**
     * Calculate a perceptual hash of an image.
     *
     * @param mixed $image
     * @return Hash
     */
    public function hash($image)
    {
        $image = $this->driver->make($image);

        return $this->implementation->hash($image);
    }

    /**
     * Compare 2 images and get the hamming distance.
     *
     * @param mixed $resource1
     * @param mixed $resource2
     * @return int
     */
    public function compare($resource1, $resource2)
    {
        $hash1 = $this->hash($resource1);
        $hash2 = $this->hash($resource2);

        return $this->distance($hash1, $hash2);
    }

    /**
     * Calculate the Hamming Distance between 2 hashes.
     *
     * @param Hash $hash1
     * @param Hash $hash2
     * @return int
     */
    public function distance(Hash $hash1, Hash $hash2)
    {
        return $hash1->distance($hash2);
    }

    /**
     * @param string $data
     * @return Image
     */
    protected function createResource($data)
    {
        return $this->driver->make($data);
    }

    /**
     * @return Implementation
     */
    protected function defaultImplementation()
    {
        return new DifferenceHash();
    }

    /**
     * @return ImageManager
     * @throws RuntimeException
     */
    protected function defaultDriver()
    {
        if (extension_loaded('gd')) {
            return new ImageManager(['driver' => 'gd']);
        }

        if (extension_loaded('imagick')) {
            return new ImageManager(['driver' => 'imagick']);
        }

        throw new RuntimeException('Please install GD or ImageMagick');
    }
}
