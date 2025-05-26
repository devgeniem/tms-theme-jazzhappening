<?php
/**
 * Template Name: Artistiarkisto
 */

use TMS\Theme\Base\Traits\Pagination;
use TMS\Theme\Jazzhappening\PostType\Artist;
use TMS\Theme\Jazzhappening\Taxonomy\ArtistCategory;

/**
 * Archive for Artist CPT
 */
class PageArtist extends BaseModel {

    use Pagination;

    /**
     * Template
     */
    const TEMPLATE = 'models/page-artist.php';

    /**
     * Search input name.
     */
    const SEARCH_QUERY_VAR = 'artist-search';

    /**
     * Artist category filter name.
     */
    const FILTER_QUERY_VAR = 'artist-filter';

    /**
     * Artist orderby var name.
     */
    const ORDERBY_QUERY_VAR = 'artist-sort';

    /**
     * Pagination data.
     *
     * @var object
     */
    protected object $pagination;

    /**
     * Hooks
     *
     * @return void
     */
    public static function hooks() {
    }

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
        $value = get_query_var( self::FILTER_QUERY_VAR, false );

        return ! $value
            ? null
            : intval( $value );
    }

    /**
     * Get filter query var value
     *
     * @return string
     */
    protected static function get_orderby_query_var() {
        $value = get_query_var( self::ORDERBY_QUERY_VAR );

        return sanitize_text_field( $value );
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
     * Return translated strings.
     *
     * @return array[]
     */
    public function strings(): array {
        return [
            'search'         => [
                'label'             => __( 'Search for artist', 'tms-theme-jazzhappening' ),
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
     * Return current search data.
     *
     * @return string[]
     */
    public function search(): array {
        $this->search_data        = new stdClass();
        $this->search_data->query = get_query_var( self::SEARCH_QUERY_VAR );

        return [
            'input_search_name' => self::SEARCH_QUERY_VAR,
            'current_search'    => $this->search_data->query,
            'action'            => \get_post_type_archive_link( Artist::SLUG ),
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
     * Filters
     *
     * @return array
     */
    public function filters() {
        $categories = \get_field( 'artist_categories' );

        if ( empty( $categories ) || \is_wp_error( $categories ) ) {
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
                'name'      => __( 'All', 'tms-theme-jazzhappening' ),
                'url'       => $base_url,
                'is_active' => null === self::get_filter_query_var(),
            ]
        );

        return $categories;
    }

    /**
     * Sort options
     *
     * @return array
     */
    public function sort_options() {
        $current = self::get_orderby_query_var();

        $options = [
            [
                'label' => __( 'Name', 'tms-theme-jazzhappening' ),
                'value' => '',
            ],
            [
                'label' => __( 'Name, descending', 'tms-theme-jazzhappening' ),
                'value' => 'desc',
            ],
        ];

        return array_map( function ( $item ) use ( $current ) {
            $item['is_selected'] = $item['value'] === $current ? 'selected' : '';

            return $item;
        }, $options );
    }

    /**
     * View results
     *
     * @return array
     */
    public function results() {
        $args = [
            'post_type' => Artist::SLUG,
            'paged'     => ( \get_query_var( 'paged' ) ) ? \get_query_var( 'paged' ) : 1,
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
            'posts'   => $this->format_posts( $the_query->posts ),
            'summary' => $is_filtered ? $this->results_summary( $the_query->found_posts, $search_clause ) : false,
        ];
    }

    /**
     * Format posts for view
     *
     * @param array $posts Array of WP_Post instances.
     *
     * @return array
     */
    protected function format_posts( array $posts ): array {
        return array_map( function ( $item ) {
            $item->permalink   = \get_the_permalink( $item->ID );
            $additional_fields = \get_fields( $item->ID );

            $item->fields = $additional_fields;

            $item->link = [
                'url'          => $item->permalink,
                'title'        => __( 'View artist', 'tms-theme-jazzhappening' ),
                'icon'         => 'chevron-right',
                'icon_classes' => 'icon--medium',
            ];

            return $item;
        }, $posts );
    }

    /**
     * Set pagination data
     *
     * @param WP_Query $wp_query Instance of WP_Query.
     *
     * @return void
     */
    protected function set_pagination_data( $wp_query ): void {
        $per_page = \get_option( 'posts_per_page' );
        $paged    = ( \get_query_var( 'paged' ) ) ? \get_query_var( 'paged' ) : 1;

        $this->pagination           = new stdClass();
        $this->pagination->page     = $paged;
        $this->pagination->per_page = $per_page;
        $this->pagination->items    = $wp_query->found_posts;
        $this->pagination->max_page = (int) ceil( $wp_query->found_posts / $per_page );
    }

    /**
     * Get results summary text.
     *
     * @param int    $result_count  Result count.
     * @param string $search_clause Search clause.
     *
     * @return string|bool
     */
    protected function results_summary( $result_count, $search_clause ) {
        if ( ! empty( $search_clause ) ) {
            $results_text = sprintf(
            // translators: 1. placeholder is number of search results, 2. placeholder contains the search term(s).
                _nx(
                    '%1$1s result found for "%2$2s"',
                    '%1$1s results found for "%2$2s"',
                    $result_count,
                    'filter with search clause results summary',
                    'tms-theme-jazzhappening'
                ),
                $result_count,
                $search_clause
            );
        }
        else {
            $results_text = sprintf(
            // translators: 1. placeholder is number of search results
                _nx(
                    '%1$1s result found',
                    '%1$1s results found',
                    $result_count,
                    'filter results summary',
                    'tms-theme-jazzhappening'
                ),
                $result_count,
                $search_clause
            );
        }

        return $results_text;
    }
}
