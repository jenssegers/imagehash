<?php

use Intervention\Image\Exceptions\DecoderException;
use Jenssegers\ImageHash\ImageHash;
use PHPUnit\Framework\TestCase;

class ImageHashTest extends TestCase
{
    private ImageHash $imageHash;

    public function setup(): void
    {
        $this->imageHash = new ImageHash();
    }

    public function testHashInvalidFile()
    {
        $this->expectException(DecoderException::class);

        $this->imageHash->hash('nonImageString');
    }
}
