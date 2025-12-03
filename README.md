# Holiday Hours WordPress Plugin

A WordPress plugin for managing holiday hours with custom schedules and displaying current operating hours on your website.

## Description

Holiday Hours allows you to manage your business operating hours during holidays and special occasions. Set custom hours for specific dates or date ranges, and display them anywhere on your site using a simple shortcode.

## Features

- **Default Operating Hours**: Set default business hours that display when no holiday schedule is active
- **Holiday Schedules**: Create custom schedules for specific dates or date ranges
- **Year Management**: Easily switch between years and manage schedules for multiple years
- **Flexible Status Options**:
  - Open with custom hours
  - Closed with custom message
- **Test Date Mode**: Test how your schedules will display on specific dates
- **Easy Display**: Use the `[holiday_hours]` shortcode to display current hours anywhere on your site
- **Clean Uninstall**: Optional data deletion when uninstalling the plugin

## Installation

1. Upload the `holiday-hours` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Holiday Hours** in the WordPress admin menu to configure settings

## Usage

### Admin Configuration

1. Navigate to **Holiday Hours** in the WordPress admin menu
2. Set your default operating hours
3. Select a year from the dropdown to manage schedules
4. Click **Add Holiday Schedule** to create a new schedule:
   - Choose a date range (or single date)
   - Select status: Open with custom hours or Closed with custom message
   - Enter the appropriate hours or message
   - Save the schedule

### Shortcode Usage

Display current hours on any page, post, or widget:

```
[holiday_hours]
```

Display hours for a specific date:

```
[holiday_hours date="2025-12-25"]
```

### Developer Functions

For theme developers, you can access holiday hours programmatically:

```php
$hours = get_holiday_hours();
// Returns array: ['status' => 'open', 'open_time' => '6:00 AM', 'close_time' => '7:00 PM']

// Get hours for specific date
$hours = get_holiday_hours('2025-12-25');
```

## Settings

### Default Operating Hours
- **Default Open Time**: The normal opening time for your business
- **Default Close Time**: The normal closing time for your business

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

**Current Version**: 1.0.0

## Author

**Vince**

## License

This plugin is licensed under the GPL v2 or later.

## Support

For bug reports, feature requests, or questions, please create an issue in the repository.

## Changelog

### 1.0.0
- Initial release
- Default operating hours management
- Holiday schedule creation and management
- Year-based organization
- Test date mode
- Shortcode support
- Clean uninstall option
