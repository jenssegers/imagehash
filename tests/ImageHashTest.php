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

    public function testHexdecForNegativeIntegers()
    {
        // native hexdec dechex conversion working for positive integers
        $this->assertEquals(1, hexdec(dechex(1)));
        // but not working for negative
        $this->assertNotEquals(-1, hexdec(dechex(-1)));

        // custom hexdec implementation works for both
        $this->assertEquals(1, $this->imageHash->hexdec(dechex(1)));
        $this->assertEquals(-1, $this->imageHash->hexdec(dechex(-1)));
    }

    public function testDistanceOfNegativeHashes()
    {
        $imageHash = new ImageHash(null, ImageHash::HEXADECIMAL);
        $hash1 = 'ffffffffffffffff'; // -1
        $hash2 = 'fffffffffffffff0'; // -16

        $distance = $imageHash->distance($hash1, $hash2);
        $this->assertEquals(4, $distance);
    }
}
