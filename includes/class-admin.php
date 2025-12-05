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
        add_action('wp_ajax_holiday_hours_add_year', array($this, 'ajax_add_year'));
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

        // Add submenu for test date (only shown when test date is enabled)
        if (get_option('holiday_hours_enable_test_date', false)) {
            add_submenu_page(
                'holiday-hours',
                __('Test Date', 'holiday-hours'),
                __('Test Date', 'holiday-hours'),
                'manage_options',
                'holiday-hours-test-date',
                array($this, 'render_test_date_page')
            );
        }
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Register day-specific settings for Monday through Sunday
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

        foreach ($days as $day) {
            register_setting('holiday_hours_settings', 'holiday_hours_' . $day . '_open', array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '6:00 AM'
            ));

            register_setting('holiday_hours_settings', 'holiday_hours_' . $day . '_close', array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '7:00 PM'
            ));

            register_setting('holiday_hours_settings', 'holiday_hours_' . $day . '_closed', array(
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => false
            ));

            register_setting('holiday_hours_settings', 'holiday_hours_' . $day . '_custom_text', array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            ));
        }

        register_setting('holiday_hours_settings', 'holiday_hours_enable_test_date', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        ));

        register_setting('holiday_hours_settings', 'holiday_hours_test_date', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));

        register_setting('holiday_hours_settings', 'holiday_hours_delete_on_uninstall', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
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
        $current_year = $selected_year;

        // Get day-specific settings
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $day_settings = array();
        foreach ($days as $day) {
            $day_settings[$day] = array(
                'open' => get_option('holiday_hours_' . $day . '_open', '6:00 AM'),
                'close' => get_option('holiday_hours_' . $day . '_close', '7:00 PM'),
                'closed' => get_option('holiday_hours_' . $day . '_closed', false),
                'custom_text' => get_option('holiday_hours_' . $day . '_custom_text', '')
            );
        }

        include HOLIDAY_HOURS_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Save settings
     */
    private function save_settings($selected_year) {
        $enable_test_date = isset($_POST['enable_test_date']) ? true : false;
        $delete_on_uninstall = isset($_POST['delete_on_uninstall']) ? true : false;

        // Save day-specific settings
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        foreach ($days as $day) {
            $open_time = isset($_POST[$day . '_open']) ? sanitize_text_field($_POST[$day . '_open']) : '6:00 AM';
            $close_time = isset($_POST[$day . '_close']) ? sanitize_text_field($_POST[$day . '_close']) : '7:00 PM';
            $is_closed = isset($_POST[$day . '_closed']) ? true : false;
            $custom_text = isset($_POST[$day . '_custom_text']) ? sanitize_text_field($_POST[$day . '_custom_text']) : '';

            update_option('holiday_hours_' . $day . '_open', $open_time);
            update_option('holiday_hours_' . $day . '_close', $close_time);
            update_option('holiday_hours_' . $day . '_closed', $is_closed);
            update_option('holiday_hours_' . $day . '_custom_text', $custom_text);
        }

        update_option('holiday_hours_enable_test_date', $enable_test_date);
        update_option('holiday_hours_delete_on_uninstall', $delete_on_uninstall);

        echo '<div class="notice notice-success is-dismissible"><p>' .
             __('Settings saved successfully!', 'holiday-hours') .
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
        $date_to = isset($_POST['date_to']) && !empty($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : null;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'open';
        $open_time = isset($_POST['open_time']) && !empty($_POST['open_time']) ? sanitize_text_field($_POST['open_time']) : null;
        $close_time = isset($_POST['close_time']) && !empty($_POST['close_time']) ? sanitize_text_field($_POST['close_time']) : null;
        $custom_text = isset($_POST['custom_text']) && !empty($_POST['custom_text']) ? sanitize_text_field($_POST['custom_text']) : null;

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

    /**
     * AJAX handler for adding a new year
     */
    public function ajax_add_year() {
        check_ajax_referer('holiday_hours_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'holiday-hours')));
        }

        $year = isset($_POST['year']) ? intval($_POST['year']) : 0;

        if ($year <= 0) {
            wp_send_json_error(array('message' => __('Invalid year', 'holiday-hours')));
        }

        // Store the year in an option so it shows in the dropdown
        $added_years = get_option('holiday_hours_added_years', array());
        if (!in_array($year, $added_years)) {
            $added_years[] = $year;
            update_option('holiday_hours_added_years', $added_years);
        }

        wp_send_json_success(array(
            'redirect_url' => admin_url('admin.php?page=holiday-hours&year=' . $year)
        ));
    }

    /**
     * Render test date page
     */
    public function render_test_date_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if (isset($_POST['holiday_hours_test_date_save']) && check_admin_referer('holiday_hours_test_date_action', 'holiday_hours_test_date_nonce')) {
            $test_date = isset($_POST['test_date']) ? sanitize_text_field($_POST['test_date']) : '';

            update_option('holiday_hours_test_date', $test_date);

            echo '<div class="notice notice-success is-dismissible"><p>' . __('Test date saved successfully!', 'holiday-hours') . '</p></div>';
        }

        $test_date = get_option('holiday_hours_test_date', date('Y-m-d'));

        include HOLIDAY_HOURS_PLUGIN_DIR . 'templates/admin-test-date.php';
    }
}
