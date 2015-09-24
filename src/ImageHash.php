<?php namespace Jenssegers\ImageHash;

use Exception;
use UnexpectedValueException;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class ImageHash {

    /**
     * The hashing implementation.
     *
     * @var Implementation
     */
    protected $implementation;

    /**
     * Constructor.
     *
     * @param Implementation $implementation
     */
    public function __construct(Implementation $implementation = null)
    {
        $this->implementation = $implementation ?: new DifferenceHash;
    }

    /**
     * Calculate a perceptual hash of an image.
     *
     * @param  mixed   $resource
     * @param  integer $size
     * @return integer
     */
    public function hash($resource)
    {
        $destroy = false;

        if ( ! is_resource($resource))
        {
            $resource = $this->loadImageResource($resource);
            $destroy = true;
        }

        $hash = $this->implementation->hash($resource);

        if ($destroy)
        {
            imagedestroy($resource);
        }

        return is_int($hash) ? dechex($hash) : $hash;
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
     */
    public function distance($hash1, $hash2)
    {
        if (extension_loaded('gmp'))
        {
            $dh = gmp_hamdist("0x$hash1", "0x$hash2");
        }
        else
        {
            $hash1 = hexdec($hash1);
            $hash2 = hexdec($hash2);

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
     * Get a file resource.
     *
     * @param  string   $file
     * @return resource
     */
    protected function loadImageResource($file)
    {
        if (is_resource($file))
        {
            return $file;
        }

        try
        {
            return imagecreatefromstring(file_get_contents($file));
        }
        catch (Exception $e)
        {
            throw new Exception("Unable to load file: $file");
        }
    }

}
