<?php

namespace TMS\Theme\Jazzhappening\ACF;

use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;
use TMS\Theme\Base\Settings;
use TMS\Theme\Jazzhappening\PostType;

/**
 * Class ArtistGroup
 *
 * @package TMS\Theme\Jazzhappening\ACF
 */
class ArtistGroup {

    /**
     * ArtistGroup constructor.
     */
    public function __construct() {
        \add_action(
            'init',
            \Closure::fromCallable( [ $this, 'register_fields' ] )
        );

        \add_filter(
            'acf/load_value/name=additional_information',
            [ $this, 'prefill_additional_info' ],
            10,
            2
        );
    }

    /**
     * Register fields
     */
    protected function register_fields(): void {
        try {
            $field_group = ( new Group( 'Artistin lisätiedot' ) )
                ->set_key( 'fg_artist_fields' );

            $rule_group = ( new RuleGroup() )
                ->add_rule( 'post_type', '==', PostType\Artist::SLUG );

            $field_group
                ->add_rule_group( $rule_group )
                ->set_position( 'normal' );

            $field_group->add_fields(
                \apply_filters(
                    'tms/acf/group/' . $field_group->get_key() . '/fields',
                    [
                        $this->get_details_tab( $field_group->get_key() ),
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
     * Get details tab
     *
     * @param string $key Field group key.
     *
     * @return Field\Tab
     * @throws Exception In case of invalid option.
     */
    protected function get_details_tab( string $key ): Field\Tab {
        $strings = [
            'tab'                    => 'Lisätiedot',
            'short_description'      => [
                'title'        => 'Lyhyt kuvaus',
                'instructions' => 'Lyhyt kuvausteksti, tämä näytetään myös festivaalin sivulla',
            ],
            'date'                   => [
                'title'        => 'Päivämäärä',
                'instructions' => 'Syötä esityksen päivämäärä',
            ],
            'additional_information' => [
                'title'        => 'Lisätiedot',
                'instructions' => '',
                'button'       => 'Lisää rivi',
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

        $short_description_field = ( new Field\Textarea( $strings['short_description']['title'] ) )
            ->set_key( "{$key}_short_description" )
            ->set_name( 'short_description' )
            ->redipress_include_search()
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['short_description']['instructions'] );

        $date_field = ( new Field\DatePicker( $strings['date']['title'] ) )
            ->set_key( "{$key}_date" )
            ->set_name( 'date' )
            ->set_wrapper_width( 50 )
            ->set_return_format( 'd.m.Y' )
            ->set_instructions( $strings['date']['instructions'] );

        $additional_info_repeater = ( new Field\Repeater( $strings['additional_information']['title'] ) )
            ->set_key( "{$key}_additional_information" )
            ->set_name( 'additional_information' )
            ->set_layout( 'block' )
            ->set_button_label( $strings['additional_information']['button'] );

        $additional_info_title = ( new Field\Text( $strings['additional_information']['item']['label']['title'] ) )
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

        $additional_info_repeater->add_fields( [ $additional_info_title, $additional_info_text ] );

        $tab->add_fields( [
            $short_description_field,
            $date_field,
            $additional_info_repeater,
        ] );

        return $tab;
    }

    /**
     * Prefill additional information
     *
     * @param mixed $value   Field value.
     * @param int   $post_id \WP_Post ID.
     *
     * @return array|array[]|mixed
     */
    public function prefill_additional_info( $value, $post_id ) {
        if ( ! empty( $value ) || PostType\Artist::SLUG !== \get_post_type( $post_id ) ) {
            return $value;
        }

        $titles = Settings::get_setting(
            'artist_additional_info',
            DPT_PLL_ACTIVE
                ? \pll_get_post_language( $post_id )
                : \get_locale()
        );

        if ( empty( $titles ) ) {
            return $value;
        }

        return array_map( function ( $item ) {
            return [
                'fg_artist_fields_additional_information_title' => $item['artist_additional_info_text'],
            ];
        }, $titles );
    }
}

( new ArtistGroup() );
