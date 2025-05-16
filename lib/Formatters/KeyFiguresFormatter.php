<?php

namespace TMS\Theme\jazzhappening\Formatters;

/**
 * Class KeyFiguresFormatter
 *
 * @package TMS\Theme\Base\Formatters
 */
class KeyFiguresFormatter implements \TMS\Theme\Base\Interfaces\Formatter {

    /**
     * Define formatter name
     */
    const NAME = 'KeyFigures';

    /**
     * Hooks
     */
    public function hooks(): void {
        \add_filter(
            'tms/acf/layout/key_figures/data',
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
        $layouts = [
            '50-50' => [
                'is-6-desktop',
                'is-6-desktop',
            ],
            '70-30' => [
                'is-6-desktop is-8-widescreen',
                'is-6-desktop is-4-widescreen',
            ],
            '30-70' => [
                'is-6-desktop is-4-widescreen',
                'is-6-desktop is-8-widescreen',
            ],
        ];

        $altered = $layout;
        $chars   = 0;

        foreach ( $altered['rows'] as $row => $row_data ) {
            $row_layout = $row_data['layout'];

            if ( empty( $row_data['numbers'] ) ) {
                continue;
            }

            foreach ( $row_data['numbers'] as $number => $numbers_data ) {
                $altered['rows'][ $row ]['numbers'][ $number ]['column_class'] = $layouts[ $row_layout ][ $number ];

                // Count the chars in the number field to determine the longest number in the component
                $num_len = strlen( $numbers_data['number'] );
                $chars   = $chars < $num_len ? $num_len : $chars;
            }
        }

        // Set the number text class according to the length of the number field
        if ( $chars <= 4 ) {
            $altered['num_class'] = 'is-text-huge';
        }
        elseif ( $chars <= 6 ) {
            $altered['num_class'] = 'is-text-bigger';
        }
        elseif ( $chars <= 10 ) {
            $altered['num_class'] = 'is-text-big';
        }

        return $altered;
    }
}
