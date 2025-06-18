<?php

namespace TMS\Theme\Jazzhappening\PostType;

use Closure;
use Geniem\RediPress\Entity\TextField;
use TMS\Theme\Base\Interfaces\PostType;
use TMS\Theme\Base\Settings;
use WP_Query;
use WP_Post;

/**
 * Festival CPT
 *
 * @package TMS\Theme\Jazzhappening\PostType
 */
class Festival implements PostType {

    /**
     * This defines the slug of this post type.
     */
    public const SLUG = 'festival';

    /**
     * This defines what is shown in the url. This can
     * be different than the slug which is used to register the post type.
     *
     * @var string
     */
    private $url_slug = 'festival';

    /**
     * Define the CPT description
     *
     * @var string
     */
    private $description = '';

    /**
     * This is used to position the post type menu in admin.
     *
     * @var int
     */
    private $menu_order = 5;

    /**
     * This defines the CPT icon.
     *
     * @var string
     */
    private $icon = 'dashicons-tickets-alt';

    /**
     * Constructor
     */
    public function __construct() {
        $this->description = \_x( 'Festival', 'theme CPT', 'tms-theme-jazzhappening' );
    }

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks(): void {
        \add_action( 'init', Closure::fromCallable( [ $this, 'register' ] ), 15 );
        \add_filter( 'tms/gutenberg/blocks', Closure::fromCallable( [ $this, 'allowed_blocks' ] ), 10, 1 );

        \add_filter(
            'tms/base/breadcrumbs/before_prepare',
            Closure::fromCallable( [ $this, 'format_single_breadcrumbs' ] ),
            10,
            3
        );

        \add_filter( 'redipress/schema_fields', function ( $fields ) {
            $fields[] = new TextField( [
                'name'     => 'artists',
                'sortable' => true,
            ] );

            return $fields;
        }, PHP_INT_MAX, 1 );

        \add_filter( 'redipress/additional_field/artists', function ( $value, $post_id, $post ) {
            if ( $post->post_type === Festival::SLUG ) {
                $value = \get_post_meta( $post_id, 'artists', true );
            }

            return $value;
        }, 10, 3 );

        \add_filter( 'redipress/search_fields', function ( $fields ) {
            $fields[] = 'artists';

            return $fields;
        } );

        \add_action( 'acf/save_post', [ $this, 'update_related_artists' ] );
    }

    /**
     * Get post type slug
     *
     * @return string
     */
    public function get_post_type(): string {
        return static::SLUG;
    }

    /**
     * This registers the post type.
     *
     * @return void
     */
    private function register() {
        $labels = [
            'name'                  => _x( 'Festival', 'theme CPT', 'tms-theme-jazzhappening' ),
            'singular_name'         => 'Festivaali',
            'menu_name'             => 'Festivaalit',
            'name_admin_bar'        => 'Festivaalit',
            'archives'              => 'Arkistot',
            'attributes'            => 'Ominaisuudet',
            'parent_item_colon'     => 'Vanhempi:',
            'all_items'             => 'Kaikki',
            'add_new_item'          => 'Lisää uusi',
            'add_new'               => 'Lisää uusi',
            'new_item'              => 'Uusi',
            'edit_item'             => 'Muokkaa',
            'update_item'           => 'Päivitä',
            'view_item'             => 'Näytä',
            'view_items'            => 'Näytä kaikki',
            'search_items'          => 'Etsi',
            'not_found'             => 'Ei löytynyt',
            'not_found_in_trash'    => 'Ei löytynyt roskakorista',
            'featured_image'        => 'Kuva',
            'set_featured_image'    => 'Aseta kuva',
            'remove_featured_image' => 'Poista kuva',
            'use_featured_image'    => 'Käytä kuvana',
            'insert_into_item'      => 'Aseta julkaisuun',
            'uploaded_to_this_item' => 'Lisätty tähän julkaisuun',
            'items_list'            => 'Listaus',
            'items_list_navigation' => 'Listauksen navigaatio',
            'filter_items_list'     => 'Suodata listaa',
        ];

        $rewrite = [
            'slug'       => $this->url_slug,
            'with_front' => false,
            'pages'      => true,
            'feeds'      => true,
        ];

        $args = [
            'label'           => $labels['name'],
            'description'     => '',
            'labels'          => $labels,
            'supports'        => [
                'title',
                'thumbnail',
                'excerpt',
                'editor',
            ],
            'hierarchical'    => false,
            'public'          => true,
            'menu_position'   => $this->menu_order,
            'menu_icon'       => $this->icon,
            'show_in_menu'    => true,
            'show_ui'         => true,
            'can_export'      => true,
            'has_archive'     => false,
            'rewrite'         => $rewrite,
            'show_in_rest'    => true,
            'capability_type' => 'festival',
            'map_meta_cap'    => true,
        ];

        \register_post_type( static::SLUG, $args );
    }

