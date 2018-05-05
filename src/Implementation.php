<?php namespace Jenssegers\ImageHash;

use Intervention\Image\Image;

interface Implementation
{
    /**
     * Calculate the hash for the given resource.
     *
     * @param Image $image
     * @return int
     */
    public function hash(Image $image);
}
