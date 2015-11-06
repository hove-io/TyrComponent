<?php

namespace CanalTP\TyrComponent;

use CanalTP\TyrComponent\Exception\VersionCheckerException;
use CanalTP\TyrComponent\Exception\NotSupportedException;

class VersionChecker
{
    /**
     * Get TyrService class to use depending on currently installed Guzzle version.
     *
     * @throws NotSupportedException when Guzzle vendor version is not supported.
     *
     * @return string
     */
    public static function getTyrServiceClassName()
    {
        $guzzleVersion = VersionChecker::vendorGuzzleVersion();

        if (5 === $guzzleVersion) {
            return 'CanalTP\\TyrComponent\\TyrService';
        } elseif (3 === $guzzleVersion) {
            return 'CanalTP\\TyrComponent\\Guzzle3\\TyrService';
        }
    }

    /**
     * @return int current Guzzle vendor version.
     *
     * @throws NotSupportedException when Guzzle vendor version is not supported.
     */
    public static function vendorGuzzleVersion()
    {
        if (self::supportsGuzzle3()) {
            return 3;
        }

        if (self::supportsGuzzle5()) {
            return 5;
        }

        throw new NotSupportedException();
    }

    /**
     * @param int $version Needed Guzzle version in vendor.
     * @param string $className The class which want to use the $version of Guzzle.
     *
     * @throws NotSupportedException when Guzzle vendor version is not supported.
     * @throws VersionCheckerException when Guzzle vendor version is supported but not by $className.
     */
    public static function supportsGuzzleVersion($version, $className)
    {
        if ($version !== self::vendorGuzzleVersion()) {
            throw new VersionCheckerException($version, $className);
        }
    }

    /**
     * Check if Guzzle vendor version is 3.
     *
     * @return bool
     */
    private static function supportsGuzzle3()
    {
        return class_exists('Guzzle\\Service\\Client');
    }

    /**
     * Check if Guzzle vendor version is 5.
     *
     * @return bool
     */
    private static function supportsGuzzle5()
    {
        return class_exists('GuzzleHttp\\Client');
    }
}
