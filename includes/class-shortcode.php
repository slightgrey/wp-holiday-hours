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
     */
    public function get_current_hours($date = null) {
        if ($date === null) {
            $date = current_time('Y-m-d');
        }

        $default_open = get_option('holiday_hours_default_open', '6:00 AM');
        $default_close = get_option('holiday_hours_default_close', '7:00 PM');

        // Check if current date falls within any holiday range
        $holiday = $this->database->get_schedule_for_date($date);

        if ($holiday) {
            if ($holiday['status'] === 'closed') {
                return array(
                    'status' => 'closed',
                    'text' => !empty($holiday['custom_text']) ? $holiday['custom_text'] : 'Closed'
                );
            } else {
                return array(
                    'status' => 'open',
                    'open_time' => $holiday['open_time'],
                    'close_time' => $holiday['close_time']
                );
            }
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
            'date' => current_time('Y-m-d'),
            'format' => 'default'
        ), $atts);

        $hours = $this->get_current_hours($atts['date']);

        ob_start();

        if ($hours['status'] === 'closed') {
            echo '<div class="holiday-hours closed">';
            echo '<span class="status">' . esc_html($hours['text']) . '</span>';
            echo '</div>';
        } else {
            echo '<div class="holiday-hours open">';
            echo '<span class="hours">' . esc_html($hours['open_time']) . ' - ' . esc_html($hours['close_time']) . '</span>';
            echo '</div>';
        }

        return ob_get_clean();
    }
}
