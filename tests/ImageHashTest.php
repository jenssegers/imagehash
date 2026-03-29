<?php

use Intervention\Image\Exceptions\RuntimeException;
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
        $this->expectException(RuntimeException::class);

        $this->imageHash->hash('nonImageString');
    }
}
