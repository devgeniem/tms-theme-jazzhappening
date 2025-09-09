<?php

namespace TMS\Theme\Jazzhappening\ACF;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;
use TMS\Theme\Jazzhappening\Taxonomy\ArtistCategory;

/**
 * Class PageArtistGroup
 *
 * @package TMS\Theme\Jazzhappening\ACF
 */
class PageArtistGroup {

    /**
     * PageGroup constructor.
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
                ->set_key( 'fg_page_artist_settings' );

            $rule_group = ( new RuleGroup() )
                ->add_rule( 'page_template', '==', \PageArtist::TEMPLATE );

            $field_group
                ->add_rule_group( $rule_group )
                ->set_position( 'normal' );

            $key = $field_group->get_key();

            $strings = [
                'description'       => [
                    'title'        => 'Kuvaus',
                    'instructions' => '',
                ],
                'artist_categories' => [
                    'title'        => 'Taitelijoiden kategoriat',
                    'instructions' => '',
                ],
                'festivals_page'    => [
                    'title'        => 'Festivaalien listaussivun painike',
                    'instructions' => 'Painike asetetaan suodattimien vierelle.
                    Tyhjäksi jätetyn tekstin sijaan tulee oletusteksti "Selaa ja etsi festivaaleja".',
                ],
            ];

            $description_field = ( new Field\Wysiwyg( $strings['description']['title'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->disable_media_upload()
                ->set_tabs( 'visual' )
                ->set_instructions( $strings['description']['instructions'] );

            $artist_category_field = ( new Field\Taxonomy( $strings['artist_categories']['title'] ) )
                ->set_key( "{$key}_artist_categories" )
                ->set_name( 'artist_categories' )
                ->set_taxonomy( ArtistCategory::SLUG )
                ->set_return_format( 'object' )
                ->allow_null()
                ->set_instructions( $strings['artist_categories']['instructions'] );

            $festivals_page_field = ( new Field\Link( $strings['festivals_page']['title'] ) )
                ->set_key( "{$key}festivals_page" )
                ->set_name( 'festivals_page' )
                ->set_instructions( $strings['festivals_page']['instructions'] );

            $field_group->add_fields(
                apply_filters(
                    'tms/acf/group/' . $field_group->get_key() . '/fields',
                    [
                        $description_field,
                        $artist_category_field,
                        $festivals_page_field,
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
     * Hide components from PageArtist
     *
     * @param array $rules ACF group rules.
     *
     * @return array
     */
    protected function alter_component_rules( array $rules ): array {
        $rules[] = [
            'param'    => 'page_template',
            'operator' => '!=',
            'value'    => \PageArtist::TEMPLATE,
        ];

        return $rules;
    }
}

( new PageArtistGroup() );


