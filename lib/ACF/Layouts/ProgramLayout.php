<?php

namespace TMS\Theme\Jazzhappening\ACF\Layouts;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Flexible\Layout;
use TMS\Plugin\ManualEvents\PostType;
use TMS\Theme\Base\Logger;

/**
 * Class ProgramLayout
 *
 * @package TMS\Theme\Jazzhappening\ACF\Layouts
 */
class ProgramLayout extends Layout {

    /**
     * Layout key
     */
    const KEY = '_program';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( string $key ) {
        parent::__construct(
            'Ohjelma',
            $key . self::KEY,
            'program'
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
        $key = $this->get_key();

        $strings = [
            'repeater' => [
                'label'        => 'Osiot',
                'instructions' => '',
            ],
            'date'    => [
                'label'        => 'Päivämäärä',
                'instructions' => '',
            ],
            'manual_events'    => [
                'label'        => 'Tapahtumat',
                'instructions' => '',
            ],
            'location'  => [
                'label'        => 'Sijainti',
                'instructions' => '',
            ],
            'time'  => [
                'label'        => 'Aikataulu',
                'instructions' => '',
            ],
            'price'  => [
                'label'        => 'Hinta',
                'instructions' => '',
            ],
            'ticket_link'    => [
                'label'        => 'Linkki lipun ostoon',
                'instructions' => '',
            ],
        ];

        $repeater = ( new Field\Repeater( $strings['repeater']['label'] ) )
            ->set_key( "{$key}_repeater" )
            ->set_name( 'repeater' )
            ->set_layout( 'block' )
            ->set_instructions( $strings['repeater']['instructions'] );

        $date_field = ( new Field\DatePicker( $strings['date']['label'] ) )
            ->set_key( "{$key}_date" )
            ->set_name( 'date' )
            ->set_wrapper_width( 50 )
            ->set_return_format( 'Y-m-d' )
            ->set_required()
            ->set_instructions( $strings['date']['instructions'] );

        $manual_events_field = ( new Field\Relationship( $strings['manual_events']['label'] ) )
            ->set_key( "{$key}_manual_events" )
            ->set_name( 'manual_events' )
            ->set_filters( [ 'search' ] )
            ->set_post_types( [ PostType\ManualEvent::SLUG ] )
            ->set_return_format( 'id' )
            ->set_max( 4 )
            ->set_instructions( $strings['manual_events']['instructions'] );

        $location_field = ( new Field\Text( $strings['location']['label'] ) )
            ->set_key( "{$key}_location" )
            ->set_name( 'location' )
            ->set_required()
            ->set_instructions( $strings['location']['instructions'] );

        $time_field = ( new Field\Text( $strings['time']['label'] ) )
            ->set_key( "{$key}_time" )
            ->set_name( 'time' )
            ->set_required()
            ->set_instructions( $strings['time']['instructions'] );

        $price_field = ( new Field\Text( $strings['price']['label'] ) )
            ->set_key( "{$key}_price" )
            ->set_name( 'price' )
            ->set_required()
            ->set_instructions( $strings['price']['instructions'] );

        $ticket_link_field = ( new Field\Link( $strings['ticket_link']['label'] ) )
            ->set_key( "{$key}_ticket_link" )
            ->set_name( 'ticket_link' )
            ->set_instructions( $strings['ticket_link']['instructions'] );

        $repeater->add_fields( [
            $date_field,
            $manual_events_field,
            $location_field,
            $time_field,
            $price_field,
            $ticket_link_field,
        ] );

        try {
            $this->add_fields(
                \apply_filters(
                    'tms/acf/layout/program/fields',
                    [ $repeater ],
                    $key
                )
            );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
