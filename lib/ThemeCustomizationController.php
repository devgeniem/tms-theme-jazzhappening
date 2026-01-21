<?php

namespace TMS\Theme\Jazzhappening;

use WP_post;
use function add_filter;
use TMS\Theme\Base\PostType\Page;
use TMS\Theme\Base\PostType\Post;

/**
 * Class ThemeCustomizationController
 *
 * @package TMS\Theme\Base
 */
class ThemeCustomizationController implements \TMS\Theme\Base\Interfaces\Controller {

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks(): void {
        \add_filter( 'tms/theme/search/search_item', [ $this, 'event_search_classes' ] );
        \add_filter( 'tms/theme/nav_parent_link_is_trigger_only', '__return_true' );

        \add_filter( 'tms/theme/header/colors', [ $this, 'header' ] );
        \add_filter( 'tms/theme/footer/colors', [ $this, 'footer' ] );
        \add_filter( 'tms/theme/search/search_item', [ $this, 'search_classes' ] );

        \add_filter( 'tms/theme/single_blog/classes', [ $this, 'single_blog_classes' ] );
        \add_filter( 'comment_form_submit_button', [ $this, 'comments_submit' ], 15, 0 );
        \add_filter( 'comment_reply_link', [ $this, 'reply_link_classes' ], 15, 1 );

        \add_filter( 'tms/theme/error404/search_link', [ $this, 'error404_search_link' ] );
        \add_filter( 'tms/acf/block/material/data', function ( $data ) {
            $data['button_classes'] = 'is-primary';

            return $data;
        } );
        \add_filter( 'tms/acf/layout/hero/data', function ( $data ) {
            $data['button_classes'] = 'is-primary';

            return $data;
        } );
        \add_filter( 'tms/acf/layout/icon_links/data', function ( $data ) {
            $data['button_classes'] = 'is-primary';

            return $data;
        } );
        \add_filter( 'tms/acf/layout/content_columns/data',
            [ $this, 'alter_content_columns_data' ],
            30
        );
        \add_filter( 'tms/acf/layout/textblock/data', function ( $data ) {
            $data['link_class'] = 'is-primary';

            return $data;
        } );
        \add_filter(
            'tms/theme/event/hero_info_classes',
            fn() => 'has-colors-primary'
        );

        \add_filter(
            'tms/gutenberg/blocks',
            [ $this, 'alter_blocks' ]
        );
    }

    /**
     * Header
     *
     * @param array $colors Color classes.
     *
     * @return array Array of customized colors.
     */
    public function header( $colors ): array {
        $colors['nav']['container']            = '';
        $colors['search_popup_container']      = 'has-text-primary-invert';
        $colors['lang_nav']['link__default']   = 'has-text-primary-invert';
        $colors['lang_nav']['link__active']    = 'has-background-primary has-text-primary-invert';
        $colors['lang_nav']['dropdown_toggle'] = 'is-small';
        $colors['fly_out_nav']['inner']        = 'has-text-primary-invert';
        $colors['fly_out_nav']['search_title'] = 'has-text-white';
        $colors['search_button']               = 'is-primary-invert is-outlined';

        return $colors;
    }

    /**
     * Footer
     *
     * @param array $classes Footer classes.
     *
     * @return array
     */
    public function footer( array $classes ): array {
        $classes['container']   = '';
        $classes['back_to_top'] = 'is-outlined-invert';
        $classes['link']        = 'has-text-paragraph';
        $classes['link_icon']   = 'is-secondary';

        return $classes;
    }

    /**
     * Search classes.
     *
     * @param array $classes Search view classes.
     *
     * @return array
     */
    public function search_classes( $classes ): array {
        $classes['event_search_section'] = '';

        return $classes;
    }

    /**
     * Override event item classes.
     *
     * @param array $classes Classes.
     *
     * @return array
     */
    public function single_blog_classes( $classes ): array {
        $classes['info_section']         = '';
        $classes['info_section_authors'] = '';
        $classes['info_section_button']  = 'is-primary';

        return $classes;
    }

    /**
     * Override comment form submit button.
     *
     * @return string
     */
    public function comments_submit(): string {
        return sprintf(
            '<button name="submit" type="submit" id="submit" class="button button--icon is-primary" >%s %s</button>', // phpcs:ignore
            __( 'Send Comment', 'tms-theme-base' ),
            '<svg class="icon icon--arrow-right icon--large">
                <use xlink:href="#icon-arrow-right"></use>
            </svg>'
        );
    }

    /**
     * Customize reply link.
     *
     * @param string $link The HTML markup for the comment reply link.
     *
     * @return string
     */
    public function reply_link_classes( string $link ): string {
        return str_replace( 'comment-reply-link', 'comment-reply-link is-small', $link );
    }

    /**
     * Alter content columns classes.
     *
     * @param array $data Block data.
     *
     * @return mixed
     */
    public function alter_content_columns_data( $data ) {
        if ( empty( $data['rows'] ) ) {
            return $data;
        }

        $aspect_ratios = [
            '50-50' => [
                'is-6-desktop',
                'is-6-desktop',
            ],
            '70-30' => [
                'is-8-desktop',
                'is-4-desktop',
            ],
            '30-70' => [
                'is-4-desktop',
                'is-8-desktop',
            ],
        ];

        $data['rows'] = array_map( function ( $item ) use ( $aspect_ratios ) {
            $item['text_col_class'] = [ 'is-6' ];
            $item['img_col_class']  = [ 'is-6' ];
            $ratio                  = $item['aspect_ratio'];

            if ( array_key_exists( $ratio, $aspect_ratios ) ) {
                $item['text_col_class'][] = $aspect_ratios[ $ratio ][0];
                $item['img_col_class'][]  = $aspect_ratios[ $ratio ][1];
            }

            if ( $item['layout'] === 'is-text-first' ) {
                $item['item_class']       = 'is-reversed-tablet is-justify-content-flex-end';
                $item['text_col_class'][] = '';
            }
            else {
                $item['img_col_class'][] = '';
            }

            $item['text_col_class'] = implode( ' ', $item['text_col_class'] );
            $item['img_col_class']  = implode( ' ', $item['img_col_class'] );

            // Add bg-color
            $item['background_color'] = 'has-background-' . $item['background_color'];

            return $item;
        }, $data['rows'] );

        return $data;
    }

    /**
     * Override event item classes.
     *
     * @param array $classes Classes.
     *
     * @return array
     */
    public function event_search_classes( $classes ): array {
        $classes['search_form'] = 'events__search-form';

        return $classes;
    }

    /**
     * Override event search link classes.
     *
     * @param array $link Link details.
     *
     * @return array
     */
    public function error404_search_link( $link ): array {
        $link['classes'] = '';

        return $link;
    }

    /**
     * Alter theme blocks
     *
     * @param array $blocks Theme blocks.
     *
     * @return array
     */
    public function alter_blocks( $blocks ) {
        $blocks['core/embed'] = [
            'post_types' => [
                Post::SLUG,
                Page::SLUG,
            ],
        ];

        return $blocks;
    }
}
