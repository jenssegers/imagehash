<?php namespace Jenssegers\ImageHash\Implementations;

use Intervention\Image\Image;
use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\Implementation;

class AverageHash implements Implementation
{
    /**
     * @var int
     */
    protected $size;

    /**
     * @param int $size
     */
    public function __construct($size = 8)
    {
        $this->size = $size;
    }

    /**
     * @inheritdoc
     */
    public function hash(Image $image)
    {
        // Resize the image.
        $resized = $image->resize($this->size, $this->size);

        // Create an array of greyscale pixel values.
        $pixels = [];
        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $rgb = $resized->pickColor($x, $y);
                $pixels[] = (int) floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));
            }
        }

        // Get the average pixel value.
        $average = floor(array_sum($pixels) / count($pixels));

        // Each hash bit is set based on whether the current pixels value is above or below the average.
        $bits = array_map(function ($pixel) use ($average) {
            return (int) ($pixel > $average);
        }, $pixels);

        return Hash::fromBits($bits);
    }
}
