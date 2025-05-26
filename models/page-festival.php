<?php
/**
 * Template Name: Festivaaliarkisto
 */

use TMS\Theme\Base\Traits\Pagination;
use TMS\Theme\Jazzhappening\PostType\Artist;
use TMS\Theme\Jazzhappening\PostType\Festival;
use TMS\Theme\Jazzhappening\Taxonomy\FestivalCategory;

/**
 * PageFestival
 */
class PageFestival extends PageArtist {

    use Pagination;

    /**
     * Template
     */
    const TEMPLATE = 'models/page-festival.php';

    /**
     * Search input name.
     */
    const SEARCH_QUERY_VAR = 'festival-search';

    /**
     * Artist category filter name.
     */
    const FILTER_QUERY_VAR = 'festival-filter';

    /**
     * Get search query var value
     *
     * @return mixed
     */
    protected static function get_search_query_var() {
        return \get_query_var( self::SEARCH_QUERY_VAR, false );
    }

    /**
     * Get filter query var value
     *
     * @return int|null
     */
    protected static function get_filter_query_var() {
        $value = \get_query_var( self::FILTER_QUERY_VAR, false );

        return ! $value
            ? null
            : intval( $value );
    }

    /**
     * Return translated strings.
     *
     * @return array[]
     */
    public function strings(): array {
        return [
            'search'         => [
                'label'             => __( 'Search for festival', 'tms-theme-jazzhappening' ),
                'submit_value'      => __( 'Search', 'tms-theme-jazzhappening' ),
                'input_placeholder' => __( 'Search query', 'tms-theme-jazzhappening' ),
            ],
            'terms'          => [
                'show_all' => __( 'Show All', 'tms-theme-jazzhappening' ),
            ],
            'no_results'     => __( 'No results', 'tms-theme-jazzhappening' ),
            'filter'         => __( 'Filter', 'tms-theme-jazzhappening' ),
            'sort'           => __( 'Sort', 'tms-theme-jazzhappening' ),
            'art_categories' => __( 'Categories', 'tms-theme-jazzhappening' ),
        ];
    }

    /**
     * Page title
     *
     * @return string
     */
    public function page_title(): string {
        return \get_the_title();
    }

    /**
     * Page description
     *
     * @return string
     */
    public function page_description(): string {
        return \get_field( 'description' ) ?? '';
    }

    /**
     * Return current search data.
     *
     * @return string[]
     */
    public function search(): array {
        $this->search_data        = new stdClass();
        $this->search_data->query = \get_query_var( self::SEARCH_QUERY_VAR );

        return [
            'input_search_name' => self::SEARCH_QUERY_VAR,
            'current_search'    => $this->search_data->query,
            'action'            => \get_the_permalink(),
        ];
    }

    /**
     * Filters
     *
     * @return array
     */
    public function filters() {
        $categories = \get_field( 'festival_categories' );

        if ( empty( $categories ) || \is_wp_error( $categories ) || 1 === count( $categories ) ) {
            return [];
        }

        $base_url   = \get_the_permalink();
        $categories = array_map( function ( $item ) use ( $base_url ) {
            return [
                'name'      => $item->name,
                'url'       => \add_query_arg(
                    [
                        self::FILTER_QUERY_VAR => $item->term_id,
                    ],
                    $base_url
                ),
                'is_active' => $item->term_id === self::get_filter_query_var(),
            ];
        }, $categories );

        array_unshift(
            $categories,
            [
                'name'      => \__( 'All', 'tms-theme-base' ),
                'url'       => $base_url,
                'is_active' => null === self::get_filter_query_var(),
            ]
        );

        return $categories;
    }

    /**
     * View results
     *
     * @return array
     */
    public function results() {
        $args = [
            'post_type' => Festival::SLUG,
            'orderby'   => 'title',
            'order'     => 'DESC',
            'paged'     => ( \get_query_var( 'paged' ) ) ? \get_query_var( 'paged' ) : 1,
        ];

        $categories = self::get_filter_query_var();

        if ( empty( $categories ) ) {
            $categories = \get_field( 'festival_categories' );
            $categories = ! empty( $categories ) ? array_map( fn( $c ) => $c->term_id, $categories ) : [];
        }

        $args['tax_query'] = [
            [
                'taxonomy' => FestivalCategory::SLUG,
                'terms'    => $categories,
            ],
        ];

        $s = self::get_search_query_var();

        if ( ! empty( $s ) ) {
            $args['s'] = $s;
        }

        $the_query = new \WP_Query( $args );

        $this->set_pagination_data( $the_query );

        $search_clause = self::get_search_query_var();
        $is_filtered   = $search_clause || self::get_filter_query_var();

        return [
            'posts'       => $this->format_posts( $the_query->get_posts() ),
            'is_filtered' => $is_filtered,
            'summary'     => $is_filtered ? $this->results_summary( $the_query->found_posts, $search_clause ) : false,
        ];
    }

    /**
     * Supply data for active filter hidden input.
     *
     * @return string[]
     */
    public function active_filter_data(): ?array {
        $active_filter = self::get_filter_query_var();

        return $active_filter ? [
            'name'  => self::FILTER_QUERY_VAR,
            'value' => $active_filter,
        ] : null;
    }

    /**
     * Sort options
     *
     * @return array
     */
    public function sort_options() {
        return [];
    }

    /**
     * Format posts for view
     *
     * @param array $posts Array of WP_Post instances.
     *
     * @return array
     */
    protected function format_posts( array $posts ): array {
        $artist_map = $this->get_artist_map();

        return array_map( function ( $item ) use ( $artist_map ) {
            if ( \has_post_thumbnail( $item->ID ) ) {
                $item->image = \get_post_thumbnail_id( $item->ID );
            }

            $item->permalink  = \get_the_permalink( $item->ID );
            $item->fields     = \get_fields( $item->ID );
            $item->categories = \wp_get_post_terms( $item->ID, FestivalCategory::SLUG, [ 'fields' => 'names' ] );

            if ( isset( $artist_map[ $item->ID ] ) ) {
                $item->artist = implode( ', ', $artist_map[ $item->ID ] );
            }

            return $item;
        }, $posts );
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

        $map = [];

        foreach ( $artists as $artist ) {
            $festivals = get_field( 'festivals', $artist->ID );

            if ( empty( $festivals ) ) {
                continue;
            }

            foreach ( $festivals as $festival ) {
                if ( ! isset( $map[ $festival->ID ] ) ) {
                    $map[ $festival->ID ] = [];
                }

                $map[ $festival->ID ][] = $artist->post_title;
            }
        }

        return $map;
    }
}
