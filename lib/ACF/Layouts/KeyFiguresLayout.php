<?php

namespace TMS\Theme\Jazzhappening\ACF\Layouts;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Flexible\Layout;
use TMS\Theme\Base\Logger;
use TMS\Theme\Base\ACF\Fields\KeyFiguresFields;

/**
 * Class KeyFiguresLayout
 *
 * @package TMS\Theme\Jazzhappening\ACF\Layouts
 */
class KeyFiguresLayout extends Layout {

    /**
     * Layout key
     */
    const KEY = '_key_figures';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( string $key ) {
        parent::__construct(
            'Numeronostot',
            $key . self::KEY,
            'key_figures'
        );

        $this->add_layout_fields();
    }

    /**
     * Add layout fields.
     *
     * @return void
     * @throws Exception In case of invalid option.
     */
    public function add_layout_fields(): void {
        $strings = [
            'background_image'      => [
                'label' => 'Taustakuva',
            ],
            'text_background_image' => [
                'label' => 'Numerotekstin taustakuva',
            ],
        ];

        $key = $this->get_key();

        $group = new KeyFiguresFields( 'Numeronostot', 'key_figures' );

        $background_image = ( new Field\Image( $strings['background_image']['label'] ) )
            ->set_key( "{$key}_background_image" )
            ->set_name( 'background_image' )
            ->set_wrapper_width( 50 );

        $text_background_image = ( new Field\Image( $strings['text_background_image']['label'] ) )
            ->set_key( "{$key}_text_background_image" )
            ->set_name( 'text_background_image' )
            ->set_wrapper_width( 50 );

        $group_repeater = $group->get_fields()['rows'];
        $group_repeater->sub_fields['numbers']->add_fields( [
            $text_background_image,
            $background_image,
        ] );
        $group_repeater->sub_fields['numbers']->remove_field( 'background_color' );

        try {
            $this->add_fields(
                \apply_filters(
                    'tms/acf/layout/key_figures/fields',
                    $group->get_fields(),
                    $key
                )
            );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
