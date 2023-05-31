<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit85c8ac6ac8ba7a3fac3424d2d7ce3b96
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit85c8ac6ac8ba7a3fac3424d2d7ce3b96::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit85c8ac6ac8ba7a3fac3424d2d7ce3b96::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit85c8ac6ac8ba7a3fac3424d2d7ce3b96::$classMap;

        }, null, ClassLoader::class);
    }
}
