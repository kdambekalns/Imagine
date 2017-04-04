<?php

namespace TYPO3\Imagine\Aspect;

/*
 * This file is part of the TYPO3.Imagine package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\Profile;
use Imagine\Image\ProfileInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\AOP\JoinPointInterface;
use TYPO3\Flow\Package\PackageManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * @Flow\Aspect
 */
class PaletteInterfaceAspect
{
    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\InjectConfiguration("profile")
     * @var array
     */
    protected $profiles = [];

    /**
     * @Flow\Around("method(Imagine\Image\Palette\RGB->profile())")
     *
     * @param JoinPointInterface $joinPoint The current join point
     * @return ProfileInterface
     */
    public function profileRGBAspect(JoinPointInterface $joinPoint)
    {
        return $this->profile($joinPoint, $this->profiles['RGB']);
    }

    /**
     * @Flow\Around("method(Imagine\Image\Palette\CMYK->profile())")
     *
     * @param JoinPointInterface $joinPoint The current join point
     * @return ProfileInterface
     */
    public function profileCMYKAspect(JoinPointInterface $joinPoint)
    {
        return $this->profile($joinPoint, $this->profiles['CMYK']);
    }

    /**
     * @Flow\Around("method(Imagine\Image\Palette\Grayscale->profile())")
     *
     * @param JoinPointInterface $joinPoint The current join point
     * @return ProfileInterface
     */
    public function profileGrayscaleAspect(JoinPointInterface $joinPoint)
    {
        return $this->profile($joinPoint, $this->profiles['Grayscale']);
    }

    /**
     * @param JoinPointInterface $joinPoint
     * @param string $profilePath
     * @return ProfileInterface
     */
    protected function profile(JoinPointInterface $joinPoint, $profilePath)
    {
        /** @var PaletteInterface $proxy */
        $proxy = $joinPoint->getProxy();

        try {
            $profile = ObjectAccess::getProperty($proxy, 'profile', true);
        } catch (\Exception $exception) {
            // Getting the profile will fail on the CMYK palette as the profile property is private and as the class
            // has to be reflected this private property will be on the _Original class and not on the proxy class and
            // is as such not accessible for property_exists in ObjectAccess::getProperty
            // @see NEOS-423
            return $this->createAndSetProfileOnProxy($proxy, $profilePath);
        }

        if (!$profile instanceof ProfileInterface) {
            $profile = $this->createAndSetProfileOnProxy($proxy, $profilePath);
        }

        return $profile;
    }

    /**
     * @param PaletteInterface $proxy
     * @param string $profilePath
     * @return \Imagine\Image\Profile
     */
    protected function createAndSetProfileOnProxy(PaletteInterface $proxy, $profilePath)
    {
        if (substr($profilePath, 0, 11) !== 'resource://') {
            $profilePath = $this->packageManager->getPackage('imagine.imagine')->getPackagePath() . 'lib/Imagine/resources/' . $profilePath;
        }
        $profile = Profile::fromPath($profilePath);
        ObjectAccess::setProperty($proxy, 'profile', $profile, true);

        return $profile;
    }
}
