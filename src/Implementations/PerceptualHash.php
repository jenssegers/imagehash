<?php namespace Jenssegers\ImageHash\Implementations;

use Intervention\Image\Image;
use Jenssegers\ImageHash\Implementation;

class PerceptualHash implements Implementation
{
    const SIZE = 64;

    /**
     * @inheritdoc
     */
    public function hash(Image $image)
    {
        // Resize the image.
        $resized = $image->resize(static::SIZE, static::SIZE);

        // Get luma value (YCbCr) from RGB colors and calculate the DCT for each row.
        $matrix = [];
        $row = [];
        $rows = [];
        $col = [];
        for ($y = 0; $y < static::SIZE; $y++) {
            for ($x = 0; $x < static::SIZE; $x++) {
                $rgb = $resized->pickColor($x, $y);
                $row[$x] = floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));
            }
            $rows[$y] = $this->DCT1D($row);
        }

        // Calculate the DCT for each column.
        for ($x = 0; $x < static::SIZE; $x++) {
            for ($y = 0; $y < static::SIZE; $y++) {
                $col[$y] = $rows[$y][$x];
            }
            $matrix[$x] = $this->DCT1D($col);
        }

        // Extract the top 8x8 pixels.
        $pixels = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $pixels[] = $matrix[$y][$x];
            }
        }

        // Calculate the median.
        $median = $this->median($pixels);

        // Calculate hash.
        $hash = 0;
        $one = 1;
        foreach ($pixels as $pixel) {
            if ($pixel > $median) {
                $hash |= $one;
            }
            $one = $one << 1;
        }

        return $hash;
    }

    /**
     * Perform a 1 dimension Discrete Cosine Transformation.
     *
     * @param array $pixels
     * @return array
     */
    protected function DCT1D(array $pixels)
    {
        $transformed = [];
        $size = count($pixels);

        for ($i = 0; $i < $size; $i++) {
            $sum = 0;
            for ($j = 0; $j < $size; $j++) {
                $sum += $pixels[$j] * cos($i * pi() * ($j + 0.5) / $size);
            }

            $sum *= sqrt(2 / $size);

            if ($i === 0) {
                $sum *= 1 / sqrt(2);
            }

            $transformed[$i] = $sum;
        }

        return $transformed;
    }

    /**
     * Get the median of the pixel values.
     *
     * @param  array $pixels
     * @return float
     */
    protected function median(array $pixels)
    {
        sort($pixels, SORT_NUMERIC);
        $middle = (int) floor(count($pixels) / 2);

        if (count($pixels) % 2) {
            $median = $pixels[$middle];
        } else {
            $low = $pixels[$middle];
            $high = $pixels[$middle + 1];
            $median = ($low + $high) / 2;
        }

        return $median;
    }
}
