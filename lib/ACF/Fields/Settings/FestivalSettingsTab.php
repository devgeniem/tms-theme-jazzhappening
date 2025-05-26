<?php

namespace TMS\Theme\Jazzhappening\ACF\Fields\Settings;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Tab;
use TMS\Theme\Base\Logger;
use TMS\Theme\Base\PostType;

/**
 * Class FestivalSettingsTab
 *
 * @package TMS\Theme\Jazzhappening\ACF\Tab
 */
class FestivalSettingsTab extends Tab {

    /**
     * Where should the tab switcher be located
     *
     * @var string
     */
    protected $placement = 'left';

    /**
     * Tab strings.
     *
     * @var array
     */
    protected $strings = [
        'tab'                           => 'Festivaalit',
        'festival_additional_info'      => [
            'title'        => 'Esitäytetyt lisätiedot',
            'instructions' => '',
        ],
        'festival_additional_info_text' => [
            'title'        => 'Lisätiedon teksti',
            'instructions' => '',
        ],
        'festival_archive_page'          => [
            'title'        => 'Festivaalien arkistosivu',
            'instructions' => '',
        ],
    ];

    /**
     * The constructor for tab.
     *
     * @param string $label Label.
     * @param null   $key   Key.
     * @param null   $name  Name.
     */
    public function __construct( $label = '', $key = null, $name = null ) { // phpcs:ignore
        $label = $this->strings['tab'];

        parent::__construct( $label );

        $this->sub_fields( $key );
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public function sub_fields( $key ) {
        $strings = $this->strings;

        try {
            $festival_list_page_field = ( new Field\PostObject( $strings['festival_archive_page']['title'] ) )
                ->set_key( "{$key}_festival_archive_page" )
                ->set_name( 'festival_archive_page' )
                ->set_post_types( [ PostType\Page::SLUG ] )
                ->set_return_format( 'id' )
                ->set_instructions( $strings['festival_archive_page']['instructions'] );

            $this->add_fields( [
                $festival_list_page_field,
            ] );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
