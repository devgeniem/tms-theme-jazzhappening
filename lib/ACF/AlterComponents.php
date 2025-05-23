<?php
/**
 *  Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Jazzhappening\ACF;

use TMS\Theme\Jazzhappening\ACF\Layouts\ProgramLayout;

/**
 * AlterComponents
 */
class AlterComponents {

    /**
     * Constructor
     */
    public function __construct() {
        \add_filter(
            'tms/acf/field/fg_page_components_components/layouts',
            [ $this, 'add_program_layout' ],
            10,
            2
        );
    }

    /**
     * Add Program layout to components
     *
     * @param array  $layouts Array of ACF Layouts.
     * @param string $key     Field group key.
     *
     * @return array
     */
    public function add_program_layout( array $layouts, string $key ): array {
        $layouts[] = new ProgramLayout( $key );

        return $layouts;
    }
}

( new AlterComponents() );
