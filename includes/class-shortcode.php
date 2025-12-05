<?php
/**
 * Shortcode functionality for Holiday Hours
 *
 * @package HolidayHours
 */

if (!defined('ABSPATH')) {
    exit;
}

class Holiday_Hours_Shortcode {

    /**
     * Database instance
     */
    private $database;

    /**
     * Constructor
     */
    public function __construct($database) {
        $this->database = $database;

        add_shortcode('holiday_hours', array($this, 'render_shortcode'));
        add_shortcode('open_times', array($this, 'render_open_times'));
    }

    /**
     * Get current hours for a date
     * Priority order:
     * 1. Single-day custom entries (can override anything)
     * 2. Date ranges with custom hours
     * 3. Day-specific default hours (including closed days)
     */
    public function get_current_hours($date = null) {
        if ($date === null) {
            // Check if test date mode is enabled
            $enable_test_date = get_option('holiday_hours_enable_test_date', false);
            if ($enable_test_date) {
                $test_date = get_option('holiday_hours_test_date', '');
                if (!empty($test_date)) {
                    $date = $test_date;
                } else {
                    $date = current_time('Y-m-d');
                }
            } else {
                $date = current_time('Y-m-d');
            }
        }

        // Get day of week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
        $day_of_week = date('w', strtotime($date));

        // Map day of week number to day name
        $day_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        $day_name = $day_names[$day_of_week];

        // Get day-specific default settings
        $day_open = get_option('holiday_hours_' . $day_name . '_open', '6:00 AM');
        $day_close = get_option('holiday_hours_' . $day_name . '_close', '7:00 PM');
        $day_closed = get_option('holiday_hours_' . $day_name . '_closed', false);
        $day_custom_text = get_option('holiday_hours_' . $day_name . '_custom_text', '');

        // Check if current date falls within any holiday range
        $holiday = $this->database->get_schedule_for_date($date);

        if ($holiday) {
            if ($holiday['status'] === 'closed') {
                return array(
                    'status' => 'closed',
                    'custom_text' => !empty($holiday['custom_text']) ? $holiday['custom_text'] : 'Closed'
                );
            } else {
                return array(
                    'status' => 'open',
                    'open_time' => $holiday['open_time'],
                    'close_time' => $holiday['close_time']
                );
            }
        }

        // Return day-specific default hours
        if ($day_closed) {
            return array(
                'status' => 'closed',
                'custom_text' => !empty($day_custom_text) ? $day_custom_text : 'Closed'
            );
        }

        return array(
            'status' => 'open',
            'open_time' => $day_open,
            'close_time' => $day_close
        );
    }

    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'date' => null,
            'format' => 'default'
        ), $atts);

        $hours = $this->get_current_hours($atts['date']);

        ob_start();

        if ($hours['status'] === 'closed') {
            echo '<div class="holiday-hours closed">';
            echo '<span class="status">' . esc_html($hours['custom_text']) . '</span>';
            echo '</div>';
        } else {
            echo '<div class="holiday-hours open">';
            echo '<span class="hours">' . esc_html($hours['open_time']) . ' - ' . esc_html($hours['close_time']) . '</span>';
            echo '</div>';
        }

        return ob_get_clean();
    }

    /**
     * Render open times table shortcode
     */
    public function render_open_times($atts) {
        $atts = shortcode_atts(array(
            'class' => ''
        ), $atts);

        $days = array(
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun'
        );

        ob_start();

        $table_class = 'open-times-table';
        if (!empty($atts['class'])) {
            $table_class .= ' ' . esc_attr($atts['class']);
        }

        echo '<table class="' . $table_class . '">';
        echo '<tbody>';

        foreach ($days as $day_key => $day_label) {
            $day_open = get_option('holiday_hours_' . $day_key . '_open', '6:00 AM');
            $day_close = get_option('holiday_hours_' . $day_key . '_close', '7:00 PM');
            $day_closed = get_option('holiday_hours_' . $day_key . '_closed', false);
            $day_custom_text = get_option('holiday_hours_' . $day_key . '_custom_text', '');

            echo '<tr>';
            echo '<td class="day-label"><strong>' . esc_html($day_label) . '</strong></td>';
            echo '<td class="day-hours">';

            if ($day_closed) {
                echo esc_html(!empty($day_custom_text) ? $day_custom_text : 'Closed');
            } else {
                echo esc_html($day_open . ' - ' . $day_close);
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        return ob_get_clean();
    }
}
