<?php namespace Jenssegers\ImageHash;

use Intervention\Image\Image;

interface Implementation
{
    public function hash(Image $image): Hash;
}
