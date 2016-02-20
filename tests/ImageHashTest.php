<?php

use Jenssegers\ImageHash\ImageHash;

class ImageHashTest extends PHPUnit_Framework_TestCase
{
    /** @var ImageHash */
    private $imageHash;

    public function setup()
    {
        $this->imageHash = new ImageHash();
    }

    public function testHashStringInvalidFile()
    {
        $this->expectException(Exception::class);

        $this->imageHash->hashString('nonImageString');
    }

    public function testHashStringSameAsFile()
    {
        $path = 'tests/images/forest/forest-low.jpg';

        $this->assertSame($this->imageHash->hash($path), $this->imageHash->hashString(file_get_contents($path)));
    }
}
