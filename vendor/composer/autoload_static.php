<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit80f9e15e8223f1986126bd5a93d79919
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit80f9e15e8223f1986126bd5a93d79919::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit80f9e15e8223f1986126bd5a93d79919::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit80f9e15e8223f1986126bd5a93d79919::$classMap;

        }, null, ClassLoader::class);
    }
}
