<?php
/**
 * Plugin Name: Holiday Hours
 * Description: Manage holiday hours with custom schedules and display current operating hours
 * Version: 1.0.0
 * Author: Vince
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: holiday-hours
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HOLIDAY_HOURS_VERSION', '1.0.0');
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

        // Initialize admin
        if (is_admin()) {
            $this->admin = new Holiday_Hours_Admin($this->database);
        }

        // Initialize shortcode
        $this->shortcode = new Holiday_Hours_Shortcode($this->database);
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
