<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdc63b4564a6cf01ed00c0606b5305a4f
{
    public static $prefixesPsr0 = array (
        'x' => 
        array (
            'xrstf\\Composer52' => 
            array (
                0 => __DIR__ . '/..' . '/xrstf/composer-php52/lib',
            ),
        ),
    );

    public static $classMap = array (
        'WPML_CMS_Nav_Pages' => __DIR__ . '/../..' . '/inc/class-wpml-cms-nav-pages.php',
        'WPML_CMS_Navigation' => __DIR__ . '/../..' . '/inc/cms-navigation.class.php',
        'WPML_Cache_Directory' => __DIR__ . '/..' . '/wpml-shared/wpml-lib-cache/src/cache/class-wpml-cache-directory.php',
        'WPML_Dependencies' => __DIR__ . '/..' . '/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-dependencies.php',
        'WPML_Navigation_Widget' => __DIR__ . '/../..' . '/inc/widgets/sidebar_navigation_widget.class.php',
        'wpml_cms_nav_cache' => __DIR__ . '/../..' . '/inc/cache.class.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInitdc63b4564a6cf01ed00c0606b5305a4f::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitdc63b4564a6cf01ed00c0606b5305a4f::$classMap;

        }, null, ClassLoader::class);
    }
}
