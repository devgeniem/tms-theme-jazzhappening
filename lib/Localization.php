<?php

namespace TMS\Theme\jazzhappening;

/**
 * Class Localization
 *
 * @package TMS\Theme\Sara_Hilden
 */
class Localization extends \TMS\Theme\Base\Localization implements \TMS\Theme\Base\Interfaces\Controller {

    /**
     * Load theme translations.
     */
    public function load_theme_textdomains() {
        \load_theme_textdomain(
            'tms-theme-base',
            \get_template_directory() . '/lang'
        );

        \load_child_theme_textdomain(
            'tms-theme-jazzhappening',
            \get_stylesheet_directory() . '/lang'
        );
    }
}
