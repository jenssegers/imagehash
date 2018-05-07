<?php namespace Jenssegers\ImageHash\Implementations;

use Intervention\Image\Image;
use Jenssegers\ImageHash\Implementation;

class PerceptualHash implements Implementation
{
    /**
     * Downscaled image size.
     */
    const SIZE = 32;

    /**
     * Use luma values.
     */
    const LUMA = 'luma';

    /**
     * Use greyscale value.
     */
    const GREYSCALE = 'greyscale';

    /**
     * @var string
     */
    const AVERAGE = 'average';

    /**
     * @var string
     */
    const MEDIAN = 'median';

    /**
     * @var string
     */
    protected $reductionMethod;

    /**
     * @var string
     */
    protected $comparisonMethod;

    /**
     * @param string $reductionMethod
     * @param string $comparisonMethod
     */
    public function __construct($reductionMethod = self::LUMA, $comparisonMethod = self::AVERAGE)
    {
        $this->reductionMethod = $reductionMethod;
        $this->comparisonMethod = $comparisonMethod;
    }

    /**
     * @inheritdoc
     */
    public function hash(Image $image)
    {
        // Resize the image.
        $resized = $image->resize(static::SIZE, static::SIZE);

        $matrix = [];
        $row = [];
        $rows = [];
        $col = [];

        for ($y = 0; $y < static::SIZE; $y++) {
            for ($x = 0; $x < static::SIZE; $x++) {
                $rgb = $resized->pickColor($x, $y);

                // Get the luma or greyscale value from the pixel.
                if ($this->reductionMethod === self::GREYSCALE) {
                    $row[$x] = (int) floor(($rgb[0] + $rgb[1] + $rgb[2]) / 3);
                } else {
                    $row[$x] = (int) floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));
                }
            }
            $rows[$y] = $this->calculateDCT($row);
        }

        for ($x = 0; $x < static::SIZE; $x++) {
            for ($y = 0; $y < static::SIZE; $y++) {
                $col[$y] = $rows[$y][$x];
            }
            $matrix[$x] = $this->calculateDCT($col);
        }

        // Extract the top 8x8 pixels.
        $pixels = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $pixels[] = $matrix[$y][$x];
            }
        }

        if ($this->comparisonMethod === self::MEDIAN) {
            $compare = $this->median($pixels);
        } else {
            $compare = $this->average($pixels);
        }

        // Calculate hash.
        $hash = 0;
        $one = 1;
        foreach ($pixels as $pixel) {
            if ($pixel > $compare) {
                $hash |= $one;
            }
            $one = $one << 1;
        }

        return $hash;
    }

    /**
     * Perform a 1 dimension Discrete Cosine Transformation.
     *
     * @param array $matrix
     * @return array
     */
    protected function calculateDCT(array $matrix)
    {
        $transformed = [];
        $size = count($matrix);

        for ($i = 0; $i < $size; $i++) {
            $sum = 0;
            for ($j = 0; $j < $size; $j++) {
                $sum += $matrix[$j] * cos($i * pi() * ($j + 0.5) / $size);
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
     * @param array $pixels
     * @return float
     */
    protected function median(array $pixels)
    {
        sort($pixels, SORT_NUMERIC);

        if (count($pixels) % 2 === 0) {
            return $pixels[count($pixels) / 2 - 1] + $pixels[count($pixels) / 2] / 2;
        }

        return $pixels[(int) floor(count($pixels) / 2)];
    }

    /**
     * @param array $pixels
     * @return float
     */
    protected function average(array $pixels)
    {
        // Calculate the average value from top 8x8 pixels, except for the first one.
        $n = count($pixels) - 1;

        return array_sum(array_slice($pixels, 1, $n)) / $n;
    }
}
