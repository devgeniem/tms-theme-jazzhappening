<?php

namespace TMS\Theme\Jazzhappening\ACF;

use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;

/**
 * Class AlterTextBlockFields
 *
 * @package TMS\Theme\Jazzhappening\ACF
 */
class AlterTextBlockFields {
    /**
     * AlterTextBlockFields constructor.
     */
    public function __construct() {
        \add_filter( 'tms/acf/layout/_textblock/fields', [ $this, 'alter_fields' ], 10, 2 );
    }

    /**
     * Add centering option to textblock layout.
     *
     * @param array  $fields Array of ACF fields.
     * @param string $key    Layout key.
     *
     * @return array
     */
    public function alter_fields( array $fields, string $key ): array {
        $strings = [
            'center_content' => [
                'label'        => 'Keskitä sisältö',
                'instructions' => '',
            ],
        ];

        try {
            $center_content_field = ( new Field\TrueFalse( $strings['center_content']['label'] ) )
                ->set_key( "{$key}_center_content" )
                ->set_name( 'center_content' )
                ->use_ui()
                ->set_instructions( $strings['center_content']['instructions'] );

            $fields[] = $center_content_field;

        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return $fields;
    }
}

( new AlterTextBlockFields() );
