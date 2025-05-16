<?php

namespace TMS\Theme\jazzhappening\ACF\Layouts;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Flexible\Layout;
use TMS\Theme\Base\Logger;

/**
 * Class HeroLayout
 *
 * @package TMS\Theme\jazzhappening\ACF\Layouts
 */
class HeroLayout extends Layout {

    /**
     * Layout key
     */
    const KEY = '_hero';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( string $key ) {
        parent::__construct(
            'Hero',
            $key . self::KEY,
            'hero'
        );

        $this->add_layout_fields();
    }

    /**
     * Add layout fields.
     *
     * @return void
     * @throws Exception In case of invalid option.
     */
    public function add_layout_fields(): void {
        $key = $this->get_key();

        $strings = [
            'carousel_repeater' => [
                'label'        => 'Kuvakaruselli',
                'instructions' => '',
            ],
            'slide_image'       => [
                'label'        => 'Kuva',
                'instructions' => '',
            ],
            'slide_title'       => [
                'label'        => 'Otsikko',
                'instructions' => '',
            ],
            'slide_subtitle'       => [
                'label'        => 'Alaotsikko',
                'instructions' => '',
            ],
            'slide_description'       => [
                'label'        => 'Kuvausteksti',
                'instructions' => '',
            ],
            'main_title'       => [
                'label'        => 'Sivun pääotsikko',
                'instructions' => 'Käytössä ruudunlukijoilla, ei näytetä sivustolla',
            ],
            'description' => [
                'label'        => 'Kuvaus',
                'instructions' => '',
            ],
            'link'        => [
                'label'        => 'Painike',
                'instructions' => '',
            ],
        ];


        $carousel_repeater_field = ( new Field\Repeater( $strings['carousel_repeater']['label'] ) )
            ->set_key( "{$key}_carousel_repeater" )
            ->set_name( 'carousel_repeater' )
            ->set_layout( 'block' )
            ->set_instructions( $strings['carousel_repeater']['instructions'] );

        $slide_image_field = ( new Field\Image( $strings['slide_image']['label'] ) )
            ->set_key( "{$key}_slide_image" )
            ->set_name( 'slide_image' )
            ->set_instructions( $strings['slide_image']['instructions'] );

        $slide_title_field = ( new Field\Text( $strings['slide_title']['label'] ) )
            ->set_key( "{$key}_slide_title" )
            ->set_name( 'slide_title' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['slide_title']['instructions'] );

        $slide_subtitle_field = ( new Field\Text( $strings['slide_subtitle']['label'] ) )
            ->set_key( "{$key}_slide_subtitle" )
            ->set_name( 'slide_subtitle' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['slide_subtitle']['instructions'] );

        $slide_description_field = ( new Field\Textarea( $strings['slide_description']['label'] ) )
            ->set_key( "{$key}_slide_description" )
            ->set_name( 'slide_description' )
            ->set_rows( 4 )
            ->set_new_lines( 'wpautop' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['slide_description']['instructions'] );

        $slide_link_field = ( new Field\Link( $strings['link']['label'] ) )
            ->set_key( "{$key}_slide_link" )
            ->set_name( 'slide_link' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['link']['instructions'] );

        $carousel_repeater_field->add_fields( [
            $slide_image_field,
            $slide_title_field,
            $slide_subtitle_field,
            $slide_description_field,
            $slide_link_field,
        ] );

        $main_title_field = ( new Field\Text( $strings['main_title']['label'] ) )
            ->set_key( "{$key}_main_title" )
            ->set_name( 'main_title' )
            ->set_wrapper_width( 50 )
            ->set_required()
            ->set_instructions( $strings['main_title']['instructions'] );

        try {
            $this->add_fields(
                \apply_filters(
                    'tms/acf/layout/hero--jazzhappening/fields',
                    [
                        $carousel_repeater_field,
                        $main_title_field,
                    ],
                    $key
                )
            );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
