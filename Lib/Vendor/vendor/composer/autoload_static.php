<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf5fc774afcf8fbc04b41adcd1c22e0d2
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Driver\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Driver\\' => 
        array (
            0 => __DIR__ . '/../../..' . '/Driver',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf5fc774afcf8fbc04b41adcd1c22e0d2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf5fc774afcf8fbc04b41adcd1c22e0d2::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
