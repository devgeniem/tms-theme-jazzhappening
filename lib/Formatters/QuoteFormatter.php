<?php
namespace TMS\Theme\Jazzhappening\Formatters;

use TMS\Theme\Base\PostType\BlogArticle;
use TMS\Theme\Base\PostType\Page;
use TMS\Theme\Base\PostType\Post;

/**
 * Class QuoteFormatter
 *
 * @package TMS\Theme\Base\Formatters
 */
class QuoteFormatter implements \TMS\Theme\Base\Interfaces\Formatter {

    /**
     * Define formatter name
     */
    const NAME = 'Quote';

    /**
     * Hooks
     */
    public function hooks(): void {
        \add_filter(
            'tms/acf/block/quote/data',
            [ $this, 'format' ]
        );
    }

    /**
     * Format layout data
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function format( array $data ): array {
        $classes = [
            'container' => [
                'has-background-secondary',
                'has-text-secondary-invert',
                'mt-5',
                'mb-5',
            ],
            'quote'     => [
                'is-family-secondary',
                'has-text-weight-medium',
                'is-size-5',
                \is_singular( [ Page::SLUG, Post::SLUG, BlogArticle::SLUG ] ) ? 'is-size-1' : 'is-size-5',
            ],
            'author'    => [
                'has-text-weight-medium',
                'is-family-secondary ',
            ],
        ];

        if ( ! empty( $data['is_wide'] ) ) {
            $classes['container'][] = 'is-align-wide';
        }

        $data['classes'] = $classes;

        return $data;
    }
}
