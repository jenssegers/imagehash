<?php namespace Jenssegers\ImageHash;

interface Implementation {

    /**
     * Calculate the hash for the given resource.
     *
     * @param  resource $resource
     * @return integer
     */
    public function hash($resource);

}
