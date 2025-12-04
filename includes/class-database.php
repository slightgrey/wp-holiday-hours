<?php
/**
 * Database operations for Holiday Hours
 *
 * @package HolidayHours
 */

if (!defined('ABSPATH')) {
    exit;
}

class Holiday_Hours_Database {

    /**
     * Table name
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'holiday_hours';
    }

    /**
     * Get table name
     */
    public function get_table_name() {
        return $this->table_name;
    }

    /**
     * Check if table exists and create if needed
     */
    public function check_and_create_table() {
        global $wpdb;

        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            $this->create_table();
        }
    }

    /**
     * Create database table
     */
    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            date_from date NOT NULL,
            date_to date DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'open',
            open_time varchar(20) DEFAULT NULL,
            close_time varchar(20) DEFAULT NULL,
            custom_text varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY date_from (date_from),
            KEY date_to (date_to)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $this->migrate_old_data();
    }

    /**
     * Migrate data from wp_options to database
     */
    private function migrate_old_data() {
        global $wpdb;

        if (get_option('holiday_hours_migrated', false)) {
            return;
        }

        $old_data = get_option('holiday_hours_data', array());

        if (!empty($old_data) && is_array($old_data)) {
            foreach ($old_data as $holiday) {
                if (empty($holiday['date_from'])) {
                    continue;
                }

                $this->insert_schedule(
                    $holiday['date_from'],
                    !empty($holiday['date_to']) ? $holiday['date_to'] : null,
                    isset($holiday['status']) ? $holiday['status'] : 'open',
                    isset($holiday['open_time']) ? $holiday['open_time'] : null,
                    isset($holiday['close_time']) ? $holiday['close_time'] : null,
                    isset($holiday['custom_text']) ? $holiday['custom_text'] : null
                );
            }
        }

        update_option('holiday_hours_migrated', true);
    }

    /**
     * Insert a holiday schedule
     */
    public function insert_schedule($date_from, $date_to, $status, $open_time, $close_time, $custom_text) {
        global $wpdb;

        return $wpdb->insert(
            $this->table_name,
            array(
                'date_from' => $date_from,
                'date_to' => $date_to,
                'status' => $status,
                'open_time' => $open_time,
                'close_time' => $close_time,
                'custom_text' => $custom_text
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Get schedules for a specific year
     */
    public function get_schedules_by_year($year) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE YEAR(date_from) = %d ORDER BY date_from ASC",
            $year
        ), ARRAY_A);
    }

    /**
     * Delete schedules for a specific year
     */
    public function delete_schedules_by_year($year) {
        global $wpdb;

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE YEAR(date_from) = %d",
            $year
        ));
    }

    /**
     * Get available years with schedules
     */
    public function get_available_years() {
        global $wpdb;

        // Get years from database
        $years = $wpdb->get_col("SELECT DISTINCT YEAR(date_from) FROM {$this->table_name} ORDER BY YEAR(date_from) DESC");

        // Get manually added years
        $added_years = get_option('holiday_hours_added_years', array());

        // Merge and deduplicate
        $all_years = array_unique(array_merge($years, $added_years));

        // Sort descending
        rsort($all_years);

        if (empty($all_years)) {
            return array(date('Y'));
        }

        return $all_years;
    }

    /**
     * Get schedule for a specific date
     * Priority order:
     * 1. Single-day entries (date_to IS NULL AND date_from = target_date)
     * 2. Date ranges (sorted by narrowest range first)
     */
    public function get_schedule_for_date($date) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name}
            WHERE date_from <= %s
            AND (date_to >= %s OR (date_to IS NULL AND date_from = %s))
            ORDER BY
                CASE
                    WHEN date_to IS NULL AND date_from = %s THEN 0
                    ELSE 1
                END,
                COALESCE(DATEDIFF(date_to, date_from), 0) ASC,
                date_from DESC
            LIMIT 1",
            $date,
            $date,
            $date,
            $date
        ), ARRAY_A);
    }

    /**
     * Check if a date is being overridden by other schedules
     * Returns the overriding schedule if found, or false otherwise
     */
    public function get_override_info($schedule_id, $date_from, $date_to) {
        global $wpdb;

        // If this is a date range, check if any single-day entries fall within it
        if (!empty($date_to) && $date_to !== $date_from) {
            $overrides = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$this->table_name}
                WHERE id != %d
                AND date_from >= %s
                AND date_from <= %s
                AND (date_to IS NULL OR date_to = date_from)
                ORDER BY date_from ASC",
                $schedule_id,
                $date_from,
                $date_to
            ), ARRAY_A);

            return $overrides;
        }

        return array();
    }
}