    /**
     * Set allowed blocks.
     *
     * @param array $blocks Block list.
     */
    public function allowed_blocks( $blocks ) {
        $allowed_blocks = [
            'acf/image',
            'acf/video',
            'acf/material',
            'acf/quote',
            'acf/map',
            'core/embed',
        ];

        foreach ( $allowed_blocks as $block ) {
            $blocks[ $block ]['post_types'][] = self::SLUG;
        }

        return $blocks;
    }

    /**
     * Get archive breadcrumbs base.
     *
     * @param false $is_cpt_archive Defines if cpt archive link is active.
     *
     * @return array[]
     */
    private function get_breadcrumbs_base( $is_cpt_archive = false ): array {
        $breadcrumbs = [
            'home' => [
                'title'     => \_x( 'Home', 'Breadcrumbs', 'tms-theme-jazzhappening' ),
                'permalink' => \trailingslashit( \get_home_url() ),
                'icon'      => '',
            ],
        ];

        $archive_page_id = Settings::get_setting( 'festival_archive_page' );

        if ( ! empty( $archive_page_id ) ) {
            $breadcrumbs[] = [
                'title'     => \get_the_title( $archive_page_id ),
                'permalink' => \get_permalink( $archive_page_id ),
                'icon'      => false,
                'is_active' => $is_cpt_archive,
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Format single view breadcrumbs.
     *
     * @param array  $breadcrumbs  Default breadcrumbs.
     * @param string $current_type Post type.
     * @param string $current_id   Current post ID.
     *
     * @return array[]
     */
    public function format_single_breadcrumbs( $breadcrumbs, $current_type, $current_id ) {
        if ( $current_type !== self::SLUG ) {
            return $breadcrumbs;
        }

        $breadcrumbs   = $this->get_breadcrumbs_base();
        $breadcrumbs[] = [
            'title'     => \get_the_title( $current_id ),
            'permalink' => false,
            'icon'      => false,
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Get all festivals.
     *
     * @return \WP_Post[]
     */
    public static function get_all(): array {
        $the_query = new WP_Query( [
            'post_type'              => self::SLUG,
            'posts_per_page'         => 200, // phpcs:ignore
            'update_post_term_cache' => false,
            'no_found_rows'          => true,
        ] );

        return $the_query->posts;
    }

    /**
     * Update related festival for search results
     *
     * @param int $post_id \WP_Post ID.
     */
    public function update_related_artists( $post_id ) {
        // Ensure this is a festival post type.
        if ( self::get_post_type() !== \get_post_type( $post_id ) ) {
            return;
        }

        // Get the artists field (relationship field).
        $artists        = \get_field( 'artists', $post_id );
        $festival_title = \get_the_title( $post_id );

        if ( empty( $artists ) ) {
            return;
        }

        // Initialize an empty string for the selected artists and festival title.
        $artists_and_title_meta = '';

        foreach ( $artists as $artist ) {
            $artist_title = \get_the_title( $artist->ID );

            // Append the artist title to the meta field.
            if ( false === strpos( $artists_and_title_meta, $artist_title ) ) {
                $artists_and_title_meta .= $artist_title . ' ';
            }

            // Get additional information repeater field.
            $additional_info = \get_field( 'additional_information', $artist->ID );

            if ( ! empty( $additional_info ) ) {
                foreach ( $additional_info as $info ) {
                    $info_text = $info['additional_information_text'] ?? '';

                    // Append the additional information text.
                    if ( ! empty( $info_text ) && false === strpos( $artists_and_title_meta, $info_text ) ) {
                        $artists_and_title_meta .= $info_text . ' ';
                    }
                }
            }
        }

        // Append the festival title to the meta field.
        $artists_and_title_meta .= $festival_title . ' ';

        // Save the concatenated string to the meta field.
        \update_post_meta( $post_id, 'artists_and_title', $artists_and_title_meta );
    }
}
