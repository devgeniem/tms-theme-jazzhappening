<?php

namespace TMS\Theme\Jazzhappening;

use Closure;
use PageCombinedEventsList;
use PageArtist;
use PageFestival;
use TMS\Theme\Base\Interfaces\Controller;

/**
 * Class ThemeSupports
 *
 * @package TMS\Theme\Sara_Hilden
 */
class ThemeSupports implements Controller {

    /**
     * Initialize the class' variables and add methods
     * to the correct action hooks.
     *
     * @return void
     */
    public function hooks(): void {
        // Allow custom url links to be added in menus
        \add_filter(
            'tms/theme/remove_custom_links',
            '__return_false'
        );

        \add_filter(
            'query_vars',
            Closure::fromCallable( [ $this, 'query_vars' ] )
        );
    }

    /**
     * Append custom query vars
     *
     * @param array $vars Registered query vars.
     *
     * @return array
     */
    protected function query_vars( $vars ) {
        $vars[] = PageCombinedEventsList::DAY_QUERY_VAR;
        $vars[] = PageCombinedEventsList::CATEGORY_QUERY_VAR;
        $vars[] = PageCombinedEventsList::ORDER_QUERY_VAR;

        $vars[] = PageArtist::SEARCH_QUERY_VAR;
        $vars[] = PageArtist::FILTER_QUERY_VAR;
        $vars[] = PageArtist::ORDERBY_QUERY_VAR;

        $vars[] = PageFestival::SEARCH_QUERY_VAR;
        $vars[] = PageFestival::FILTER_QUERY_VAR;

        return $vars;
    }
}
