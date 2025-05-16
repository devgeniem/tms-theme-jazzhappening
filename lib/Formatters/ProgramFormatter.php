<?php

namespace TMS\Theme\jazzhappening\Formatters;

use TMS\Plugin\ManualEvents\PostType;

/**
 * Class ProgramFormatter
 *
 * @package TMS\Theme\Base\Formatters
 */
class ProgramFormatter implements \TMS\Theme\Base\Interfaces\Formatter {

    /**
     * Define formatter name
     */
    const NAME = 'Program';

    /**
     * Hooks
     */
    public function hooks(): void {
        \add_filter(
            'tms/acf/layout/program/data',
            [ $this, 'format' ],
            30
        );
    }

    /**
     * Format layout data
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function format( array $layout ): array {

        $dayNames = [];

        $layout['repeater'] = array_map( function ( $item ) use ( &$dayNames ) {
            $date        = $item['date'] ?? '';
            $timestamp   = \DateTime::createFromFormat( 'Y-m-d', $date );
            $dayName     = $timestamp ? \date_i18n( 'D', $timestamp->getTimestamp() ) : '';
            $fullDayName = $timestamp ? \date_i18n( 'l', $timestamp->getTimestamp() ) : '';

            // Remove possible duplicates
            if ( $dayName && ! in_array( [ 'day_name' => $dayName, 'full_day_name' => $fullDayName ], $dayNames, true ) ) {
                $dayNames[] = [
                    'day_name'      => $dayName,
                    'full_day_name' => $fullDayName,
                ];
            }

            // Get manual event content by ID
            if ( ! empty( $item['manual_events'] ) ) {
                foreach ( $item['manual_events'] as $event_id ) {
                    $start_datetime = \get_field( 'start_datetime', $event_id );
                    $formatted_time = $start_datetime ? \date_i18n( 'D H:i', strtotime( $start_datetime ) ) : '';

                    $item['events'][] = [
                        'id'       => $event_id,
                        'title'    => \get_the_title( $event_id ),
                        'link'     => \get_the_permalink( $event_id ),
                        'image_id' => \get_post_thumbnail_id( $event_id ),
                        'datetime' => $formatted_time,
                    ];
                }

                $item['location_and_time'] = sprintf( '%s | %s', $item['location'], $item['time'] );
                $item['day_name']          = $dayName;

                // Add class for the masonry layout
                $item['events_class'] = sprintf( 'event-count--%s', count( $item['manual_events'] ) );
            }

            // Format the date field
            if ( $date ) {
                $item['formatted_date'] = $timestamp ? \date_i18n( 'D, d.m.', $timestamp->getTimestamp() ) : '';
            }

            return $item;
        }, $layout['repeater'] );

        $layout['day_names'] = $dayNames;

        return $layout;
    }
}
