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
    }

    /**
     * Get current hours for a date
     * Priority order:
     * 1. Single-day custom entries (can override anything including weekends)
     * 2. Weekend closed settings (overrides date ranges)
     * 3. Date ranges with custom hours
     * 4. Default hours
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

        $default_open = get_option('holiday_hours_default_open', '6:00 AM');
        $default_close = get_option('holiday_hours_default_close', '7:00 PM');

        // Check if current date falls within any holiday range
        $holiday = $this->database->get_schedule_for_date($date);

        // Check if it's a weekend day
        $day_of_week = date('w', strtotime($date)); // 0 = Sunday, 6 = Saturday
        $saturday_closed = get_option('holiday_hours_saturday_closed', false);
        $sunday_closed = get_option('holiday_hours_sunday_closed', false);
        $is_closed_weekend = ($day_of_week == 6 && $saturday_closed) || ($day_of_week == 0 && $sunday_closed);

        if ($holiday) {
            // Check if this is a single-day entry (highest priority)
            $is_single_day = ($holiday['date_to'] === null && $holiday['date_from'] === $date);

            if ($holiday['status'] === 'closed') {
                return array(
                    'status' => 'closed',
                    'custom_text' => !empty($holiday['custom_text']) ? $holiday['custom_text'] : 'Closed'
                );
            } else {
                // If it's a date range (not single day) and it's a weekend that should be closed,
                // weekend setting takes priority
                if (!$is_single_day && $is_closed_weekend) {
                    return array(
                        'status' => 'closed',
                        'custom_text' => 'Closed'
                    );
                }

                return array(
                    'status' => 'open',
                    'open_time' => $holiday['open_time'],
                    'close_time' => $holiday['close_time']
                );
            }
        }

        // Check if it's a weekend day that should be closed
        if ($is_closed_weekend) {
            return array(
                'status' => 'closed',
                'custom_text' => 'Closed'
            );
        }

        // Return default hours
        return array(
            'status' => 'open',
            'open_time' => $default_open,
            'close_time' => $default_close
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
}
