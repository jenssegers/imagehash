<?php

use Intervention\Image\Exception\NotReadableException;
use Jenssegers\ImageHash\ImageHash;
use PHPUnit\Framework\TestCase;

class ImageHashTest extends TestCase
{
    /**
     * @var ImageHash
     */
    private $imageHash;

    public function setup()
    {
        $this->imageHash = new ImageHash();
    }

    public function testHashInvalidFile()
    {
        $this->expectException(NotReadableException::class);

        $this->imageHash->hash('nonImageString');
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
