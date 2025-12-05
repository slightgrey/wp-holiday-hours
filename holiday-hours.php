<?php
/**
 * Plugin Name: Holiday Hours
 * Description: Manage holiday hours with custom schedules and display current operating hours
 * Version: 1.1.0
 * Author: Aspire Web Pty Ltd
 * Author URI: https://aspireweb.com.au/
 * License: GPL v2 or later
 * Text Domain: holiday-hours
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HOLIDAY_HOURS_VERSION', '1.1.0');
define('HOLIDAY_HOURS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HOLIDAY_HOURS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Holiday Hours Plugin Class
 */
class HolidayHours {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Database instance
     */
    private $database;

    /**
     * Admin instance
     */
    private $admin;

    /**
     * Shortcode instance
     */
    private $shortcode;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once HOLIDAY_HOURS_PLUGIN_DIR . 'includes/class-database.php';
        require_once HOLIDAY_HOURS_PLUGIN_DIR . 'includes/class-admin.php';
        require_once HOLIDAY_HOURS_PLUGIN_DIR . 'includes/class-shortcode.php';
    }

    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize database
        $this->database = new Holiday_Hours_Database();

        // Check and create table on admin_init
        add_action('admin_init', array($this->database, 'check_and_create_table'));

        // Run migration on admin_init
        add_action('admin_init', array($this, 'migrate_settings'));

        // Initialize admin
        if (is_admin()) {
            $this->admin = new Holiday_Hours_Admin($this->database);
        }

        // Initialize shortcode
        $this->shortcode = new Holiday_Hours_Shortcode($this->database);
    }

    /**
     * Migrate old settings to new day-specific structure
     */
    public function migrate_settings() {
        // Check if migration has already been done
        if (get_option('holiday_hours_migrated_to_day_specific', false)) {
            return;
        }

        // Check if old settings exist
        $old_default_open = get_option('holiday_hours_default_open');
        $old_default_close = get_option('holiday_hours_default_close');

        // If old settings exist, migrate them
        if ($old_default_open !== false || $old_default_close !== false) {
            $default_open = $old_default_open !== false ? $old_default_open : '6:00 AM';
            $default_close = $old_default_close !== false ? $old_default_close : '7:00 PM';

            // Get old weekend settings
            $saturday_closed = get_option('holiday_hours_saturday_closed', false);
            $sunday_closed = get_option('holiday_hours_sunday_closed', false);

            // Apply old default hours to all days
            $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
            foreach ($days as $day) {
                // Set default hours for each day
                update_option('holiday_hours_' . $day . '_open', $default_open);
                update_option('holiday_hours_' . $day . '_close', $default_close);

                // Apply weekend closed settings
                if ($day === 'saturday' && $saturday_closed) {
                    update_option('holiday_hours_saturday_closed', true);
                    update_option('holiday_hours_saturday_custom_text', 'Closed');
                } elseif ($day === 'sunday' && $sunday_closed) {
                    update_option('holiday_hours_sunday_closed', true);
                    update_option('holiday_hours_sunday_custom_text', 'Closed');
                } else {
                    update_option('holiday_hours_' . $day . '_closed', false);
                    update_option('holiday_hours_' . $day . '_custom_text', '');
                }
            }

            // Delete old settings
            delete_option('holiday_hours_default_open');
            delete_option('holiday_hours_default_close');
            delete_option('holiday_hours_saturday_closed');
            delete_option('holiday_hours_sunday_closed');
        } else {
            // No old settings exist, initialize new settings with defaults
            $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
            foreach ($days as $day) {
                if (get_option('holiday_hours_' . $day . '_open') === false) {
                    update_option('holiday_hours_' . $day . '_open', '6:00 AM');
                    update_option('holiday_hours_' . $day . '_close', '7:00 PM');
                    update_option('holiday_hours_' . $day . '_closed', false);
                    update_option('holiday_hours_' . $day . '_custom_text', '');
                }
            }
        }

        // Mark migration as complete
        update_option('holiday_hours_migrated_to_day_specific', true);
    }

    /**
     * Get database instance
     */
    public function get_database() {
        return $this->database;
    }

    /**
     * Get shortcode instance (for backwards compatibility)
     */
    public function get_current_hours($date = null) {
        if ($this->shortcode) {
            return $this->shortcode->get_current_hours($date);
        }
        return array(
            'status' => 'open',
            'open_time' => '6:00 AM',
            'close_time' => '7:00 PM'
        );
    }
}

/**
 * Initialize the plugin
 */
function holiday_hours_init() {
    return HolidayHours::get_instance();
}
add_action('plugins_loaded', 'holiday_hours_init');

/**
 * Get current hours (helper function for theme developers)
 */
function get_holiday_hours($date = null) {
    $plugin = HolidayHours::get_instance();
    return $plugin->get_current_hours($date);
}
