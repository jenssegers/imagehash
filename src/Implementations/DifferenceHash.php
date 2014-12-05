<?php namespace Jenssegers\ImageHash\Implementations;

use Jenssegers\ImageHash\Implementation;

class DifferenceHash implements Implementation {

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

		// Each bit is simply set based on whether the left pixel is brighter than the right pixel.
		// http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html
		$hash = 0; $one = 1;
		foreach ($pixels as $i => $pixel)
		{
			$i = isset($pixels[$i + 1]) ? $i : -1;

			if ($pixel > $pixels[$i + 1])
			{
				$hash |= $one;
			}

			$one = $one << 1;
		}

		return $hash;
	}

}
