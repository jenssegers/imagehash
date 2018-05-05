<?php namespace Jenssegers\ImageHash\Implementations;

use Intervention\Image\Image;
use Jenssegers\ImageHash\Implementation;

class DifferenceHash implements Implementation
{
    const SIZE = 8;

    /**
     * @inheritdoc
     */
    public function hash(Image $image)
    {
        // For this implementation we create a 8x9 image.
        $width = static::SIZE + 1;
        $height = static::SIZE;

        // Resize the image.
        $resized = $image->resize($width, $height);

        $hash = 0;
        $one = 1;
        for ($y = 0; $y < $height; $y++) {
            // Get the pixel value for the leftmost pixel.
            $rgb = $resized->pickColor(0, $y);
            $left = floor(($rgb[0] + $rgb[1] + $rgb[2]) / 3);

            for ($x = 1; $x < $width; $x++) {
                // Get the pixel value for each pixel starting from position 1.
                $rgb = $resized->pickColor($x, $y);
                $right = floor(($rgb[0] + $rgb[1] + $rgb[2]) / 3);

                // Each hash bit is set based on whether the left pixel is brighter than the right pixel.
                // http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html
                if ($left > $right) {
                    $hash |= $one;
                }

                // Prepare the next loop.
                $left = $right;
                $one = $one << 1;
            }
        }

        return $hash;
    }
}
