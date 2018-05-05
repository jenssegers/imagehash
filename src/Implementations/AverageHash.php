<?php namespace Jenssegers\ImageHash\Implementations;

use Intervention\Image\Image;
use Jenssegers\ImageHash\Implementation;

class AverageHash implements Implementation
{
    const SIZE = 8;

    /**
     * @inheritdoc
     */
    public function hash(Image $image)
    {
        // Resize the image.
        $resized = $image->resize(static::SIZE, static::SIZE);

        // Create an array of greyscale pixel values.
        $pixels = [];
        for ($y = 0; $y < static::SIZE; $y++) {
            for ($x = 0; $x < static::SIZE; $x++) {
                $rgb = $resized->pickColor($x, $y);
                $pixels[] = floor(($rgb[0] + $rgb[1] + $rgb[2]) / 3);
            }
        }

        // Get the average pixel value.
        $average = floor(array_sum($pixels) / count($pixels));

        // Each hash bit is set based on whether the current pixels value is above or below the average.
        $hash = 0;
        $one = 1;
        foreach ($pixels as $pixel) {
            if ($pixel > $average) {
                $hash |= $one;
            }
            $one = $one << 1;
        }

        return $hash;
    }
}
