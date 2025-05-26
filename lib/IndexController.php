<?php
/**
 * Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Jazzhappening;

use Geniem\RediPress\Entity\TextField;
use TMS\Theme\Base\Interfaces\Controller;

/**
 * Class IndexController
 *
 * @package TMS\Theme\Jazzhappening
 */
class IndexController implements Controller {
    /**
     * Hooks
     */
    public function hooks(): void {
        \add_filter( 'redipress/schema_fields', function ( $fields ) {
            $fields[] = new TextField( [
                'name'     => 'artists',
                'sortable' => true,
            ] );

            return $fields;
        }, PHP_INT_MAX, 1 );

        \add_filter( 'redipress/additional_field/artists', function ( $data, $post_id, $post ) {
            $value = '';

            if ( $post->post_type === Festival::SLUG ) {
                $value = \get_post_meta( $post_id, 'artists', true );
            }

            return $value;
        }, 10, 3 );
    }
}
