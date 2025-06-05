<?php

use TMS\Plugin\ManualEvents\PostType;
use TMS\Theme\Base\Traits\Pagination;
use TMS\Theme\Base\Logger;
use TMS\Theme\Base\Formatters\EventzFormatter;

/**
 * Template Name: Tapahtumalistaus (yhdistetty)
 */

/**
 * The PageCombinedEventsList class.
 */
class PageCombinedEventsList extends PageEventsSearch {

    use Pagination;

    /**
     * Template
     */
    const TEMPLATE = 'page-combined-events-list.php';

    /**
     * Day filter name.
     */
    const DAY_QUERY_VAR = 'event-day';

    /**
     * Category filter name.
     */
    const CATEGORY_QUERY_VAR = 'event-category';

    /**
     * Order filter name.
     */
    const ORDER_QUERY_VAR = 'event-order';

    /**
     * Return form fields.
     *
     * @return array
     */
    public function form() {
        return [];
    }

    /**
     * Description text
     */
    public function description() : ?string {
        return \get_field( 'description' );
    }

    /**
     * Get no results text
     *
     * @return string
     */
    public function no_results() : string {
        return \__( 'No results', 'tms-theme-base' );
    }

    /**
     * Hooks
     */
    public static function hooks() : void {
        \add_action(
            'pre_get_posts',
            [ __CLASS__, 'modify_event_query' ]
        );
    }

