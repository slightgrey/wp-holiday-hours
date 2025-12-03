<?php
/**
 * Repeater Row Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="holiday-row" data-index="<?php echo esc_attr($index); ?>">
    <div class="holiday-row-header">
        <h3><?php _e('Holiday Schedule', 'holiday-hours'); ?> #<span class="row-number"><?php echo is_numeric($index) ? esc_html($index + 1) : '{{INDEX_PLUS_1}}'; ?></span></h3>
        <button type="button" class="button button-link-delete remove-holiday-row"><?php _e('Remove', 'holiday-hours'); ?></button>
    </div>

    <div class="holiday-row-content">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php _e('Date Range', 'holiday-hours'); ?></label>
                </th>
                <td>
                    <div class="date-range-wrapper">
                        <label>
                            <?php _e('From:', 'holiday-hours'); ?>
                            <input type="date"
                                   name="holiday_hours[<?php echo esc_attr($index); ?>][date_from]"
                                   value="<?php echo esc_attr($holiday['date_from']); ?>"
                                   class="date-input">
                        </label>
                        <label>
                            <?php _e('To:', 'holiday-hours'); ?>
                            <input type="date"
                                   name="holiday_hours[<?php echo esc_attr($index); ?>][date_to]"
                                   value="<?php echo esc_attr($holiday['date_to']); ?>"
                                   class="date-input">
                        </label>
                    </div>
                    <p class="description"><?php _e('Leave "To" empty for single day. Dates will include both start and end dates.', 'holiday-hours'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label><?php _e('Status', 'holiday-hours'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="radio"
                               name="holiday_hours[<?php echo esc_attr($index); ?>][status]"
                               value="open"
                               class="status-radio"
                               <?php checked($holiday['status'], 'open'); ?>>
                        <?php _e('Open with custom hours', 'holiday-hours'); ?>
                    </label>
                    <br>
                    <label>
                        <input type="radio"
                               name="holiday_hours[<?php echo esc_attr($index); ?>][status]"
                               value="closed"
                               class="status-radio"
                               <?php checked($holiday['status'], 'closed'); ?>>
                        <?php _e('Closed with custom message', 'holiday-hours'); ?>
                    </label>
                </td>
            </tr>

            <tr class="hours-fields" style="<?php echo $holiday['status'] === 'closed' ? 'display: none;' : ''; ?>">
                <th scope="row">
                    <label><?php _e('Hours', 'holiday-hours'); ?></label>
                </th>
                <td>
                    <div class="time-range-wrapper">
                        <label>
                            <?php _e('Open:', 'holiday-hours'); ?>
                            <input type="text"
                                   name="holiday_hours[<?php echo esc_attr($index); ?>][open_time]"
                                   value="<?php echo esc_attr($holiday['open_time']); ?>"
                                   class="time-input"
                                   placeholder="e.g., 6:00 AM">
                        </label>
                        <label>
                            <?php _e('Close:', 'holiday-hours'); ?>
                            <input type="text"
                                   name="holiday_hours[<?php echo esc_attr($index); ?>][close_time]"
                                   value="<?php echo esc_attr($holiday['close_time']); ?>"
                                   class="time-input"
                                   placeholder="e.g., 4:00 PM">
                        </label>
                    </div>
                </td>
            </tr>

            <tr class="custom-text-field" style="<?php echo $holiday['status'] === 'open' ? 'display: none;' : ''; ?>">
                <th scope="row">
                    <label><?php _e('Custom Message', 'holiday-hours'); ?></label>
                </th>
                <td>
                    <input type="text"
                           name="holiday_hours[<?php echo esc_attr($index); ?>][custom_text]"
                           value="<?php echo esc_attr($holiday['custom_text']); ?>"
                           class="regular-text"
                           placeholder="e.g., Closed for Christmas, Resume Tomorrow">
                    <p class="description"><?php _e('This message will be displayed instead of hours.', 'holiday-hours'); ?></p>
                </td>
            </tr>
        </table>
    </div>
</div>
