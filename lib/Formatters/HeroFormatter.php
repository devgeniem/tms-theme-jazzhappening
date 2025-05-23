<?php

namespace TMS\Theme\Jazzhappening\Formatters;

/**
 * Class HeroFormatter
 *
 * @package TMS\Theme\Base\Formatters
 */
class HeroFormatter implements \TMS\Theme\Base\Interfaces\Formatter {

    /**
     * Define formatter name
     */
    const NAME = 'Hero';

    /**
     * Hooks
     */
    public function hooks(): void {
        \add_filter(
            'tms/acf/layout/hero/data',
            [ $this, 'format' ],
            30
        );
    }

    /**
     * Format layout data
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function format( array $layout ): array {
        // Count the number of slides in the repeater and add unique ids for the slides
        $slides = $layout['carousel_repeater'] ?? [];
        $layout['slides_count'] = count( $slides );
        foreach ( $slides as $key => $slide ) {
            $layout['carousel_repeater'][ $key ]['hero_carousel_item_id'] = 'hero-carousel-item-' . $key;
        }

        return $layout;
    }
}
