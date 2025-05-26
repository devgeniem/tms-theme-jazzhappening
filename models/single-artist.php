<?php
/**
 * Define the SingleArtist class.
 */

use DustPress\Query;
use TMS\Theme\Base\Traits;
use TMS\Theme\Jazzhappening\PostType\Festival;

/**
 * The SingleArtist class.
 */
class SingleArtist extends BaseModel {

    use Traits\Sharing;

    /**
     * Content
     *
     * @return array|object|WP_Post|null
     * @throws Exception If global $post is not available or $id param is not defined.
     */
    public function content() {
        $single = Query::get_acf_post();

        return $single;
    }

    /**
     * Prepend additional information rows with artist years.
     *
     * @return array Additional information rows.
     */
    public function additional_information() {
        $additional_information = \get_field( 'additional_information' );

        if ( empty( $additional_information ) ) {
            $additional_information = [];
        }

        return $additional_information;
    }

    /**
     * Returns festival links.
     */
    public function festivals_string() {
        return \__( 'Festivals', 'tms-theme-jazzhappening' );
    }

    /**
     * Returns festival links.
     */
    public function festival_links() {
        $festivals = array_keys( $this->get_festival_map() );

        if ( empty( $festivals ) ) {
            return false;
        }

        return array_map( function ( $festival ) {
            $featured_media_id = \get_post_thumbnail_id( $festival );

            if ( $featured_media_id === 0 ) {
                $featured_media_id = false;
            }

            return [
                'link'        => \get_permalink( $festival ),
                'name'        => \get_the_title( $festival ),
                'image_id'    => $featured_media_id,
            ];
        }, $festivals );
    }

    /**
     * Get map of festivals this artist is selected in.
     *
     * @return array
     */
    protected function get_festival_map(): array {
        $festivals = Festival::get_all();

        if ( empty( $festivals ) ) {
            return [];
        }

        $map        = [];
        $current_id = \get_the_ID();

        foreach ( $festivals as $festival ) {
            $artists = \get_field( 'artists', $festival->ID );

            if ( empty( $artists ) ) {
                continue;
            }

            $artist_ids = array_map( function ( $artist_item ) {
                return $artist_item->ID;
            }, $artists );

            if ( in_array( $current_id, $artist_ids, true ) ) {
                $map[ $festival->ID ] = $festival;
            }
        }

        return $map;
    }
}
