<?php
/**
 * Uninstall Holiday Hours Plugin
 *
 * This file runs when the plugin is uninstalled (deleted).
 * It will only delete data if the user has enabled the "Delete data on uninstall" option.
 *
 * @package HolidayHours
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user wants to delete data on uninstall
$delete_on_uninstall = get_option('holiday_hours_delete_on_uninstall', false);

if ($delete_on_uninstall) {
    global $wpdb;

    // Delete database table
    $table_name = $wpdb->prefix . 'holiday_hours';
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

    // Delete all plugin options
    delete_option('holiday_hours_default_open');
    delete_option('holiday_hours_default_close');
    delete_option('holiday_hours_enable_test_date');
    delete_option('holiday_hours_test_date');
    delete_option('holiday_hours_delete_on_uninstall');
    delete_option('holiday_hours_added_years');
    delete_option('holiday_hours_saturday_closed');
    delete_option('holiday_hours_sunday_closed');

    // For multisite installations, delete options from all sites
    if (is_multisite()) {
        $sites = get_sites();
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);

            $table_name = $wpdb->prefix . 'holiday_hours';
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

            delete_option('holiday_hours_default_open');
            delete_option('holiday_hours_default_close');
            delete_option('holiday_hours_enable_test_date');
            delete_option('holiday_hours_test_date');
            delete_option('holiday_hours_delete_on_uninstall');
            delete_option('holiday_hours_added_years');
            delete_option('holiday_hours_saturday_closed');
            delete_option('holiday_hours_sunday_closed');

            restore_current_blog();
        }
    }
}
