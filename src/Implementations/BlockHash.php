<?php

namespace Jenssegers\ImageHash\Implementations;

use Intervention\Image\Image;
use InvalidArgumentException;
use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\Implementation;

class BlockHash implements Implementation
{
    /**
     * @var string
     */
    const PRECISE = 'precise';

    /**
     * @var string
     */
    const QUICK = 'quick';

    protected string $mode;

    protected int $size;

    public function __construct(int $size = 16, $mode = self::PRECISE)
    {
        if ($size % 4 !== 0) {
            throw new InvalidArgumentException('Amount of bits needs to be dividable by 4');
        }

        if (!in_array($mode, [self::QUICK, self::PRECISE])) {
            throw new InvalidArgumentException('Unknown mode ' . $mode);
        }

        $this->size = $size;
        $this->mode = $mode;
    }

    public function hash(Image $image): Hash
    {
        if ($this->mode === self::QUICK) {
            return $this->even($image);
        }

        return $this->uneven($image);
    }

    private function even(Image $image): Hash
    {
        $width = $image->width();
        $height = $image->height();
        $blocksizeX = (int) floor($width / $this->size);
        $blocksizeY = (int) floor($height / $this->size);

        $result = [];

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $value = 0;

                for ($iy = 0; $iy < $blocksizeY; $iy++) {
                    for ($ix = 0; $ix < $blocksizeX; $ix++) {
                        $cx = $x * $blocksizeX + $ix;
                        $cy = $y * $blocksizeY + $iy;
                        $rgb = $image->pickColor($cx, $cy)->toArray();
                        $value += $rgb[0] + $rgb[1] + $rgb[2];
                    }
                }

                $result[] = $value;
            }
        }

        return $this->blocksToBits($result, $blocksizeX * $blocksizeY);
    }

    private function uneven(Image $image): Hash
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        $evenX = $imageWidth % $this->size === 0;
        $evenY = $imageHeight % $this->size === 0;
        $blockWidth = $imageWidth / $this->size;
        $blockHeight = $imageHeight / $this->size;

        // Initialize empty blocks.
        $blocks = [];
        for ($i = 0; $i < $this->size; $i++) {
            $blocks[$i] = array_fill(0, $this->size, 0);
        }

        for ($y = 0; $y < $imageHeight; $y++) {
            if ($evenY) {
                // Don't bother dividing y, if the size evenly divides by bits.
                $blockTop = $blockBottom = (int) floor($y / $blockHeight);
                $weightTop = 1;
                $weightBottom = 0;
            } else {
                $yMod = fmod($y + 1, $blockHeight);
                $yFrac = $yMod - (int) floor($yMod);
                $yInt = $yMod - $yFrac;

                $weightTop = 1 - $yFrac;
                $weightBottom = $yFrac;

                // yInt will be 0 on bottom/right borders and on block boundaries.
                if ($yInt > 0 || ($y + 1) === $imageHeight) {
                    $blockTop = $blockBottom = (int) floor($y / $blockHeight);
                } else {
                    $blockTop = (int) floor($y / $blockHeight);
                    $blockBottom = (int) ceil($y / $blockHeight);
                }
            }

            for ($x = 0; $x < $imageWidth; $x++) {
                $rgb = $image->pickColor($x, $y)->toArray();
                $value = $rgb[0] + $rgb[1] + $rgb[2];

                if ($evenX) {
                    $blockLeft = $blockRight = (int) floor($x / $blockWidth);
                    $weightLeft = 1;
                    $weightRight = 0;
                } else {
                    $xMod = fmod($x + 1, $blockWidth);
                    $xFrac = $xMod - (int) floor($xMod);
                    $xInt = $xMod - $xFrac;

                    $weightLeft = (1 - $xFrac);
                    $weightRight = $xFrac;

                    // xInt will be 0 on bottom/right borders and on block boundaries.
                    if ($xInt > 0 || ($x + 1) === $imageWidth) {
                        $blockLeft = $blockRight = (int) floor($x / $blockWidth);
                    } else {
                        $blockLeft = (int) floor($x / $blockWidth);
                        $blockRight = (int) ceil($x / $blockWidth);
                    }
                }

                // Add weighted pixel value to relevant blocks.
                if (!isset($blocks[$blockTop][$blockLeft])) {
                    $blocks[$blockTop][$blockLeft] = 0;
                }
                if (!isset($blocks[$blockTop][$blockRight])) {
                    $blocks[$blockTop][$blockRight] = 0;
                }
                if (!isset($blocks[$blockBottom][$blockLeft])) {
                    $blocks[$blockBottom][$blockLeft] = 0;
                }
                if (!isset($blocks[$blockBottom][$blockRight])) {
                    $blocks[$blockBottom][$blockRight] = 0;
                }
                $blocks[$blockTop][$blockLeft] += $value * $weightTop * $weightLeft;
                $blocks[$blockTop][$blockRight] += $value * $weightTop * $weightRight;
                $blocks[$blockBottom][$blockLeft] += $value * $weightBottom * $weightLeft;
                $blocks[$blockBottom][$blockRight] += $value * $weightBottom * $weightRight;
            }
        }

        $result = [];
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                $result[] = $blocks[$i][$j];
            }
        }

        return $this->blocksToBits($result, $blockWidth * $blockHeight);
    }

    protected function blocksToBits(array $blocks, float $pixelsPerBlock): Hash
    {
        $halfBlockValue = $pixelsPerBlock * 256 * 3 / 2;

        // Compare medians across four horizontal bands.
        $bandsize = (int) floor(count($blocks) / 4);

        $bits = [];

        for ($i = 0; $i < 4; $i++) {
            $median = $this->median(array_slice($blocks, $i * $bandsize, $bandsize));

            for ($j = $i * $bandsize; $j < ($i + 1) * $bandsize; $j++) {
                $value = $blocks[$j];

                // Output a 1 if the block is brighter than the median.
                // With images dominated by black or white, the median may
                // end up being 0 or the max value, and thus having a lot
                // of blocks of value equal to the median. To avoid
                // generating hashes of all zeros or ones, in that case output
                // 0 if the median is in the lower value space, 1 otherwise.
                $bits[$j] = (int) ($value > $median || (abs($value - $median) < 1 && $median > $halfBlockValue));
            }
        }

        return Hash::fromBits($bits);
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
}
