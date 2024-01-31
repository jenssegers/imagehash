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

    protected int $size;

    protected string $comparisonMethod;

    public function __construct(int $size = 32, string $comparisonMethod = self::AVERAGE)
    {
        if (!in_array($comparisonMethod, [self::AVERAGE, self::MEDIAN])) {
            throw new InvalidArgumentException('Unknown comparison mode ' . $comparisonMethod);
        }

        $this->size = $size;
        $this->comparisonMethod = $comparisonMethod;
    }

    public function hash(Image $image): Hash
    {
        // Resize the image.
        $resized = $image->resize($this->size, $this->size);

        $matrix = [];
        $row = [];
        $rows = [];
        $col = [];

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $rgb = $resized->pickColor($x, $y)->toArray();
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
     */
    protected function calculateDCT(array $matrix): array
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
     */
    protected function median(array $pixels): float
    {
        sort($pixels, SORT_NUMERIC);

        if (count($pixels) % 2 === 0) {
            return ($pixels[count($pixels) / 2 - 1] + $pixels[count($pixels) / 2]) / 2;
        }

        return $pixels[(int) floor(count($pixels) / 2)];
    }

    /**
     * Get the average of the pixel values.
     */
    protected function average(array $pixels): float
    {
        // Calculate the average value from top 8x8 pixels, except for the first one.
        $n = count($pixels) - 1;

        return array_sum(array_slice($pixels, 1, $n)) / $n;
    }
}
