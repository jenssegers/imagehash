<?php

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;

class ImageTest extends PHPUnit_Framework_TestCase {

	protected $precision = 10;

	public function setUp()
	{
		$this->hashers = [
			new AverageHash,
			new DifferenceHash
		];
	}

	public function testEqualHashes()
	{
		foreach ($this->hashers as $hasher)
		{
			$imageHash = new ImageHash($hasher);
			$images = glob('tests/images/forest/*');

			$hashes = [];
			foreach ($images as $image)
			{
				$hashes[$image] = $hash = $imageHash->hash($image);
			}

			foreach ($hashes as $image => $hash)
			{
				foreach ($hashes as $target => $compare)
				{
					if ($target == $image) continue;

					$distance = $imageHash->hammingDistance($hash, $compare);
					$this->assertLessThan($this->precision, $distance, "[" . get_class($hasher) . "] $image ($hash) <=> $target ($compare)");
				}
			}
		}
	}

	public function testDifferentHashes()
	{
		foreach ($this->hashers as $hasher)
		{
			$imageHash = new ImageHash($hasher);
			$images = glob('tests/images/office/*');

			$hashes = [];
			foreach ($images as $image)
			{
				$hashes[$image] = $hash = $imageHash->hash($image);
			}

			foreach ($hashes as $image => $hash)
			{
				foreach ($hashes as $target => $compare)
				{
					if ($target == $image) continue;

					$distance = $imageHash->hammingDistance($hash, $compare);
					$this->assertGreaterThan($this->precision, $distance, "[" . get_class($hasher) . "] $image ($hash) <=> $target ($compare)");
				}
			}
		}
	}

	public function testCompareEqual()
	{
		foreach ($this->hashers as $hasher)
		{
			$imageHash = new ImageHash($hasher);
			$images = glob('tests/images/forest/*');

			foreach ($images as $image)
			{
				foreach ($images as $target)
				{
					if ($target == $image) continue;

					$distance = $imageHash->compare($image, $target);
					$this->assertLessThan($this->precision, $distance, "[" . get_class($hasher) . "] $image <=> $target");
				}
			}
		}
	}

	public function testCompareDifferent()
	{
		foreach ($this->hashers as $hasher)
		{
			$imageHash = new ImageHash($hasher);
			$images = glob('tests/images/office/*');

			foreach ($images as $image)
			{
				foreach ($images as $target)
				{
					if ($target == $image) continue;

					$distance = $imageHash->compare($image, $target);
					$this->assertGreaterThan($this->precision, $distance, "[" . get_class($hasher) . "] $image <=> $target");
				}
			}
		}
	}

}
