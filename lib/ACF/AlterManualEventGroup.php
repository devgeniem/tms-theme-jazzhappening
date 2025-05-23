<?php

namespace TMS\Theme\Jazzhappening\ACF;

use Closure;
use Geniem\ACF\Field;
use TMS\Theme\Base\ACF\Fields\VideoFields;

/**
 * Class AlterManualEventGroup
 */
class AlterManualEventGroup {

    /**
     * PageGroup constructor.
     */
    public function __construct() {
        \add_filter(
            'tms/acf/group/fg_manual_event_fields/fields',
            [ $this, 'remove_event_fields' ],
            10,
            1
        );

        \add_filter(
            'tms/acf/group/fg_manual_event_fields/fields',
            [ $this, 'add_event_fields' ],
            10,
            1
        );
    }

    /**
     * Remove fields.
     *
     * @param array $fields Event fields.
     *
     * @return mixed
     */
    public function remove_event_fields( $fields ) {
        $fields[0]->remove_field( 'recurring_event' );
        $fields[0]->remove_field( 'dates' );
        $fields[0]->remove_field( 'end_datetime' );
        $fields[0]->remove_field( 'provider' );
        $fields[0]->remove_field( 'is_virtual_event' );
        $fields[0]->remove_field( 'virtual_event_link' );

        return $fields;
    }

    /**
     * Add fields.
     *
     * @param array $fields Event fields.
     *
     * @return mixed
     */
    public function add_event_fields( $fields ) {

        $strings = [
            'tab'           => 'Tapahtuman lisätietoja',
            'event_program' => [
                'label'        => 'Ohjelma',
                'instructions' => '',
            ],
            'event_artists' => [
                'label'        => 'Esiintyjät',
                'instructions' => '',
            ],
            'event_price'   => [
                'label'        => 'Hinta',
                'instructions' => '',
            ],
            'event_links'   => [
                'label'        => 'Linkit',
                'instructions' => '',
            ],
            'event_link'    => [
                'label'        => 'Linkki',
                'instructions' => '',
            ],
            'event_video'   => [
                'label'        => 'Videoupotus',
                'instructions' => '',
            ],
        ];

        try {
            $tab = ( new Field\Tab( $strings['tab'] ) )
                ->set_placement( 'left' );

            $event_program_field = ( new Field\Wysiwyg( $strings['event_program']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_program' )
                ->set_name( 'event_custom_program' )
                ->set_toolbar( [ 'bold', 'italic', 'link' ] )
                ->disable_media_upload()
                ->set_instructions( $strings['event_program']['instructions'] );

            $event_artists_field = ( new Field\Wysiwyg( $strings['event_artists']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_artists' )
                ->set_name( 'event_custom_artists' )
                ->set_toolbar( [ 'bold', 'italic', 'link' ] )
                ->disable_media_upload()
                ->set_instructions( $strings['event_artists']['instructions'] );

            $event_price_field = ( new Field\Wysiwyg( $strings['event_price']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_price' )
                ->set_name( 'event_custom_price' )
                ->set_toolbar( [ 'bold', 'italic', 'link' ] )
                ->disable_media_upload()
                ->set_instructions( $strings['event_price']['instructions'] );

            $event_links_field = ( new Field\Repeater( $strings['event_links']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_links' )
                ->set_name( 'event_custom_links' )
                ->set_instructions( $strings['event_links']['instructions'] );

            $event_link_field = ( new Field\Link( $strings['event_link']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_link' )
                ->set_name( 'event_custom_link' )
                ->set_instructions( $strings['event_link']['instructions'] );

            $event_links_field->add_field( $event_link_field );

            $video_fields = new VideoFields(
                'Videoupotus',
                'fg_manual_event_fields_event_video',
                'event_video'
            );

            $tab->add_fields( [
                $event_program_field,
                $event_artists_field,
                $event_price_field,
                $event_links_field,
                $video_fields,
            ] );

            $fields[] = $tab;

        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return $fields;
    }
}

( new AlterManualEventGroup() );
