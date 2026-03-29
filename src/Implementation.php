<?php namespace Jenssegers\ImageHash;

use Intervention\Image\Interfaces\ImageInterface;

interface Implementation
{
    public function hash(ImageInterface $image): Hash;
}
