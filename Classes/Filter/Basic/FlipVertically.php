<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Imagine\Filter\Basic;

use Imagine\Image\ImageInterface;
use Imagine\Filter\FilterInterface;

/**
 * A "flip vertically" filter
 */
class FlipVertically implements FilterInterface
{
    /**
     * (non-PHPdoc)
     * @see Imagine\Filter\FilterInterface::apply()
     */
    public function apply(ImageInterface $image)
    {
        return $image->flipVertically();
    }
}
