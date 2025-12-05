# Holiday Hours WordPress Plugin

A WordPress plugin for managing holiday hours with custom schedules and displaying current operating hours on your website.

## Description

Holiday Hours allows you to manage your business operating hours during holidays and special occasions. Set custom hours for specific dates or date ranges, and display them anywhere on your site using a simple shortcode.

## Features

- **Day-Specific Operating Hours**: Set unique default hours for each day of the week (Monday-Sunday)
- **Flexible Day Settings**: Each day can be configured as:
  - Open with specific hours (e.g., "9:00 AM - 5:00 PM")
  - Closed with custom message (e.g., "Closed on Sundays")
- **Holiday Schedules**: Create custom schedules for specific dates or date ranges that override default hours
- **Year Management**: Easily switch between years and manage schedules for multiple years
- **Priority-Based Display**: Holiday schedules take precedence over default day-specific hours
- **Test Date Mode**: Test how your schedules will display on specific dates
- **Easy Display**: Use the `[holiday_hours]` shortcode to display current hours anywhere on your site
- **Weekly Schedule Display**: Use the `[open_times]` shortcode to display a table of all default operating hours for the week
- **Clean Uninstall**: Optional data deletion when uninstalling the plugin
- **Automatic Migration**: Seamlessly upgrades from single default hours to day-specific settings

## Installation

1. Upload the `holiday-hours` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Holiday Hours** in the WordPress admin menu to configure settings

## Usage

### Admin Configuration

#### Setting Default Operating Hours

1. Navigate to **Holiday Hours** in the WordPress admin menu
2. Configure hours for each day of the week:
   - **For Open Days**: Enter opening and closing times (e.g., "9:00 AM" and "5:00 PM")
   - **For Closed Days**: Check the "Closed" checkbox and enter a custom message (e.g., "Closed on Sundays")
3. Click **Save Settings**

#### Managing Holiday Schedules

1. Select a year from the dropdown
2. Click **Add Holiday Schedule** to create a special schedule:
   - Choose a date range (or single date)
   - Select status: Open with custom hours or Closed with custom message
   - Enter the appropriate hours or message
   - Save the schedule

**Note**: Holiday schedules override default day-specific hours. For example, if you're normally closed on Sundays but open for a special event, create a holiday schedule for that Sunday.

### Shortcode Usage

#### Display Current Hours

Display current hours on any page, post, or widget:

```
[holiday_hours]
```

Display hours for a specific date:

```
[holiday_hours date="2025-12-25"]
```

#### Display Weekly Operating Hours

Display a table showing all default operating hours for the week:

```
[open_times]
```

This will display a table with all seven days of the week (Mon-Sun) and their corresponding operating hours from your default settings. Days marked as closed will show "Closed" or your custom closed message.

Example output:
```
Mon: 6:00 AM - 5:00 PM
Tue: 6:00 AM - 5:00 PM
Wed: 6:00 AM - 5:00 PM
Thu: 6:00 AM - 5:00 PM
Fri: 6:00 AM - 5:00 PM
Sat: 9:00 AM - 2:00 PM
Sun: Closed
```

You can also add a custom CSS class to the table:

```
[open_times class="my-custom-class"]
```

The table has the CSS class `open-times-table` and each row contains `day-label` and `day-hours` classes for styling.

### Developer Functions

For theme developers, you can access holiday hours programmatically:

```php
// Get current hours (checks today's day of week and any active holiday schedules)
$hours = get_holiday_hours();
// Returns array:
// ['status' => 'open', 'open_time' => '9:00 AM', 'close_time' => '5:00 PM']
// or
// ['status' => 'closed', 'custom_text' => 'Closed on Sundays']

// Get hours for specific date
$hours = get_holiday_hours('2025-12-25');

// Display example
if ($hours['status'] === 'open') {
    echo 'Open today: ' . $hours['open_time'] . ' - ' . $hours['close_time'];
} else {
    echo $hours['custom_text'];
}
```

## Settings

### Default Operating Hours

Configure individual settings for each day of the week (Monday through Sunday):

- **Day Hours**: Set unique opening and closing times for each day
- **Closed Days**: Mark specific days as closed and provide a custom message
- **Examples**:
  - Monday-Friday: 9:00 AM - 5:00 PM
  - Saturday: 10:00 AM - 2:00 PM
  - Sunday: Closed with message "Closed on Sundays"

### How Hours Are Determined

The plugin uses the following priority order:

1. **Holiday Schedules** (Highest Priority)
   - If a holiday schedule exists for the date, it overrides everything

2. **Day-Specific Default Hours** (Fallback)
   - If no holiday schedule, uses the default hours configured for that day of the week

**Example Scenarios**:
- Regular Monday with no holiday → Shows Monday's default hours
- December 25 with "Closed for Christmas" schedule → Shows holiday message
- Sunday normally closed, but special event scheduled → Shows holiday schedule hours

### Test Date Mode
- Enable test date mode to preview how schedules will display on specific dates
- Accessible via **Holiday Hours > Test Date** when enabled

### Uninstall Options
- **Delete Data on Uninstall**: Choose whether to remove all plugin data when uninstalling

## File Structure

```
holiday-hours/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   ├── class-admin.php      # Admin interface and functionality
│   ├── class-database.php   # Database operations
│   └── class-shortcode.php  # Shortcode rendering
├── templates/
│   ├── admin-settings.php   # Main settings page template
│   ├── admin-test-date.php  # Test date page template
│   └── repeater-row.php     # Holiday list item template
├── holiday-hours.php        # Main plugin file
└── uninstall.php           # Cleanup on uninstall
```

## Database

The plugin creates a custom table `wp_holiday_hours` to store holiday schedules with the following structure:

- `id`: Unique identifier
- `date_from`: Start date of the holiday schedule
- `date_to`: End date (nullable for single-day schedules)
- `status`: Either 'open' or 'closed'
- `open_time`: Opening time (for open status)
- `close_time`: Closing time (for open status)
- `custom_text`: Custom message (for closed status)
- `created_at`: Timestamp of creation

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Version

**Current Version**: 1.2.0

## Author

**Aspire Web Pty Ltd**
Website: [https://aspireweb.com.au/](https://aspireweb.com.au/)

## License

This plugin is licensed under the GPL v2 or later.

## Support

For bug reports, feature requests, or questions, please create an issue in the repository.

## Changelog

### 1.2.0
- **NEW**: `[open_times]` shortcode to display a table of all weekly operating hours
- **NEW**: Abbreviated day labels (Mon, Tue, Wed, etc.) in the weekly schedule display
- **NEW**: Custom CSS class support for the open times table
- **IMPROVED**: Enhanced documentation with shortcode usage examples

### 1.1.0
- **NEW**: Day-specific operating hours (Monday-Sunday)
- **NEW**: Individual open/close settings for each day of the week
- **NEW**: Custom closed messages for specific days
- **IMPROVED**: Automatic migration from single default hours to day-specific settings
- **IMPROVED**: Enhanced admin interface with day-by-day configuration
- **CHANGED**: Removed global weekend settings in favor of individual day control

### 1.0.0
- Initial release
- Default operating hours management
- Holiday schedule creation and management
- Year-based organization
- Test date mode
- Shortcode support
- Clean uninstall option
