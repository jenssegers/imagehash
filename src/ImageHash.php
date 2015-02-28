<?php namespace Jenssegers\ImageHash;

use Exception, UnexpectedValueException;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;

class ImageHash {

	/**
	 * The hashing implementation.
	 *
	 * @var Implementation
	 */
	protected $implementation;

	/**
	 * Constructor.
	 *
	 * @param Implementation $implementation
	 */
	public function __construct(Implementation $implementation = null)
	{
		$this->implementation = $implementation ?: new AverageHash;
	}

	/**
	 * Calculate a perceptual hash of an image.
	 *
	 * @param  mixed   $resource
	 * @param  integer $size
	 * @return integer
	 */
	public function hash($resource)
	{
		$destroy = false;

		if ( ! is_resource($resource))
		{
			$resource = $this->loadImageResource($resource);
			$destroy = true;
		}

		$hash = $this->implementation->hash($resource);

		if ($destroy)
		{
			imagedestroy($resource);
		}

		return $hash;
	}

	/**
	 * Compare 2 images and get the hamming distance.
	 *
	 * @param  mixed $resource1
	 * @param  mixed $resource2
	 * @return int
	 */
	public function compare($resource1, $resource2)
	{
		$hash1 = $this->hash($resource1);
		$hash2 = $this->hash($resource2);

		return $this->distance($hash1, $hash2);
	}

	/**
	 * Calculate the Hamming Distance.
	 *
	 * @param int $hash1
	 * @param int $hash2
	 */
	public function distance($hash1, $hash2)
	{
		if ( ! is_numeric($hash1) or ! is_numeric($hash2))
		{
			throw new UnexpectedValueException;
		}

		$bin1 = decbin($hash1);
		$bin2 = decbin($hash2);

		// Add leading zero's to the binary string.
		$bin1 = str_pad($bin1, 64, '0', STR_PAD_LEFT);
		$bin2 = str_pad($bin2, 64, '0', STR_PAD_LEFT);

		// Split into arrays
		$a1 = str_split($bin1);
		$a2 = str_split($bin2);

		$dh = 0;
		for ($i = 0; $i < count($a1); $i++)
		{
			if($a1[$i] != $a2[$i]) $dh++;
		}

		return $dh;
	}

	/**
	 * Get a file resource.
	 *
	 * @param  string $file
	 * @return resource
	 */
	protected function loadImageResource($file)
	{
		if (is_resource($file))
		{
			return $file;
		}

		try
		{
			return imagecreatefromstring(file_get_contents($file));
		}
		catch (Exception $e)
		{
			throw new Exception("Unable to load file: $file");
		}
	}

}
