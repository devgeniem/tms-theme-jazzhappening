<?php
/**
 * Define the SingleFestival class.
 */

use DustPress\Query;
use TMS\Theme\Base\Formatters\ImageFormatter;
use TMS\Theme\Base\Traits;
use TMS\Theme\Jazzhappening\PostType\Artist;
use TMS\Theme\Jazzhappening\Taxonomy\FestivalCategory;

/**
 * The SingleFestival class.
 */
class SingleFestival extends SingleArtist {

    use Traits\Sharing;

    /**
     * Content
     *
     * @return array|object|WP_Post|null
     * @throws Exception If global $post is not available or $id param is not defined.
     */
    public function content() {
        $single       = Query::get_acf_post();
        $single->meta = \get_post_meta( get_the_ID() );

        if ( ! empty( $single->image ) ) {
            $single = ImageFormatter::get_image_artist( (array) $single, (array) $single->image );
        }

        return $single;
    }

    /**
     * Get additional info.
     *
     * @return array|mixed
     */
    public function additional_info() {
        $info_rows = ! empty( \get_field( 'additional_information' ) ) ? \get_field( 'additional_information' ) : [];

        $artist_name_group = $this->get_info_group_artist_name();

        if ( ! empty( $artist_name_group ) ) {
            array_unshift(
                $info_rows,
                $artist_name_group
            );
        }

        $festival_year_group = $this->get_info_group_festival_year();

        if ( ! empty( $festival_year_group ) ) {
            array_unshift(
                $info_rows,
                $festival_year_group
            );
        }

        return $info_rows;
    }

    /**
     * Get info group artist name.
     *
     * @return array[]|null
     */
    protected function get_info_group_artist_name(): ?array {
        $group      = null;
        $artist_ids = array_keys( $this->get_artist_map() );

        if ( empty( $artist_ids ) ) {
            return null;
        }

        foreach ( $artist_ids as $artist_id ) {
            $artist_name = \get_the_title( $artist_id );

            if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
                $artist_names[] = $artist_name;
            }
        }

        if ( ! empty( $artist_names ) ) {
            $group_title = count( $artist_names ) > 1
                ? _x( 'Artists', 'theme-frontend', 'tms-theme-jazzhappening' )
                : _x( 'Artist', 'theme-frontend', 'tms-theme-jazzhappening' );

            $group = $this->format_info_group( $group_title, implode( ', ', $artist_names ) );
        }

        return $group;
    }

    /**
     * Get info group festival year
     *
     * @return array[]|string[]|null
     */
    public function get_info_group_festival_year() {
        $year = \get_field( 'year' );

        if ( ! empty( $year ) ) {
            return $this->format_info_group(
                \__( 'Year', 'tms-theme-jazzhappening' ),
                $year
            );
        }

        return null;
    }

    /**
     * Format data for use as additional information row.
     *
     * @param string $row_title Info group title.
     * @param string $row_text  Info group text content.
     *
     * @return array[]
     */
    protected function format_info_group( string $row_title, string $row_text ): array {
        return [
            'additional_information_title' => $row_title,
            'additional_information_text'  => $row_text,
        ];
    }

    /**
     * Return image gallery data.
     *
     * @return array
     */
    public function image_gallery(): array {
        $gallery_field = ! empty( \get_field( 'images' ) ) ? \get_field( 'images' ) : [];

        if ( \has_post_thumbnail() ) {
            array_unshift( $gallery_field, $this->get_featured_media_gallery_item() );
        }

        $data['rows'] = array_map( static function ( $item ) {
            $item['meta'] = ImageFormatter::format( [
                'is_clickable' => true,
                'id'           => $item['ID'],
                'image'        => $item,
            ] );

            $item['id'] = \wp_unique_id( 'image-gallery-item-' );

            return $item;
        }, $gallery_field );

        $data['gallery_id']   = \wp_unique_id( 'image-gallery-' );
        $data['translations'] = ( new \Strings() )->s()['gallery'] ?? [];

        return $data;
    }

    /**
     * Get featured media formatted for use as a gallery item.
     *
     * @return array|WP_Post|null
     */
    protected function get_featured_media_gallery_item() {
        $featured_media_id = \get_post_thumbnail_id();

        if ( function_exists( 'acf_get_attachment' ) ) {
            return \acf_get_attachment( $featured_media_id );
        }

        $img_src           = \wp_get_attachment_image_src( $featured_media_id, 'fullhd' );
        $img_post          = \get_post( $featured_media_id, ARRAY_A );
        $img_post['sizes'] = [
            'fullhd'        => $img_src[0],
            'fullhd-width'  => $img_src[1],
            'fullhd-height' => $img_src[2],
        ];

        return $img_post;
    }

    /**
     * Returns artist links.
     */
    public function artist_links() {
        $artists = \get_field( 'artists' );

        if ( empty( $artists ) ) {
            return false;
        }

        return array_map( function ( $artist ) {
            $date              = \get_field( 'date', $artist->ID );
            $description       = \get_field( 'short_description', $artist->ID );
            $featured_media_id = \get_post_thumbnail_id( $artist->ID );
            if ( $featured_media_id === 0 ) {
                $featured_media_id = false;
            }

            return [
                'link'        => \get_permalink( $artist->ID ),
                'name'        => \get_the_title( $artist->ID ),
                'image_id'    => $featured_media_id,
                'description' => $description,
                'date'        => $date,
            ];
        }, $artists );
    }

    /**
     * Get festival artist ID.
     *
     * @return array
     */
    protected function get_festival(): array {
        $map     = $this->get_artist_map();
        $festival = [];

        foreach ( $map as $map_festivals ) {
            foreach ( $map_festivals as $map_festival ) {
                if ( ! in_array( $map_festival, $festival ) ) { // phpcs:ignore
                    $festival[] = $map_festival;
                }
            }
        }

        return $festival;
    }

    /**
     * Get festivals artists map
     *
     * @return array
     */
    protected function get_artist_map(): array {
        $artists = Artist::get_all();

        if ( empty( $artists ) ) {
            return [];
        }

        $map        = [];
        $current_id = \get_the_ID();

        foreach ( $artists as $artist ) {
            $festivals = \get_field( 'festivals', $artist->ID );

            if ( empty( $festivals ) ) {
                continue;
            }

            $festival_ids = array_map( function ( $festival_item ) {
                return $festival_item->ID;
            }, $festivals );

            if ( in_array( $current_id, $festival_ids, true ) ) {
                foreach ( $festivals as $festival ) {
                    $map[ $artist->ID ][] = $festival;
                }
            }
        }

        return $map;
    }
}
