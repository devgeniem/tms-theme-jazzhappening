<?php

use TMS\Theme\Base\Logger;

/**
 * Alter BlogArticles Fields block
 */
class AlterBlogArticlesFields {

    /**
     * Constructor
     */
    public function __construct() {
        \add_filter(
            'tms/acf/layout/_blog_articles/fields',
            [ $this, 'alter_fields' ],
            10,
            2
        );
    }

    /**
     * Alter fields
     *
     * @param array $fields Array of ACF fields.
     *
     * @return array
     */
    public function alter_fields( array $fields ): array {

        $strings = [
            'limit' => [
                'instructions' => 'Valitse väliltä 3-12',
            ],
        ];

        try {
            $fields['number']->set_min( 3 );
            $fields['number']->set_instructions( $strings['limit']['instructions'] );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
        return $fields;
    }
}

( new AlterBlogArticlesFields() );
