<?php

namespace TMS\Theme\Jazzhappening\ACF;

use Closure;
use Geniem\ACF\Field;
use TMS\Theme\Base\ACF\Fields\VideoFields;
use TMS\Theme\Base\ACF\Fields\SomeLinkListFields;
use TMS\Theme\Base\Logger;

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
            [ $this, 'alter_event_fields' ],
            10,
            2
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
     * Alter fields
     *
     * @param array $fields Array of ACF fields.
     *
     * @return array
     */
    public function alter_event_fields( array $fields ) : array {
        try {
            $short_desc_field = $fields[0]->get_field('short_description');
            $short_desc_field->set_new_lines( 'br' );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
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
            'tab'            => 'Tapahtuman lisätietoja',
            'excerpt'        => [
                'label'        => 'Sitaatti',
                'instructions' => '',
            ],
            'main_content'   => [
                'label'        => 'Pääsisältö',
                'instructions' => '',
            ],
            'excerpt'        => [
                'label'        => 'Sitaatti',
                'instructions' => '',
            ],
            'image_gallery' => [
                'label'        => 'Kuvagalleria',
                'instructions' => '',
                'button'       => 'Lisää kuva',
            ],
            'image' => [
                'label'        => 'Kuva',
                'instructions' => '',
            ],
            'composition'    => [
                'label'        => 'Kokoonpano',
                'instructions' => '',
            ],
            'links'          => [
                'label'        => 'Linkit',
                'instructions' => '',
                'button'       => 'Lisää linkki',
            ],
            'link'           => [
                'label'        => 'Linkki',
                'instructions' => '',
            ],
            'spotify_embed'  => [
                'label'        => 'Spotify-upotus',
                'instructions' => 'Syötä tähän kenttään Spotify-upotuksen iframe-koodi',
            ],
            'logo_wall'      => [
                'label'        => 'Logoseinä',
                'instructions' => '',
                'button'       => 'Lisää logo',
            ],
            'logo'           => [
                'label'        => 'Logo',
                'instructions' => '',
            ],
        ];

        try {
            $tab = ( new Field\Tab( $strings['tab'] ) )
                ->set_placement( 'left' );

            $event_main_content_field = ( new Field\Wysiwyg( $strings['main_content']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_main_content' )
                ->set_name( 'event_custom_main_content' )
                ->set_toolbar( [ 'bold', 'italic', 'link' ] )
                ->disable_media_upload()
                ->set_instructions( $strings['main_content']['instructions'] );

            $event_image_gallery_field = ( new Field\Repeater( $strings['image_gallery']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_image_gallery' )
                ->set_name( 'event_custom_image_gallery' )
                ->set_button_label( $strings['image_gallery']['button'] )
                ->set_instructions( $strings['image_gallery']['instructions'] );

            $event_image_field = ( new Field\Image( $strings['image']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_image' )
                ->set_name( 'event_custom_image' )
                ->set_instructions( $strings['image']['instructions'] );

            $event_composition_field = ( new Field\Wysiwyg( $strings['composition']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_composition' )
                ->set_name( 'event_custom_composition' )
                ->set_toolbar( [ 'bold', 'italic', 'link' ] )
                ->disable_media_upload()
                ->set_instructions( $strings['composition']['instructions'] );

            $event_logo_wall_field = ( new Field\Repeater( $strings['logo_wall']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_logo_wall' )
                ->set_name( 'event_custom_logo_wall' )
                ->set_button_label( $strings['logo_wall']['button'] )
                ->set_instructions( $strings['logo_wall']['instructions'] );

            $event_logo_field = ( new Field\Image( $strings['logo']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_logo' )
                ->set_name( 'event_custom_logo' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $strings['logo']['instructions'] );

            $event_logo_link_field = ( new Field\Link( $strings['link']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_logo_link' )
                ->set_name( 'event_custom_logo_link' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $strings['link']['instructions'] );

            $event_spotify_embed_field = ( new Field\Textarea( $strings['spotify_embed']['label'] ) )
                ->set_key( 'fg_manual_event_fields_event_custom_spotify_embed' )
                ->set_name( 'event_custom_spotify_embed' )
                ->set_instructions( $strings['spotify_embed']['instructions'] );

            $event_video_embed_field = new VideoFields(
                'Videoupotus',
                'fg_manual_event_fields_event_custom_video_embed',
                'event_custom_video_embed'
            );

            $event_some_link_list_field = new SomeLinkListFields(
                'Some-linkkilista',
                'fg_manual_event_fields_event_custom_some_link_list',
                'event_custom_some_link_list'
            );

            $event_image_gallery_field->add_field( $event_image_field );
            $event_logo_wall_field->add_fields( [
                $event_logo_field,
                $event_logo_link_field,
            ] );

            $tab->add_fields( [
                $event_main_content_field,
                $event_composition_field,
                $event_some_link_list_field,
                $event_video_embed_field,
                $event_spotify_embed_field,
                $event_logo_wall_field,
                $event_image_gallery_field,
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
