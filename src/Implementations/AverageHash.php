<?php namespace Jenssegers\ImageHash\Implementations;

use Jenssegers\ImageHash\Implementation;

class AverageHash implements Implementation {

	const SIZE = 8;

	/**
	 * {@inheritDoc}
	 */
	public function hash($resource)
	{
		// Resize the image.
		$resized = imagecreatetruecolor(static::SIZE, static::SIZE);
		imagecopyresampled($resized, $resource, 0, 0, 0, 0, static::SIZE, static::SIZE, imagesx($resource), imagesy($resource));
		imagecopymergegray($resized, $resource, 0, 0, 0, 0, static::SIZE, static::SIZE, 50);

		// Get luma value (YCbCr) from RGB colors.
		$pixels = []; $index = 0;
		for ($y = 0; $y < static::SIZE; $y++)
		{
			for ($x = 0; $x < static::SIZE; $x++)
			{
				$rgb = imagecolorsforindex($resized, imagecolorat($resized, $x, $y));
				$pixel = (($rgb['red'] * 0.299) + ($rgb['green'] * 0.587) + ($rgb['blue'] * 0.114));
				$pixels[] = floor($pixel);
			}
		}

		// Free up memory.
		imagedestroy($resized);

		// Get the average pixel values.
		$average = floor(array_sum($pixels) / count($pixels));

		// Calculate hash.
		$hash = 0; $one = 1;
		foreach ($pixels as $pixel)
		{
			if ($pixel > $average)
			{
				$hash |= $one;
			}

			$one = $one << 1;
		}

		return $hash;
	}

}
