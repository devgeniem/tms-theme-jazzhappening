<?php

use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;

/**
 * Alter Content Columns Fields block
 */
class AlterContentColumnsFields {

    /**
     * Constructor
     */
    public function __construct() {
        \add_filter(
            'tms/acf/layout/_content_columns/fields',
            [ $this, 'alter_fields' ],
            10,
            2
        );
    }

    /**
     * Alter fields
     *
     * @param array $fields Array of ACF fields.
     * @param string $key   Layout key.
     *
     * @return array
     */
    public function alter_fields( array $fields, string $key ): array {

        $strings = [
            'aspect_ratio' => [
                'instructions' => 'Tekstiosio / kuvaosio. Ensimmäinen luku on tekstiosion koko, toinen kuvaosion.',
            ],
            'background_color'    => [
                'label'        => 'Taustaväri',
                'instructions' => '',
            ],
        ];

        try {
            $fields['rows']->sub_fields['layout']->set_wrapper_width( 50 );
            $fields['rows']->sub_fields['aspect_ratio']->set_wrapper_width( 50 );
            $fields['rows']->sub_fields['aspect_ratio']->set_instructions( $strings['aspect_ratio']['instructions'] );
            $fields['rows']->sub_fields['description']->set_toolbar( [ 'bold', 'italic', 'link' ] );

            $background_color_field = ( new Field\Radio( $strings['background_color']['label'] ) )
                ->set_key( "{$key}_background_color" )
                ->set_name( 'background_color' )
                ->set_layout( 'horizontal' )
                ->set_wrapper_width( 50 )
                ->set_choices( [
                    'no_color'  => 'Ei taustaväriä',
                    'primary'   => 'Pääväri',
                    'secondary' => 'Toissijainen väri',
                ] )
                ->set_instructions( $strings['background_color']['instructions'] );

            $fields['rows']->add_fields( [ $background_color_field ] );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
        return $fields;
    }
}

( new AlterContentColumnsFields() );