    /**
     * Get events
     */
    public function events() : ?array {
        try {
            $response = $this->get_events();

            return $response['events'] ?? [];
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return null;
    }

    /**
     * Get events.
     *
     * @return array
     */
    protected function get_events() : array {

        $paged = \get_query_var( 'paged', 1 );
        $skip  = 0;

        if ( $paged > 1 ) {
            $skip = ( $paged - 1 ) * \get_option( 'posts_per_page' );
        }

        $params = [
            'category_id' => \get_field( 'category' ),
            'page_size'   => 200, // Use an arbitrary limit as a sanity check.
            'show_images' => \get_field( 'show_images' ),
            'start'       => date( 'Y-m-d' ),
            'areas'       => '',
            'tags'        => '',
            'targets'     => '',
            'sort'        => '',
            'q'           => '',
            'page'        => 1,
        ];

        $formatter = new EventzFormatter();
        $params    = $formatter->format_query_params( $params );

        $cache_group = 'page-combined-events-list';
        $cache_key   = md5( \wp_json_encode( $params ) );
        $response    = \wp_cache_get( $cache_key, $cache_group );

        if ( empty( $response ) ) {
            $response           = $this->do_get_events( $params );
            $response['events'] = $this->get_manual_events();

            if ( ! empty( $response ) ) {
                $this->set_pagination_data( count( $response['events'] ) );

                $response['events'] = array_slice( $response['events'], $skip, \get_option( 'posts_per_page' ) );
            }
        }

        return $response;
    }

    /**
     * Get manual events.
     *
     * @return array
     */
    protected function get_manual_events() : array {
        $args = [
            'post_type'      => PostType\ManualEvent::SLUG,
            'posts_per_page' => 200, // phpcs:ignore
            'meta_query'     => [
                'recurring_event_clause' => [
                    [
                        'key'   => 'recurring_event',
                        'value' => 0,
                    ],
                ],
            ],
        ];

        $query = new \WP_Query( $args );

        if ( empty( $query->posts ) ) {
            return [];
        }

        $events = array_map( function ( $e ) {
            $id                  = $e->ID;
            $event               = (object) \get_fields( $id );
            $event->id           = $id;
            $event->title        = \get_the_title( $id );
            $event->url          = \get_permalink( $id );
            $event->image        = \has_post_thumbnail( $id ) ? \get_the_post_thumbnail_url( $id, 'medium_large' ) : null;
            $event->end_datetime = null;

            return PostType\ManualEvent::normalize_event( $event );
        }, $query->posts );

        return $events;
    }

    /**
     * Get filter query var value
     *
     * @return string|null
     */
    protected static function get_day_query_var() : ?string {
        $value = \get_query_var( self::DAY_QUERY_VAR, false );

        return ! $value
            ? null
            : \sanitize_text_field( $value );
    }

    /**
     * Get filter query var value
     *
     * @return string|null
     */
    protected static function get_category_query_var() : ?string {
        $value = \get_query_var( self::CATEGORY_QUERY_VAR, false );

        return ! $value
            ? null
            : \sanitize_text_field( $value );
    }

    /**
     * Get order query var value
     *
     * @return string|null
     */
    protected static function get_order_query_var() : ?string {
        $value = \get_query_var( self::ORDER_QUERY_VAR, false );

        return ! $value
            ? null
            : \sanitize_text_field( $value );
    }

    /**
     * Category filters
     *
     * @return array
     */
    public function filter_strings() : array {
        $strings = [
            'day'          => \__( 'Filter by day', 'tms-theme-jazzhappening' ),
            'change_order' => \__( 'Change order', 'tms-theme-jazzhappening' ),
            'order'        => \__( 'Order:', 'tms-theme-jazzhappening' ),
        ];

        return $strings;
    }

    /**
     * Days filter
     *
     * @return array
     */
    public function days() {
        $selected_day = self::get_day_query_var();
        $choices      = [];

        // Remove query modifications to get all posts
        \remove_action( 'pre_get_posts', [ __CLASS__, 'modify_event_query' ] );

        // Query for all manual events to get all available dates
        $dates_args = [
            'post_type'      => PostType\ManualEvent::SLUG,
            'posts_per_page' => -1,
            'meta_key'       => 'start_datetime',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
        ];

        // Check if category is selected
        $cat = self::get_category_query_var();
        if ( ! empty( $cat ) ) {
            $dates_args = [
                'post_type'      => PostType\ManualEvent::SLUG,
                'posts_per_page' => -1,
                'meta_key'       => 'start_datetime',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
                'tax_query'      => [
                    [
                        'taxonomy' => 'manual-event-category',
                        'field'    => 'slug',
                        'terms'    => $cat,
                    ],
                ]
            ];
        }

        $dates_query = new \WP_Query( $dates_args );

        // Add query modifications back
        \add_action( 'pre_get_posts', [ __CLASS__, 'modify_event_query' ] );

        if ( empty( $dates_query->posts ) ) {
            return [];
        }

        $choices[] = [
            'label'       => \__( 'All days', 'tms-theme-jazzhappening' ),
            'value'       => '',
            'is_selected' => empty( $selected_day ) ? 'selected' : '',
        ];

        foreach ( $dates_query->posts as $single_event ) {
            $start_datetime = \get_field( 'start_datetime', $single_event->ID );
            $event_date     = date( 'd.m.Y', strtotime( $start_datetime ) );

            // Skip duplicate dates
            if ( in_array( $event_date, array_column( $choices, 'value' ) ) ) {
                continue;
            }

            $choices[] = [
                'label'       => $event_date,
                'value'       => $event_date,
                'is_selected' => $event_date === $selected_day ? 'selected' : '',
            ];
        }

        return $choices;
    }

    /**
     * Category filters
     *
     * @return array
     */
    public function categories() : array {
        $selected_cat = self::get_category_query_var();
        $categories   = [];
        $terms        = \get_terms( 'manual-event-category' );

        if ( empty( $terms ) ) {
            return [];
        }

        $categories[] = [
            'name'      => \__( 'All', 'tms-theme-base' ),
            'url'       => '',
            'is_active' => empty( $selected_day ) ? 'selected' : '',
        ];

        foreach ( $terms as $term ) {
            $categories[] = [
                'name'      => $term->name,
                'value'     => $term->slug,
                'is_active' => $term->slug === $selected_cat,
            ];
        }

        return $categories;
    }

        /**
     * Days filter
     *
     * @return array
     */
    public function event_order() {
        $selected_order = self::get_order_query_var();

        $choices = [
            [
                'value'       => 'date',
                'is_selected' => 'date' === $selected_order ? 'selected' : '',
                'label'       => \__( 'Date', 'tms-theme-jazzhappening' ),
            ],
            [
                'value'       => 'title',
                'is_selected' => 'title' === $selected_order ? 'selected' : '',
                'label'       => \__( 'Alphabetical', 'tms-theme-jazzhappening' ),
            ],
        ];

        return $choices;
    }

    /**
     * Modify query
     *
     * @param WP_Query $wp_query Instance of WP_Query.
     *
     * @return void
     */
    public static function modify_event_query( WP_Query $wp_query ) : void {
        if ( \is_admin() || ! \is_page_template( PageCombinedEventsList::TEMPLATE ) ) {
            return;
        }

        $day         = self::get_day_query_var();
        $cat         = self::get_category_query_var();
        $event_order = self::get_order_query_var();

        if ( empty( $day ) && empty( $cat ) && empty( $event_order ) ) {
            return;
        }

        $meta_query = [];

        if ( ! empty( $day ) ) {
            $start_of_day = date( 'Y-m-d H:i:s', strtotime( $day . ' 00:00:00' ) );
            $end_of_day   = date( 'Y-m-d H:i:s', strtotime( $day . ' 23:59:59' ) );

            $meta_query[] = [
                'key'     => 'start_datetime',
                'value'   => [ $start_of_day, $end_of_day ],
                'compare' => 'BETWEEN',
                'type'    => 'DATETIME',
            ];
        }

        // Category filtering
        if ( ! empty( $cat ) ) {
            $tax_query = [
                [
                    'taxonomy' => 'manual-event-category',
                    'field'    => 'slug',
                    'terms'    => $cat,
                ],
            ];

            $wp_query->set( 'tax_query', $tax_query );
        }

        // Event ordering
        if ( ! empty( $event_order ) ) {
            if ($event_order === 'date') {
                $wp_query->set( 'meta_key', 'start_datetime' );
                $wp_query->set( 'orderby', 'meta_value_num' );
                $wp_query->set( 'order', 'ASC' );
            } else {
                $wp_query->set( 'orderby', $event_order );
                $wp_query->set( 'order', 'ASC' );
            }
        }

        if ( ! empty( $meta_query ) ) {
            $wp_query->set( 'meta_query', $meta_query );
        }
    }
}
