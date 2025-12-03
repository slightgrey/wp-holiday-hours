<?php
/**
 * Admin functionality for Holiday Hours
 *
 * @package HolidayHours
 */

if (!defined('ABSPATH')) {
    exit;
}

class Holiday_Hours_Admin {

    /**
     * Database instance
     */
    private $database;

    /**
     * Constructor
     */
    public function __construct($database) {
        $this->database = $database;

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // AJAX handlers
        add_action('wp_ajax_holiday_hours_save', array($this, 'ajax_save_holiday'));
        add_action('wp_ajax_holiday_hours_delete', array($this, 'ajax_delete_holiday'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Holiday Hours', 'holiday-hours'),
            __('Holiday Hours', 'holiday-hours'),
            'manage_options',
            'holiday-hours',
            array($this, 'render_settings_page'),
            'dashicons-clock',
            30
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('holiday_hours_settings', 'holiday_hours_default_open', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '6:00 AM'
        ));

        register_setting('holiday_hours_settings', 'holiday_hours_default_close', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '7:00 PM'
        ));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'holiday-hours') {
            return;
        }

        wp_enqueue_style(
            'holiday-hours-admin',
            HOLIDAY_HOURS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            HOLIDAY_HOURS_VERSION
        );

        wp_enqueue_script(
            'holiday-hours-admin',
            HOLIDAY_HOURS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            HOLIDAY_HOURS_VERSION,
            false
        );

        wp_localize_script(
            'holiday-hours-admin',
            'holidayHoursAjax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('holiday_hours_ajax_nonce')
            )
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        // Handle form submission
        if (isset($_POST['holiday_hours_save']) && check_admin_referer('holiday_hours_save_action', 'holiday_hours_nonce')) {
            $this->save_settings($selected_year);
        }

        // Get data for display
        $holiday_data = $this->database->get_schedules_by_year($selected_year);
        $available_years = $this->database->get_available_years();
        $default_open = get_option('holiday_hours_default_open', '6:00 AM');
        $default_close = get_option('holiday_hours_default_close', '7:00 PM');
        $current_year = $selected_year;

        include HOLIDAY_HOURS_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Save settings
     */
    private function save_settings($selected_year) {
        $default_open = isset($_POST['default_open']) ? sanitize_text_field($_POST['default_open']) : '6:00 AM';
        $default_close = isset($_POST['default_close']) ? sanitize_text_field($_POST['default_close']) : '7:00 PM';

        // Save default hours
        update_option('holiday_hours_default_open', $default_open);
        update_option('holiday_hours_default_close', $default_close);

        // Delete existing schedules for this year
        $this->database->delete_schedules_by_year($selected_year);

        // Get holiday data from JSON field
        $holiday_json = isset($_POST['holiday_hours_json']) ? $_POST['holiday_hours_json'] : '';
        $holiday_data = json_decode(stripslashes($holiday_json), true);

        // Save new schedules
        $saved_count = 0;
        if (!empty($holiday_data) && is_array($holiday_data)) {
            foreach ($holiday_data as $holiday) {
                if (empty($holiday['date_from'])) {
                    continue;
                }

                $result = $this->database->insert_schedule(
                    sanitize_text_field($holiday['date_from']),
                    !empty($holiday['date_to']) ? sanitize_text_field($holiday['date_to']) : null,
                    sanitize_text_field($holiday['status']),
                    !empty($holiday['open_time']) ? sanitize_text_field($holiday['open_time']) : null,
                    !empty($holiday['close_time']) ? sanitize_text_field($holiday['close_time']) : null,
                    !empty($holiday['custom_text']) ? sanitize_text_field($holiday['custom_text']) : null
                );

                if ($result !== false) {
                    $saved_count++;
                }
            }
        }

        echo '<div class="notice notice-success is-dismissible"><p>' .
             sprintf(__('Settings saved successfully! %d holiday schedule(s) saved.', 'holiday-hours'), $saved_count) .
             '</p></div>';
    }

    /**
     * AJAX handler for saving a holiday
     */
    public function ajax_save_holiday() {
        check_ajax_referer('holiday_hours_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'holiday-hours')));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : null;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'open';
        $open_time = isset($_POST['open_time']) ? sanitize_text_field($_POST['open_time']) : null;
        $close_time = isset($_POST['close_time']) ? sanitize_text_field($_POST['close_time']) : null;
        $custom_text = isset($_POST['custom_text']) ? sanitize_text_field($_POST['custom_text']) : null;

        if (empty($date_from)) {
            wp_send_json_error(array('message' => __('Date is required', 'holiday-hours')));
        }

        if ($id > 0) {
            // Update existing - delete old and insert new
            global $wpdb;
            $table = $wpdb->prefix . 'holiday_hours';
            $wpdb->delete($table, array('id' => $id), array('%d'));
        }

        $result = $this->database->insert_schedule(
            $date_from,
            $date_to,
            $status,
            $open_time,
            $close_time,
            $custom_text
        );

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Holiday schedule saved successfully', 'holiday-hours')
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save holiday schedule', 'holiday-hours')));
        }
    }

    /**
     * AJAX handler for deleting a holiday
     */
    public function ajax_delete_holiday() {
        check_ajax_referer('holiday_hours_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'holiday-hours')));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            wp_send_json_error(array('message' => __('Invalid ID', 'holiday-hours')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'holiday_hours';
        $result = $wpdb->delete($table, array('id' => $id), array('%d'));

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Holiday schedule deleted successfully', 'holiday-hours')
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete holiday schedule', 'holiday-hours')));
        }
    }
}
