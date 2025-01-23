<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb17ca08aa26661d163e83029f04db81c
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'StickyQuickConnector\\' => 21,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'StickyQuickConnector\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb17ca08aa26661d163e83029f04db81c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb17ca08aa26661d163e83029f04db81c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb17ca08aa26661d163e83029f04db81c::$classMap;

        }, null, ClassLoader::class);
    }
}
