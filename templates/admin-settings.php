<?php
/**
 * Admin Settings Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap holiday-hours-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Year Selector -->
    <div class="year-selector" style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;">
        <label for="year-select" style="font-weight: 600; margin-right: 10px;"><?php _e('Select Year:', 'holiday-hours'); ?></label>
        <select id="year-select" onchange="window.location.href='<?php echo admin_url('admin.php?page=holiday-hours&year='); ?>' + this.value;">
            <?php foreach ($available_years as $year): ?>
                <option value="<?php echo esc_attr($year); ?>" <?php selected($current_year, $year); ?>>
                    <?php echo esc_html($year); ?>
                </option>
            <?php endforeach; ?>
            <?php if (!in_array(date('Y'), $available_years)): ?>
                <option value="<?php echo date('Y'); ?>" <?php selected($current_year, date('Y')); ?>>
                    <?php echo date('Y'); ?> (<?php _e('Current Year', 'holiday-hours'); ?>)
                </option>
            <?php endif; ?>
        </select>
        <button type="button" class="button" id="add-next-year-btn">
            <?php _e('+ Add Next Year', 'holiday-hours'); ?>
        </button>

    <!-- Two Column Layout -->
    <div class="holiday-hours-layout">

        <!-- Left Column: Settings -->
        <div class="holiday-hours-left">
            <form method="post" action="<?php echo admin_url('admin.php?page=holiday-hours&year=' . $current_year); ?>" id="holiday-hours-form">
                <?php wp_nonce_field('holiday_hours_save_action', 'holiday_hours_nonce'); ?>

                <!-- Hidden field to store holiday data -->
                <input type="hidden" name="holiday_hours_json" id="holiday-hours-json" value="">

                <!-- Default Hours Section -->
                <div class="card">
                    <h2><?php _e('Default Operating Hours', 'holiday-hours'); ?></h2>
                    <p class="description"><?php _e('These hours will be displayed when there are no holiday schedules active.', 'holiday-hours'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="default_open"><?php _e('Default Open Time', 'holiday-hours'); ?></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="default_open"
                                       name="default_open"
                                       value="<?php echo esc_attr($default_open); ?>"
                                       class="regular-text"
                                       placeholder="e.g., 6:00 AM">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="default_close"><?php _e('Default Close Time', 'holiday-hours'); ?></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="default_close"
                                       name="default_close"
                                       value="<?php echo esc_attr($default_close); ?>"
                                       class="regular-text"
                                       placeholder="e.g., 7:00 PM">
                            </td>
                        </tr>
                    </table>

                    <h3><?php _e('Weekend Hours', 'holiday-hours'); ?></h3>
                    <p class="description"><?php _e('Configure whether your business is closed on weekends. If checked, the business will show as closed on these days.', 'holiday-hours'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="saturday_closed"><?php _e('Saturday', 'holiday-hours'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           id="saturday_closed"
                                           name="saturday_closed"
                                           value="1"
                                           <?php checked(get_option('holiday_hours_saturday_closed', false), true); ?>>
                                    <?php _e('Closed on Saturdays', 'holiday-hours'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sunday_closed"><?php _e('Sunday', 'holiday-hours'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           id="sunday_closed"
                                           name="sunday_closed"
                                           value="1"
                                           <?php checked(get_option('holiday_hours_sunday_closed', false), true); ?>>
                                    <?php _e('Closed on Sundays', 'holiday-hours'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Test Date Section -->
                <div class="card">
                    <h2><?php _e('Test Date Mode', 'holiday-hours'); ?></h2>
                    <p class="description"><?php _e('Enable this setting to test the plugin with different dates. A "Test Date" submenu will appear where you can set a simulated date.', 'holiday-hours'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="enable_test_date"><?php _e('Enable Test Date', 'holiday-hours'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           id="enable_test_date"
                                           name="enable_test_date"
                                           value="1"
                                           <?php checked(get_option('holiday_hours_enable_test_date', false), true); ?>>
                                    <?php _e('Enable test date mode to simulate different dates on your site', 'holiday-hours'); ?>
                                </label>
                                <p class="description"><?php _e('When enabled, the plugin will use the test date instead of the current date. This allows you to preview how holiday schedules will display on specific dates.', 'holiday-hours'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Delete Data on Uninstall Section -->
                <div class="card">
                    <h2><?php _e('Uninstall Options', 'holiday-hours'); ?></h2>
                    <p class="description"><?php _e('Configure what happens when you uninstall this plugin.', 'holiday-hours'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="delete_on_uninstall"><?php _e('Delete Data on Uninstall', 'holiday-hours'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           id="delete_on_uninstall"
                                           name="delete_on_uninstall"
                                           value="1"
                                           <?php checked(get_option('holiday_hours_delete_on_uninstall', false), true); ?>>
                                    <?php _e('Delete all plugin data when the plugin is uninstalled', 'holiday-hours'); ?>
                                </label>
                                <p class="description"><?php _e('When enabled, all holiday schedules, settings, and database tables will be permanently deleted when you uninstall this plugin. This action cannot be undone.', 'holiday-hours'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Shortcode Information -->
                <div class="card">
                    <h2><?php _e('How to Use', 'holiday-hours'); ?></h2>
                    <p><?php _e('Use the following shortcode to display current hours on your site:', 'holiday-hours'); ?></p>
                    <code class="shortcode-example">[holiday_hours]</code>

                    <h3><?php _e('Optional Parameters:', 'holiday-hours'); ?></h3>
                    <ul>
                        <li><code>[holiday_hours date="2025-12-25"]</code> - Display hours for a specific date</li>
                    </ul>
                </div>

                <?php submit_button(__('Save Settings', 'holiday-hours'), 'primary', 'holiday_hours_save'); ?>
            </form>
        </div>

        <!-- Right Column: Holiday List -->
        <div class="holiday-hours-right">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0;"><?php printf(__('Holiday Hours for %s', 'holiday-hours'), $current_year); ?></h2>
                    <button type="button" class="button button-primary" id="add-holiday-btn">
                        <span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span>
                        <?php _e('Add Holiday Schedule', 'holiday-hours'); ?>
                    </button>
                </div>

                <div id="holiday-list">
                    <?php if (empty($holiday_data)): ?>
                        <div class="empty-state">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <p><?php _e('No holiday schedules yet.', 'holiday-hours'); ?></p>
                            <p><?php _e('Click "Add Holiday Schedule" to get started.', 'holiday-hours'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($holiday_data as $holiday): ?>
                            <div class="holiday-list-item" data-holiday-id="<?php echo esc_attr($holiday['id']); ?>">
                                <div class="holiday-item-info">
                                    <div class="holiday-item-date">
                                        <?php
                                        $date_from = date('M j, Y', strtotime($holiday['date_from']));
                                        $date_to = !empty($holiday['date_to']) ? date('M j, Y', strtotime($holiday['date_to'])) : '';
                                        echo esc_html($date_from);
                                        if ($date_to && $date_to !== $date_from) {
                                            echo ' - ' . esc_html($date_to);
                                        }
                                        ?>
                                    </div>
                                    <div class="holiday-item-status status-<?php echo esc_attr($holiday['status']); ?>">
                                        <?php if ($holiday['status'] === 'closed'): ?>
                                            <strong><?php _e('Closed:', 'holiday-hours'); ?></strong> <?php echo esc_html($holiday['custom_text']); ?>
                                        <?php else: ?>
                                            <strong><?php _e('Open:', 'holiday-hours'); ?></strong> <?php echo esc_html($holiday['open_time'] . ' - ' . $holiday['close_time']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    // Check for overrides
                                    $overrides = $this->database->get_override_info(
                                        $holiday['id'],
                                        $holiday['date_from'],
                                        $holiday['date_to']
                                    );
                                    if (!empty($overrides)):
                                    ?>
                                        <div class="holiday-item-override-notice">
                                            <span class="dashicons dashicons-info"></span>
                                            <span>
                                                <?php
                                                $override_dates = array();
                                                foreach ($overrides as $override) {
                                                    $override_dates[] = date('M j', strtotime($override['date_from']));
                                                }
                                                printf(
                                                    _n(
                                                        'Overridden on %s',
                                                        'Overridden on %s',
                                                        count($overrides),
                                                        'holiday-hours'
                                                    ),
                                                    implode(', ', $override_dates)
                                                );
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="holiday-item-actions">
                                    <button type="button" class="button edit-holiday-btn" data-holiday-id="<?php echo esc_attr($holiday['id']); ?>">
                                        <?php _e('Edit', 'holiday-hours'); ?>
                                    </button>
                                    <button type="button" class="button delete-holiday-btn" data-holiday-id="<?php echo esc_attr($holiday['id']); ?>">
                                        <?php _e('Delete', 'holiday-hours'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add/Edit Holiday Modal -->
<div id="holiday-modal" class="holiday-modal">
    <div class="holiday-modal-content">
        <div class="holiday-modal-header">
            <h2 id="modal-title"><?php _e('Add Holiday Schedule', 'holiday-hours'); ?></h2>
            <button class="holiday-modal-close">&times;</button>
        </div>
        <div class="holiday-modal-body">
            <input type="hidden" id="modal-holiday-id" value="">

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label><?php _e('Date Range', 'holiday-hours'); ?></label>
                    </th>
                    <td>
                        <div class="date-range-wrapper">
                            <label>
                                <?php _e('From:', 'holiday-hours'); ?>
                                <input type="date" id="modal-date-from" class="date-input">
                            </label>
                            <label>
                                <?php _e('To:', 'holiday-hours'); ?>
                                <input type="date" id="modal-date-to" class="date-input">
                            </label>
                        </div>
                        <p class="description"><?php _e('Leave "To" empty for single day.', 'holiday-hours'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php _e('Status', 'holiday-hours'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="radio" name="modal-status" value="open" class="modal-status-radio" checked>
                            <?php _e('Open with custom hours', 'holiday-hours'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="modal-status" value="closed" class="modal-status-radio">
                            <?php _e('Closed with custom message', 'holiday-hours'); ?>
                        </label>
                    </td>
                </tr>

                <tr class="modal-hours-fields">
                    <th scope="row">
                        <label><?php _e('Hours', 'holiday-hours'); ?></label>
                    </th>
                    <td>
                        <div class="time-range-wrapper">
                            <label>
                                <?php _e('Open:', 'holiday-hours'); ?>
                                <input type="text" id="modal-open-time" class="time-input" placeholder="e.g., 6:00 AM">
                            </label>
                            <label>
                                <?php _e('Close:', 'holiday-hours'); ?>
                                <input type="text" id="modal-close-time" class="time-input" placeholder="e.g., 4:00 PM">
                            </label>
                        </div>
                    </td>
                </tr>

                <tr class="modal-custom-text-field" style="display: none;">
                    <th scope="row">
                        <label><?php _e('Custom Message', 'holiday-hours'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="modal-custom-text" class="regular-text" placeholder="e.g., Closed for Christmas">
                        <p class="description"><?php _e('This message will be displayed instead of hours.', 'holiday-hours'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="holiday-modal-footer">
            <button type="button" class="button" id="modal-cancel-btn"><?php _e('Cancel', 'holiday-hours'); ?></button>
            <button type="button" class="button button-primary" id="modal-save-btn"><?php _e('Save Holiday', 'holiday-hours'); ?></button>
        </div>
    </div>
</div>

<script>
// Store holiday data in JavaScript
var holidayData = <?php echo json_encode($holiday_data); ?>;
var currentYear = <?php echo json_encode($current_year); ?>;
</script>
