<?php

namespace TMS\Theme\Jazzhappening\ACF;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;
use TMS\Theme\Jazzhappening\Taxonomy\FestivalCategory;

/**
 * Class PageFestivalGroup
 *
 * @package TMS\Theme\Jazzhappening\ACF
 */
class PageFestivalGroup {

    /**
     * PageFestivalGroup constructor.
     */
    public function __construct() {
        \add_action(
            'init',
            Closure::fromCallable( [ $this, 'register_fields' ] )
        );

        \add_filter(
            'tms/acf/group/fg_page_components/rules',
            Closure::fromCallable( [ $this, 'alter_component_rules' ] )
        );
    }

    /**
     * Register fields
     */
    protected function register_fields(): void {
        try {
            $group_title = 'Arkiston asetukset';

            $field_group = ( new Group( $group_title ) )
                ->set_key( 'fg_page_festival_settings' );

            $rule_group = ( new RuleGroup() )
                ->add_rule( 'page_template', '==', \PageFestival::TEMPLATE );

            $field_group
                ->add_rule_group( $rule_group )
                ->set_position( 'normal' );

            $key = $field_group->get_key();

            $strings = [
                'description'         => [
                    'title'        => 'Kuvaus',
                    'instructions' => '',
                ],
                'festival_categories' => [
                    'title'        => 'Festivaalien kategoriat',
                    'instructions' => '',
                ],
            ];

            $description_field = ( new Field\Wysiwyg( $strings['description']['title'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->disable_media_upload()
                ->set_tabs( 'visual' )
                ->set_instructions( $strings['description']['instructions'] );

            $festival_categories_field = ( new Field\Taxonomy( $strings['festival_categories']['title'] ) )
                ->set_key( "{$key}_festival_categories" )
                ->set_name( 'festival_categories' )
                ->set_taxonomy( FestivalCategory::SLUG )
                ->set_return_format( 'object' )
                ->allow_null()
                ->set_instructions( $strings['festival_categories']['instructions'] );

            $field_group->add_fields(
                \apply_filters(
                    'tms/acf/group/' . $field_group->get_key() . '/fields',
                    [
                        $description_field,
                        $festival_categories_field,
                    ]
                )
            );

            $field_group = \apply_filters(
                'tms/acf/group/' . $field_group->get_key(),
                $field_group
            );

            $field_group->register();
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }
    }

    /**
     * Hide components from PageFestival
     *
     * @param array $rules ACF group rules.
     *
     * @return array
     */
    protected function alter_component_rules( array $rules ): array {
        $rules[] = [
            'param'    => 'page_template',
            'operator' => '!=',
            'value'    => \PageFestival::TEMPLATE,
        ];

        return $rules;
    }
}

( new PageFestivalGroup() );
