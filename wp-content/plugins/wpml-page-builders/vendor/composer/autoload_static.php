<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5767159332ea40b2296045942c66dece
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
        'IWPML_Page_Builders_Data_Settings' => __DIR__ . '/../..' . '/classes/compatibility/interface-iwpml-page-builders-data-settings.php',
        'IWPML_Page_Builders_Module' => __DIR__ . '/../..' . '/classes/compatibility/interface-iwpml-page-builders-module.php',
        'IWPML_Page_Builders_Translatable_Nodes' => __DIR__ . '/../..' . '/classes/compatibility/interface-iwpml-page-builders-translatable-nodes.php',
        'WPML_Beaver_Builder_Accordion' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/modules/class-wpml-beaver-builder-accordion.php',
        'WPML_Beaver_Builder_Content_Slider' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/modules/class-wpml-beaver-builder-content-slider.php',
        'WPML_Beaver_Builder_Data_Settings' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/class-wpml-beaver-builder-data-settings.php',
        'WPML_Beaver_Builder_Icon_Group' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/modules/class-wpml-beaver-builder-icon-group.php',
        'WPML_Beaver_Builder_Integration_Factory' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/class-wpml-beaver-builder-integration-factory.php',
        'WPML_Beaver_Builder_Module_With_Items' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/modules/class-wpml-beaver-builder-module-with-items.php',
        'WPML_Beaver_Builder_Pricing_Table' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/modules/class-wpml-beaver-builder-pricing-table.php',
        'WPML_Beaver_Builder_Register_Strings' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/class-wpml-beaver-builder-register-strings.php',
        'WPML_Beaver_Builder_Tab' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/modules/class-wpml-beaver-builder-tab.php',
        'WPML_Beaver_Builder_Testimonials' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/modules/class-wpml-beaver-builder-testimonials.php',
        'WPML_Beaver_Builder_Translatable_Nodes' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/class-wpml-beaver-builder-translatable-nodes.php',
        'WPML_Beaver_Builder_Update_Translation' => __DIR__ . '/../..' . '/classes/compatibility/beaver-builder/class-wpml-beaver-builder-update-translation.php',
        'WPML_Dependencies' => __DIR__ . '/..' . '/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-dependencies.php',
        'WPML_Elementor_Accordion' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-accordion.php',
        'WPML_Elementor_Data_Settings' => __DIR__ . '/../..' . '/classes/compatibility/elementor/class-wpml-elementor-data-settings.php',
        'WPML_Elementor_Form' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-form.php',
        'WPML_Elementor_Icon_List' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-icon-list.php',
        'WPML_Elementor_Integration_Factory' => __DIR__ . '/../..' . '/classes/compatibility/elementor/class-wpml-elementor-integration-factory.php',
        'WPML_Elementor_Module_With_Items' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-module-with-items.php',
        'WPML_Elementor_Price_List' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-price-list.php',
        'WPML_Elementor_Price_Table' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-price-table.php',
        'WPML_Elementor_Register_Strings' => __DIR__ . '/../..' . '/classes/compatibility/elementor/class-wpml-elementor-register-strings.php',
        'WPML_Elementor_Slides' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-slides.php',
        'WPML_Elementor_Tabs' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-tabs.php',
        'WPML_Elementor_Toggle' => __DIR__ . '/../..' . '/classes/compatibility/elementor/modules/class-wpml-elementor-toggle.php',
        'WPML_Elementor_Translatable_Nodes' => __DIR__ . '/../..' . '/classes/compatibility/elementor/class-wpml-elementor-translatable-nodes.php',
        'WPML_Elementor_Update_Translation' => __DIR__ . '/../..' . '/classes/compatibility/elementor/class-wpml-elementor-update-translation.php',
        'WPML_Page_Builders_App' => __DIR__ . '/../..' . '/classes/class-wpml-page-builders-app.php',
        'WPML_Page_Builders_Defined' => __DIR__ . '/../..' . '/classes/compatibility/class-wpml-page-builders-defined.php',
        'WPML_Page_Builders_Integration' => __DIR__ . '/../..' . '/classes/class-page-builder-integration.php',
        'WPML_Page_Builders_Register_Strings' => __DIR__ . '/../..' . '/classes/compatibility/class-wpml-page-builders-register-strings.php',
        'WPML_Page_Builders_Update_Translation' => __DIR__ . '/../..' . '/classes/compatibility/class-wpml-page-builders-update-translation.php',
        'WPML_String_Registration_Factory' => __DIR__ . '/../..' . '/classes/st/class-wpml-string-registration-factory.php',
        'xrstf\\Composer52\\AutoloadGenerator' => __DIR__ . '/..' . '/xrstf/composer-php52/lib/xrstf/Composer52/AutoloadGenerator.php',
        'xrstf\\Composer52\\Generator' => __DIR__ . '/..' . '/xrstf/composer-php52/lib/xrstf/Composer52/Generator.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit5767159332ea40b2296045942c66dece::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit5767159332ea40b2296045942c66dece::$classMap;

        }, null, ClassLoader::class);
    }
}
