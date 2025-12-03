<?php
/**
 * Test Date Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap holiday-hours-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2><?php _e('Test Date Settings', 'holiday-hours'); ?></h2>
        <p class="description">
            <?php _e('Set a test date to simulate how the plugin will behave on different dates. This is useful for testing holiday schedules before they go live.', 'holiday-hours'); ?>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field('holiday_hours_test_date_action', 'holiday_hours_test_date_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="test_date"><?php _e('Test Date', 'holiday-hours'); ?></label>
                    </th>
                    <td>
                        <input type="date"
                               id="test_date"
                               name="test_date"
                               value="<?php echo esc_attr($test_date); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('The plugin will use this date instead of the current date when displaying hours.', 'holiday-hours'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Current Status', 'holiday-hours'); ?>
                    </th>
                    <td>
                        <?php
                        // Get hours for the test date
                        $plugin = HolidayHours::get_instance();
                        $hours = $plugin->get_current_hours($test_date);
                        ?>
                        <div style="background: #f0f0f1; padding: 15px; border-radius: 4px; border-left: 4px solid #2271b1;">
                            <p style="margin: 0 0 10px 0;">
                                <strong><?php _e('Simulated Date:', 'holiday-hours'); ?></strong>
                                <?php echo date('F j, Y', strtotime($test_date)); ?>
                            </p>
                            <?php if ($hours['status'] === 'closed'): ?>
                                <p style="margin: 0; color: #d63638;">
                                    <strong><?php _e('Status:', 'holiday-hours'); ?></strong>
                                    <?php echo esc_html($hours['custom_text']); ?>
                                </p>
                            <?php else: ?>
                                <p style="margin: 0; color: #00a32a;">
                                    <strong><?php _e('Hours:', 'holiday-hours'); ?></strong>
                                    <?php echo esc_html($hours['open_time'] . ' - ' . $hours['close_time']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <p class="description" style="margin-top: 10px;">
                            <?php _e('This shows what will be displayed on your site with the current test date.', 'holiday-hours'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Test Date', 'holiday-hours'), 'primary', 'holiday_hours_test_date_save'); ?>
        </form>

        <hr style="margin: 30px 0;">

        <h3><?php _e('Shortcode Preview', 'holiday-hours'); ?></h3>
        <p><?php _e('Use this shortcode to display hours on your site:', 'holiday-hours'); ?></p>
        <code class="shortcode-example">[holiday_hours]</code>

        <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
            <strong><?php _e('Preview Output:', 'holiday-hours'); ?></strong>
            <div style="margin-top: 10px;">
                <?php echo do_shortcode('[holiday_hours]'); ?>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
            <p style="margin: 0;">
                <strong><?php _e('Note:', 'holiday-hours'); ?></strong>
                <?php _e('Remember to disable test date mode before going live! The test date will affect what all visitors see on your site.', 'holiday-hours'); ?>
            </p>
        </div>
    </div>
</div>
