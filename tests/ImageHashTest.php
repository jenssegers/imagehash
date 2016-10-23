<?php

use Jenssegers\ImageHash\ImageHash;

class ImageHashTest extends PHPUnit_Framework_TestCase
{
    /**
	 * @var ImageHash
	 */
    private $imageHash;

    public function setup()
    {
        $this->imageHash = new ImageHash();
    }

    public function testHashStringInvalidFile()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('InvalidArgumentException');
        } else {
            $this->setExpectedException('InvalidArgumentException');
        }

        $this->imageHash->hashFromString('nonImageString');
    }

    public function testHashStringSameAsFile()
    {
        $path = 'tests/images/forest/forest-low.jpg';

        $this->assertSame($this->imageHash->hash($path), $this->imageHash->hashFromString(file_get_contents($path)));
    }
}
