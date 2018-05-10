<?php namespace Jenssegers\ImageHash;

use Intervention\Image\Image;

interface Implementation
{
    /**
     * Calculate the hash for the given image.
     *
     * @param Image $image
     * @return Hash
     */
    public function hash(Image $image);
}
