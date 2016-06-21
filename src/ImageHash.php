<?php namespace Jenssegers\ImageHash;

use Exception;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class ImageHash
{
    /**
     * Return hashes as hexacedimals.
     */
    const HEXADECIMAL = 'hex';

    /**
     * Return hashes as decimals.
     */
    const DECIMAL = 'dec';

    const SIZE = 64;

    /**
     * The hashing implementation.
     *
     * @var Implementation
     */
    protected $implementation;

    /**
     * Should we auto rotate the images before hashing them? This ensures (or
     * tries to) that images that have been rotated 90, 180, or 270 degrees will
     * return the same (or very similar) hashes. eg: It's very common for a
     * camera to automatically rotate JPEG images, but not rotate the RAW
     * versions, and without auto rotation they'll return two completely
     * different hashes.
     */
    public $autorotate = false;

    /**
     * Should we auto flip the images before hashing them? This ensures (or
     * tries to) that images that have been flipped will
     * return the same (or very similar) hashes. eg: It's very common for a
     * camera to automatically rotate JPEG images, but not rotate the RAW
     * versions, and without auto rotation they'll return two completely
     * different hashes.
     */
    public $autoflip = false;

    /**
     * Constructor.
     *
     * @param Implementation $implementation
     * @param string $mode
     */
    public function __construct(Implementation $implementation = null, $mode = self::HEXADECIMAL)
    {
        $this->implementation = $implementation ?: new DifferenceHash;

        $this->mode = $mode;
    }

    /**
     * Calculate a perceptual hash of an image.
     *
     * @param  mixed   $resource
     * @return int
     */
    public function hash($resource)
    {
        $destroy = false;

        if (! is_resource($resource)) {
            $resource = $this->loadImageResource($resource);
            $destroy = true;
        }

        if ($this->autorotate || $this->autoflip) {

            // Resize the image to 64x64 (the current max needed for any
            // implementation) so we dramatically cut down the number of pixels
            // we need to examine in PHP.
            $resized = imagecreatetruecolor(static::SIZE, static::SIZE);
            imagecopyresampled($resized, $resource, 0, 0, 0, 0, static::SIZE, static::SIZE, imagesx($resource), imagesy($resource));

            // Ensure that images that have been rotated in 90 degree increments
            // return the same hash. To use this, set AverageHash->autorotate = true.
            // We auto rotate after resizing so that we only have to analyze a few
            // pixels, instead of (potentially) many millions.
            if ($this->autorotate) {
                $rotated = $this->autorotateImageResource($resized);
                imagedestroy($resized);
                $resized = $rotated;
                unset($rotated);
            }

            // Ensure that images that have been flipped return the same hash. To
            // use this, set AverageHash->autoflip = true. Highly recommended that
            // you enable autorotate if you're using this.
            if ($this->autoflip) {
                $this->autoflipImageResource($resized);
            }

            if ($destroy) {
                imagedestroy($resource);
            }
            $resource = $resized;
            unset($resized);

            // since this new resource is ours now, we're responsible for memory
            // cleanup - destroy it when done
            $destroy = true;
        }

        $hash = $this->implementation->hash($resource);

        if ($destroy) {
            imagedestroy($resource);
        }

        if ($this->mode === self::HEXADECIMAL and is_int($hash)) {
            return dechex($hash);
        }

        return $hash;
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
        if (extension_loaded('gmp')) {
            if ($this->mode === self::HEXADECIMAL) {
                $dh = gmp_hamdist('0x' . $hash1, '0x' . $hash2);
            } else {
                $dh = gmp_hamdist($hash1, $hash2);
            }
        } else {
            if ($this->mode === self::HEXADECIMAL) {
                $hash1 = hexdec($hash1);
                $hash2 = hexdec($hash2);
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
     * Get a file resource.
     *
     * @param  string   $file
     * @return resource
     */
    protected function loadImageResource($file)
    {
        if (is_resource($file)) {
            return $file;
        }

        try {
            return imagecreatefromstring(file_get_contents($file));
        } catch (Exception $e) {
            throw new Exception("Unable to load file: $file");
        }
    }

    /**
     * Rotate the image so the brightest "side" is on top. This way images that
     * have been rotated (in 90 degree increments) end up with the same (or
     * very similar) hashes.
     *
     * @param resource $resource A resource pointing to the image to rotate.
     * @return resource A new (possibly rotated) image resource.
     */
    protected function autorotateImageResource($resource)
    {

        // Keep a running total of the brightness of each side, which ends up
        // being a running total of what the "top" of the image would be if we
        // rotated it by 0, 90, 180, or 270 degrees.
        $rotBright = array(
            0 => 0,
            90 => 0,
            180 => 0,
            270 => 0,
        );

        // Split the image into four overlapping "sides". To do this we
        // break the image into four quadrants (draw a + over your image).
        // The top "side" ends up being the upper left and upper right
        // quadrants. The right side ends up being the upper right and
        // lower right quadrants, etc. All the pixels in each quadrant /
        // side are summed up and the brightest side is rotated to be on
        // top. Because we end up processing all pixels in the quadrants,
        // it's a good idea to resize the image down to the largest size
        // the hash will need first.
        $width = imagesx($resource);
        $height = imagesy($resource);
        $halfw = $width / 2;
        $halfh = $height / 2;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorsforindex($resource, imagecolorat($resource, $x, $y));
                $brightness = floor(($rgb['red'] + $rgb['green'] + $rgb['blue']) / 3);
                if ($x >= $halfw) {
                    $rotBright[90] += $brightness;
                } else {
                    $rotBright[270] += $brightness;
                }
                if ($y >= $halfh) {
                    $rotBright[180] += $brightness;
                } else {
                    $rotBright[0] += $brightness;
                }
            }
        }

        // Sort the values so the brightest side is the first element in the
        // array.
        arsort($rotBright);

        // And that first element will have a key of either 0, 90, 180, or 270.
        $rotation = array_keys($rotBright);

        // Only rotate if rotation required is not zero.
        if ($rotation[0] > 0) {

            // Rotate the image (returns a new image resource)
            $rotated = imagerotate($resource, $rotation[0], 0);
        } else {

            // Just copy it so we don't return the same resource.
            // This is slightly wasteful, but ensures that autorotate always
            // returns a new resource, instead of sometimes returning a new one
            // and sometimes returning the existing one.
            $rotated = imagecreatetruecolor($width, $height);
            imagecopyresampled($rotated, $resource, 0, 0, 0, 0, $width, $height, $width, $height);
        }

        return $rotated;
    }

    /**
     * Flip the image so the brightest left/right "side" is on the left. This
     * way images that have been flipped end up with the same (or very similar)
     * hashes. This will only flip horizontally, as it assumes if you're trying
     * to detect flips, you're also trying to detect rotations and have run
     * autorotateImageResource first (and that the brightest side will already
     * be on top).
     *
     * @param resource $resource A resource pointing to the image to flip. This
     * resource will be directly modified (flipped) if needed.
     * @return void
     */
    protected function autoflipImageResource($resource)
    {

        // Keep a running total of the brightness of each side, which ends up
        // being a running total of "to flip or not to flip".
        $flipBright = array(
            0 => 0,
            1 => 0,
        );

        // Split the image into four overlapping "sides". To do this we
        // break the image into four quadrants (draw a + over your image).
        // The top "side" ends up being the upper left and upper right
        // quadrants. The right side ends up being the upper right and
        // lower right quadrants, etc. All the pixels in each quadrant /
        // side are summed up and the brightest side is rotated to be on
        // top. Because we end up processing all pixels in the quadrants,
        // it's a good idea to resize the image down to the largest size
        // the hash will need first.
        $width = imagesx($resource);
        $height = imagesy($resource);
        $halfw = $width / 2;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorsforindex($resource, imagecolorat($resource, $x, $y));
                $brightness = floor(($rgb['red'] + $rgb['green'] + $rgb['blue']) / 3);
                if ($x >= $halfw) {
                    $flipBright[1] += $brightness;
                } else {
                    $flipBright[0] += $brightness;
                }
            }
        }

        if ($flipBright[1] > $flipBright[0]) {
            imageflip($resource, IMG_FLIP_HORIZONTAL);
        }

        return;
    }
}
