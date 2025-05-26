<?php

namespace TMS\Theme\Jazzhappening\ACF;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;
use TMS\Theme\Base\Settings;
use TMS\Theme\Jazzhappening\PostType;

/**
 * Class FestivalGroup
 *
 * @package TMS\Theme\Jazzhappening\ACF
 */
class FestivalGroup {

    /**
     * FestivalGroup constructor.
     */
    public function __construct() {
        \add_action(
            'init',
            Closure::fromCallable( [ $this, 'register_fields' ] )
        );
    }

    /**
     * Register fields
     */
    protected function register_fields(): void {
        try {
            $field_group = ( new Group( 'Festivaalin lisätiedot' ) )
                ->set_key( 'fg_festival_fields' );

            $rule_group = ( new RuleGroup() )
                ->add_rule( 'post_type', '==', PostType\Festival::SLUG );

            $field_group
                ->add_rule_group( $rule_group )
                ->set_position( 'normal' );

            $field_group->add_fields(
                \apply_filters(
                    'tms/acf/group/' . $field_group->get_key() . '/fields',
                    [
                        $this->get_details_tab( $field_group->get_key() ),
                        $this->get_artists_tab( $field_group->get_key() ),
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
     * Get additional info tab
     *
     * @param string $key Field group key.
     *
     * @return Field\Tab
     * @throws Exception In case of invalid option.
     */
    protected function get_details_tab( string $key ): Field\Tab {
        $strings = [
            'tab'                    => 'Lisätiedot',
            'images'                 => [
                'title'        => 'Kuvat',
                'instructions' => '',
            ],
            'year'                   => [
                'title'        => 'Vuosi',
                'instructions' => '',
            ],
            'additional_information' => [
                'title'        => 'Lisätiedot',
                'instructions' => '',
                'button'       => 'Lisää rivi',
                'group'        => [
                    'title'        => 'Otsikko',
                    'instructions' => '',
                ],
                'item'         => [
                    'label' => [
                        'title'        => 'Otsikko',
                        'instructions' => '',
                    ],
                    'value' => [
                        'title'        => 'Teksti',
                        'instructions' => '',
                    ],
                ],
            ],
        ];

        $tab = ( new Field\Tab( $strings['tab'] ) )
            ->set_placement( 'left' );

        $images_field = ( new Field\Gallery( $strings['images']['title'] ) )
            ->set_key( "{$key}_images" )
            ->set_name( 'images' )
            ->set_instructions( $strings['images']['instructions'] );

        $year_field = ( new Field\Text( $strings['year']['title'] ) )
            ->set_key( "{$key}_year" )
            ->set_name( 'year' )
            ->set_instructions( $strings['year']['instructions'] );

        $additional_info_repeater = ( new Field\Repeater( $strings['additional_information']['title'] ) )
            ->set_key( "{$key}_additional_information" )
            ->set_name( 'additional_information' )
            ->set_layout( 'block' )
            ->set_button_label( $strings['additional_information']['button'] );

        $additional_info_title = ( new Field\Text(
            $strings['additional_information']['item']['label']['title']
        ) )
            ->set_key( "{$key}_additional_information_title" )
            ->set_name( 'additional_information_title' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['additional_information']['item']['label']['instructions'] );

        $additional_info_text = ( new Field\Textarea( $strings['additional_information']['item']['value']['title'] ) )
            ->set_key( "{$key}_additional_information_text" )
            ->set_name( 'additional_information_text' )
            ->set_new_lines( 'br' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['additional_information']['item']['value']['instructions'] );

        $additional_info_repeater->add_fields( [
            $additional_info_title,
            $additional_info_text,
        ] );

        $tab->add_fields( [
            $year_field,
            $images_field,
            $additional_info_repeater,
        ] );

        return $tab;
    }

    /**
     * Get artists tab
     *
     * @param string $key Field group key.
     *
     * @return Field\Tab
     * @throws Exception In case of invalid option.
     */
    protected function get_artists_tab( string $key ): Field\Tab {
        $strings = [
            'tab'     => 'Artistit',
            'artists' => [
                'title'        => 'Artistit',
                'instructions' => '',
            ],
        ];

        $tab = ( new Field\Tab( $strings['tab'] ) )
            ->set_placement( 'left' );

        $artists_field = ( new Field\Relationship( $strings['artists']['title'] ) )
            ->set_key( "{$key}_artists" )
            ->set_name( 'artists' )
            ->set_post_types( [ PostType\Artist::SLUG ] )
            ->set_filters( [ 'search' ] )
            ->set_instructions( $strings['artists']['instructions'] );

        $tab->add_fields( [
            $artists_field,
        ] );

        return $tab;
    }
}

( new FestivalGroup() );
