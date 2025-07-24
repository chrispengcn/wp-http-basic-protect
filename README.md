# Admin HTTP Basic Auth Plugin

A WordPress plugin that adds an extra layer of security by implementing HTTP Basic Authentication for the WordPress admin area, login page, REST API, and PHP files.

## Description

Admin HTTP Basic Auth enhances your WordPress site's security by requiring additional HTTP Basic Authentication before accessing sensitive areas. This plugin allows you to protect:

- WordPress admin area (/wp-admin/)
- wp-login.php page
- WordPress REST API endpoints
- All PHP files on your site

This creates an additional security barrier beyond your regular WordPress login credentials, helping prevent unauthorized access even if your WordPress credentials are compromised.

## Features

- Enable/disable HTTP Basic Authentication globally
- Selectively protect specific areas (admin, login, REST API, PHP files)
- Custom username and password for HTTP Basic Authentication
- Easy-to-use settings page in WordPress admin
- Lightweight and performance-friendly

## Installation

1. Upload the `admin-http-basic-auth` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'Settings' > 'HTTP Basic Auth' to configure the plugin

## Configuration

1. After activation, go to Settings > HTTP Basic Auth in your WordPress admin
2. Check "Enable HTTP Basic Authentication for /wp-admin/" to activate the plugin
3. Select additional areas to protect (login page, REST API, PHP files)
4. Set a strong username and password (different from your WordPress credentials)
5. Click "Save Changes"

## Usage

Once configured, when accessing any protected area, you'll first be prompted for the HTTP Basic Authentication credentials you set. After entering these successfully, you'll then proceed to the regular WordPress login (if applicable).

## Notes

- The HTTP Basic Authentication credentials are stored in plain text in the WordPress database. Ensure your server is properly secured.
- Use a strong, unique password different from your WordPress admin password
- If you forget your HTTP Basic Authentication credentials, you can disable the plugin by renaming the plugin folder via FTP to regain access

## Changelog

### 2.1.2
- Initial release

## Frequently Asked Questions

### Q: What happens if I forget my HTTP Basic Auth credentials?
A: You can rename the plugin folder via FTP to disable the plugin and regain access to your site.

### Q: Can I use this with other security plugins?
A: Yes, this plugin should work with most security plugins, but always test compatibility in a staging environment.

### Q: Will this affect my site's performance?
A: The performance impact is minimal as the authentication check is lightweight.

### Q: Does this work with WordPress Multisite?
A: This plugin is designed for single-site WordPress installations. Compatibility with Multisite is untested.

## License

This plugin is licensed under the GPL-2.0+ license.

## Author

Chris Peng  
[https://hugocms.net](https://hugocms.net)
