<?php

use TMS\Theme\Base\Logger;
use TMS\Theme\Base\Settings;
use TMS\Theme\Base\Formatters\ImageFormatter;
use TMS\Plugin\ManualEvents\PostType\ManualEvent;

/**
 * The SingleManualEventCpt class.
 */
class SingleManualEventCpt extends PageEvent {

    /**
     * Template
     */
    const TEMPLATE = 'single-manual-event-cpt.php';

    /**
     * Hooks
     */
    public function hooks() : void {
        \remove_action(
            'wp_head',
            Closure::fromCallable( [ $this, 'add_json_ld_data' ] )
        );
    }

    /**
     * Hero image URL.
     *
     * @return string|null|false
     */
    public function hero_image() {
        if ( ! \has_post_thumbnail() ) {
            return empty( Settings::get_setting( 'events_default_image' ) )
                ? null
                : \wp_get_attachment_image_url( Settings::get_setting( 'events_default_image' ), 'large' );
        }

        return \get_the_post_thumbnail_url( null, 'large' );
    }

    /**
     * Hero image credits
     *
     * @return string
     */
    public function hero_image_credits() : ?string {
        if ( ! \has_post_thumbnail() ) {
            return Settings::get_setting( 'events_default_image_credits' ) ?? '';
        }

        $image_data = ImageFormatter::get_image_artist( [], [ 'id' => \get_post_thumbnail_id() ] );

        return $image_data['author_name'] ?? '';
    }

    /**
     * Get event id.
     *
     * @return string
     */
    protected function get_event_id() : string {
        return (string) \get_the_ID();
    }

    /**
     * Set view event
     */
    protected function set_event() : void {
        try {
            $fields       = \get_fields();
            $event        = (object) $fields;
            $event->id    = $this->get_event_id();
            $event->title = \get_the_title();
            $event->url   = \get_permalink();
            $event->image = \has_post_thumbnail() ? \get_the_post_thumbnail_url( null, 'large' ) : null;

            if ( ! empty( $event->id ) ) {
                $this->event = $event;
            }
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );

            $this->event = null;
        }
    }

    /**
     * Get event info
     *
     * @return array|null
     */
    public function event() {
        $event = $this->get_event();

        if ( empty( $event ) ) {
            return null;
        }

        $event->recurring_event = false;
        $event->end_datetime    = null;

        $normalized_event          = ManualEvent::normalize_event( $event );
        $event_location            = $normalized_event['location']['name'];
        $weekday_prefix            = \date_i18n( 'D', strtotime( $event->start_datetime ) );
        $normalized_event['price'] = [
            [
                'price'    => $event->price_is_free
                    ? __( 'Free', 'tms-theme-base' )
                    : $event->price['price_price'],
                'info_url' => [
                    'url' => $event->price['price_info_url']['url'] ?? '',
                ],
            ],
        ];

        return [
            'normalized'               => $normalized_event,
            'orig'                     => $event,
            'buy_ticket_string'        => \__( 'Buy ticket', 'tms-theme-jazzhappening' ),
            'time_prefix'              => \__( 'at', 'tms-theme-jazzhappening' ),
            'composition_title'        => \__( 'Composition', 'tms-theme-jazzhappening' ),
            'links_title'              => \__( 'Links', 'tms-theme-jazzhappening' ),
            'weekday_prefix'           => $weekday_prefix,
            'location_price_separator' => $event_location ? ', ' : '',
            'gallery_id'               => \wp_unique_id( 'image-gallery-' ),
            'translations'             => ( new \Strings() )->s()['gallery'] ?? [],
        ];
    }
}
