<?php

declare(strict_types=1);

namespace Jenssegers\ImageHash;

use Intervention\Image\Colors\Rgb\Colorspace as Rgb;
use Intervention\Image\Interfaces\AnalyzerInterface;
use Intervention\Image\Interfaces\ColorChannelInterface;
use Intervention\Image\Interfaces\ImageInterface;

class RgbArrayAnalyzer implements AnalyzerInterface
{
    public function __construct(protected int $x, protected int $y)
    {
        //
    }

    /**
     * Return an array of the rgb color channel values of the color at the current position.
     */
    public function analyze(ImageInterface $image): mixed
    {
        return array_map(
            fn(ColorChannelInterface $channel): int => (int) $channel->value(),
            $image->colorAt($this->x, $this->y)->toColorspace(Rgb::class)->channels()
        );
    }
}
