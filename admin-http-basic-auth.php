<?php
/**
 * Plugin Name: Admin HTTP Basic Auth
 * Plugin URI: https://hugocms.net/admin-http-basic-auth
 * Description: Add HTTP Basic Authentication to /wp-admin/, wp-login.php, REST API, and PHP files with whitelist functionality.
 * Version: 2.2.0
 * Author: Chris Peng
 * Author URI: https://hugocms.net
 * License: GPL-2.0+
 * Text Domain: admin-http-basic-auth
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Admin_HTTP_Basic_Auth {
    /**
     * Plugin instance
     *
     * @var Admin_HTTP_Basic_Auth
     */
    private static $instance;

    /**
     * Get plugin instance (singleton pattern)
     *
     * @return Admin_HTTP_Basic_Auth
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Check authentication before loading admin area or login page
        add_action('init', array($this, 'check_authentication'), 1);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'HTTP Basic Auth Settings',
            'HTTP Basic Auth',
            'manage_options',
            'admin-http-basic-auth',
            array($this, 'settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('admin_http_basic_auth', 'admin_http_basic_auth_enabled');
        register_setting('admin_http_basic_auth', 'admin_http_basic_auth_username');
        register_setting('admin_http_basic_auth', 'admin_http_basic_auth_password');
        register_setting('admin_http_basic_auth', 'admin_http_basic_auth_protect_login');
        register_setting('admin_http_basic_auth', 'admin_http_basic_auth_protect_php');
        register_setting('admin_http_basic_auth', 'admin_http_basic_auth_protect_rest');
        register_setting('admin_http_basic_auth', 'admin_http_basic_auth_whitelist'); // New: Whitelist setting
    }

    /**
     * Settings page HTML
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>HTTP Basic Auth Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('admin_http_basic_auth');
                do_settings_sections('admin_http_basic_auth');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Authentication</th>
                        <td>
                            <input type="checkbox" name="admin_http_basic_auth_enabled" value="1" <?php checked(get_option('admin_http_basic_auth_enabled'), 1); ?> />
                            <label>Enable HTTP Basic Authentication for /wp-admin/</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Protect Login Page</th>
                        <td>
                            <input type="checkbox" name="admin_http_basic_auth_protect_login" value="1" <?php checked(get_option('admin_http_basic_auth_protect_login'), 1); ?> />
                            <label>Enable HTTP Basic Authentication for wp-login.php</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Protect REST API</th>
                        <td>
                            <input type="checkbox" name="admin_http_basic_auth_protect_rest" value="1" <?php checked(get_option('admin_http_basic_auth_protect_rest'), 1); ?> />
                            <label>Enable HTTP Basic Authentication for WordPress REST API</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Protect PHP Files</th>
                        <td>
                            <input type="checkbox" name="admin_http_basic_auth_protect_php" value="1" <?php checked(get_option('admin_http_basic_auth_protect_php'), 1); ?> />
                            <label>Enable HTTP Basic Authentication for all PHP files</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Whitelisted Files</th>
                        <td>
                            <textarea name="admin_http_basic_auth_whitelist" rows="5" cols="50" class="regular-text"><?php echo esc_textarea(get_option('admin_http_basic_auth_whitelist')); ?></textarea>
                            <p class="description">Enter filenames to whitelist (one per line), e.g. abc.php, litespeed.php. These files will bypass authentication.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Username</th>
                        <td>
                            <input type="text" name="admin_http_basic_auth_username" value="<?php echo esc_attr(get_option('admin_http_basic_auth_username')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Password</th>
                        <td>
                            <input type="password" name="admin_http_basic_auth_password" value="<?php echo esc_attr(get_option('admin_http_basic_auth_password')); ?>" class="regular-text" />
                            <p class="description">Password is stored in plain text. Please use a strong password and ensure your server is secure.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Check HTTP Basic Authentication
     */
    public function check_authentication() {
        // Skip check if authentication is not enabled
        if (!get_option('admin_http_basic_auth_enabled')) {
            return;
        }

        // Get configured username and password
        $username = get_option('admin_http_basic_auth_username');
        $password = get_option('admin_http_basic_auth_password');

        // Skip check if username or password is empty
        if (empty($username) || empty($password)) {
            return;
        }

        // Check if current file is in whitelist
        $whitelist = get_option('admin_http_basic_auth_whitelist');
        if (!empty($whitelist)) {
            // Get current filename from request URI
            $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $current_filename = basename($request_path);
            
            // Process whitelist - split into lines, trim, and remove empty entries
            $whitelisted_files = array_filter(array_map('trim', explode("\n", $whitelist)));
            
            // If current filename is in whitelist, skip authentication
            if (in_array($current_filename, $whitelisted_files)) {
                return;
            }
        }

        // Check if it's admin area or login page
        $is_admin = is_admin();
        $is_login = in_array($GLOBALS['pagenow'], array('wp-login.php'));
        
        // Check if it's a REST API request
        $is_rest = defined('REST_REQUEST') && REST_REQUEST;
        
        // Check if it's a PHP file request (case-insensitive)
        $is_php = false;
        if (get_option('admin_http_basic_auth_protect_php')) {
            $request_uri = strtolower($_SERVER['REQUEST_URI']);
            $is_php = (strpos($request_uri, '.php') !== false);
        }
        
        // Perform authentication check if:
        // - Login page protection is enabled and current page is login page
        // - Current page is admin area
        // - REST API protection is enabled and current request is for REST API
        // - PHP file protection is enabled and current request is for a PHP file
        if (($is_login && get_option('admin_http_basic_auth_protect_login')) || $is_admin || 
            ($is_rest && get_option('admin_http_basic_auth_protect_rest')) || $is_php) {
            
            // Check if already authenticated
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
                $_SERVER['PHP_AUTH_USER'] !== $username || $_SERVER['PHP_AUTH_PW'] !== $password) {
                header('WWW-Authenticate: Basic realm="WordPress Admin Access"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Access denied. Please enter correct username and password.';
                exit;
            }
        }
    }
}

// Initialize plugin
Admin_HTTP_Basic_Auth::get_instance();
