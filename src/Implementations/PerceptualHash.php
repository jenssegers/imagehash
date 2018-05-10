<?php namespace Jenssegers\ImageHash\Implementations;

use Intervention\Image\Image;
use InvalidArgumentException;
use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\Implementation;

class PerceptualHash implements Implementation
{
    /**
     * @var string
     */
    const AVERAGE = 'average';

    /**
     * @var string
     */
    const MEDIAN = 'median';

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $comparisonMethod;

    /**
     * @param int $size
     * @param string $comparisonMethod
     */
    public function __construct($size = 32, $comparisonMethod = self::AVERAGE)
    {
        if (!in_array($comparisonMethod, [self::AVERAGE, self::MEDIAN])) {
            throw new InvalidArgumentException('Unknown comparison mode ' . $comparisonMethod);
        }

        $this->size = $size;
        $this->comparisonMethod = $comparisonMethod;
    }

    /**
     * @inheritdoc
     */
    public function hash(Image $image)
    {
        // Resize the image.
        $resized = $image->resize($this->size, $this->size);

        $matrix = [];
        $row = [];
        $rows = [];
        $col = [];

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $rgb = $resized->pickColor($x, $y);
                $row[$x] = (int) floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));
            }
            $rows[$y] = $this->calculateDCT($row);
        }

        for ($x = 0; $x < $this->size; $x++) {
            for ($y = 0; $y < $this->size; $y++) {
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
        $bits = [];
        foreach ($pixels as $pixel) {
            $bits[] = (int) ($pixel > $compare);
        }

        return Hash::fromBits($bits);
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
            return ($pixels[count($pixels) / 2 - 1] + $pixels[count($pixels) / 2]) / 2;
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
