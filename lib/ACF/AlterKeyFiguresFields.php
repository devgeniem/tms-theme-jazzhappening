<?php

use TMS\Theme\Base\Logger;

/**
 * Alter Grid Fields block
 */
class AlterKeyFiguresFields {

    /**
     * Constructor
     */
    public function __construct() {
        \add_filter(
            'tms/block/key_figures/fields',
            [ $this, 'alter_fields' ],
            10,
            2
        );
    }

    /**
     * Alter fields
     *
     * @param array $fields Array of ACF fields.
     *
     * @return array
     */
    public function alter_fields( array $fields ): array {
        $strings = [
            'background_image'      => [
                'label' => 'Taustakuva',
            ],
            'text_background_image' => [
                'label' => 'Numerotekstin taustakuva',
            ],
        ];

        $background_image = ( new \Geniem\ACF\Field\Image( $strings['background_image']['label'] ) )
            ->set_key( 'background_image' )
            ->set_name( 'background_image' )
            ->set_wrapper_width( 50 );

        $text_background_image = ( new \Geniem\ACF\Field\Image( $strings['text_background_image']['label'] ) )
            ->set_key( 'text_background_image' )
            ->set_name( 'text_background_image' )
            ->set_wrapper_width( 50 );

        try {
            $fields['rows']->sub_fields['numbers']->add_fields( [
                $text_background_image,
                $background_image,
            ] );
            $fields['rows']->sub_fields['numbers']->remove_field( 'background_color' );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
        return $fields;
    }
}

( new AlterKeyFiguresFields() );
