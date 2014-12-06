<?php namespace Jenssegers\ImageHash\Implementations;

use Jenssegers\ImageHash\Implementation;

class PerceptualHash implements Implementation {

	const SIZE = 32;

	/**
	 * {@inheritDoc}
	 */
	public function hash($resource)
	{
		// Resize the image.
		$resized = imagecreatetruecolor(static::SIZE, static::SIZE);
		imagecopyresampled($resized, $resource, 0, 0, 0, 0, static::SIZE, static::SIZE, imagesx($resource), imagesy($resource));

		// Get luma value (YCbCr) from RGB colors and calculate the DCT for each row.
		$matrix = []; $row = []; $rows = []; $col = []; $cols = [];
		for ($y = 0; $y < static::SIZE; $y++)
		{
			for ($x = 0; $x < static::SIZE; $x++)
			{
				$rgb = imagecolorsforindex($resized, imagecolorat($resized, $x, $y));
				$row[$x] = floor(($rgb['red'] * 0.299) + ($rgb['green'] * 0.587) + ($rgb['blue'] * 0.114));
			}
			$rows[$y] = $this->DCT1D($row);
		}

		// Free up memory.
		imagedestroy($resized);

		// Calculate the DCT for each column.
		for ($x = 0; $x < static::SIZE; $x++)
		{
			for ($y = 0; $y < static::SIZE; $y++)
			{
				$col[$y] = $rows[$y][$x];
			}
			$matrix[$x] = $this->DCT1D($col);
		}

		// Extract the top 8x8 pixels.
		$pixels = [];
		for ($x = 0; $x < 8; $x++)
		{
			for ($y = 0; $y < 8; $y++)
			{
				$pixels[] = $matrix[$y][$x];
			}
		}

		// Get the average pixel values.
		$average = floor(array_sum($pixels) / count($pixels));

		// Calculate hash.
		$hash = 0; $one = 1;
		foreach ($pixels as $pixel)
		{
			if ($pixel > $average) $hash |= $one;
			$one = $one << 1;
		}

		return $hash;
	}

	/**
	 * Perform 1 dimension Discrete Cosine Transformation.
	 *
	 * @param  array $pixels
	 */
	protected function DCT1D(array $pixels)
	{
		$transformed = [];
		$size = count($pixels);

		for ($i = 0; $i < $size; $i++)
		{
			$sum = 0;
			for ($j = 0; $j < $size; $j++)
			{
				$sum += $pixels[$j] * cos($i * pi() * ($j + 0.5) / ($size));
			}

			$sum *= sqrt(2 / $size);

			if ($i == 0)
			{
				$sum *= 1 / sqrt(2);
			}

			$transformed[$i] = $sum;
		}

		return $transformed;
	}

}
