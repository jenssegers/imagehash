<?php namespace Jenssegers\ImageHash;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use RuntimeException;

class ImageHash
{
    /**
     * Return hashes as hexadecimals.
     */
    const HEXADECIMAL = 'hex';

    /**
     * Return hashes as decimals.
     */
    const DECIMAL = 'dec';

    /**
     * The hashing implementation.
     *
     * @var Implementation
     */
    protected $implementation;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var Image
     */
    private $driver;

    /**
     * Constructor.
     *
     * @param Implementation $implementation
     * @param string $mode
     * @param ImageManager $driver
     */
    public function __construct(
        Implementation $implementation = null,
        $mode = self::HEXADECIMAL,
        ImageManager $driver = null
    ) {
        $this->implementation = $implementation ?: new DifferenceHash;
        $this->mode = $mode;
        $this->driver = $driver ?: $this->defaultDriver();
    }

    /**
     * Calculate a perceptual hash of an image.
     *
     * @param mixed $image
     * @return int
     */
    public function hash($image)
    {
        $image = $this->driver->make($image);

        $hash = $this->implementation->hash($image);

        return $this->formatHash($hash);
    }

    /**
     * Calculate a perceptual hash of an image string.
     *
     * @deprecated
     * @param  mixed $data Image data
     * @return string
     */
    public function hashFromString($data)
    {
        return $this->hash($data);
    }

    /**
     * Compare 2 images and get the hamming distance.
     *
     * @param  mixed $resource1
     * @param  mixed $resource2
     * @return int
     */
    public function compare($resource1, $resource2)
    {
        $hash1 = $this->hash($resource1);
        $hash2 = $this->hash($resource2);

        return $this->distance($hash1, $hash2);
    }

    /**
     * Calculate the Hamming Distance.
     *
     * @param int $hash1
     * @param int $hash2
     * @return int
     */
    public function distance($hash1, $hash2)
    {
        if (extension_loaded('gmp')) {
            if ($this->mode === self::HEXADECIMAL) {
                $dh = gmp_hamdist('0x' . $hash1, '0x' . $hash2);
            } else {
                $dh = gmp_hamdist($hash1, $hash2);
            }
        } else {
            if ($this->mode === self::HEXADECIMAL) {
                $hash1 = $this->hexdec($hash1);
                $hash2 = $this->hexdec($hash2);
            }

            $dh = 0;
            for ($i = 0; $i < 64; $i++) {
                $k = (1 << $i);
                if (($hash1 & $k) !== ($hash2 & $k)) {
                    $dh++;
                }
            }
        }

        return $dh;
    }

    /**
     * Convert hexadecimal to signed decimal.
     *
     * @param string $hex
     * @return int
     */
    public function hexdec($hex)
    {
        if (strlen($hex) === 16 && hexdec($hex[0]) > 8) {
            list($higher, $lower) = array_values(unpack('N2', hex2bin($hex)));
            return $higher << 32 | $lower;
        }

        return hexdec($hex);
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
     * Format hash in hex.
     *
     * @param int $hash
     * @return string|int
     */
    protected function formatHash($hash)
    {
        return $this->mode === static::HEXADECIMAL ? dechex($hash) : $hash;
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
