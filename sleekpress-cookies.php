<?php
/**
 * Plugin Name:       SleekPress Cookies
 * Plugin URI:        https://sleekpress.com/
 * Description:        Lightweight cookie consent banner with a cookie scanner, AI-assisted categorisation and Google Consent Mode v2 support.
 * Version:           1.1.2
 * Author:            SleekPress
 * License:           GPL-2.0-or-later
 * Text Domain:       sleekpress-cookies
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SPC_VERSION', '1.1.2' );
define( 'SPC_FILE', __FILE__ );
define( 'SPC_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPC_URL', plugin_dir_url( __FILE__ ) );
define( 'SPC_OPT_SETTINGS', 'spc_settings' );
define( 'SPC_OPT_COOKIES', 'spc_cookies' );

require_once SPC_DIR . 'vendor/sleekpress-ui/php/class-sleekpress-ui.php';
require_once SPC_DIR . 'includes/class-spc-cookie-db.php';
require_once SPC_DIR . 'includes/class-spc-settings.php';
require_once SPC_DIR . 'includes/class-spc-ai.php';
require_once SPC_DIR . 'includes/class-spc-scanner.php';
require_once SPC_DIR . 'includes/class-spc-rest.php';
require_once SPC_DIR . 'includes/class-spc-admin.php';
require_once SPC_DIR . 'includes/class-spc-frontend.php';
require_once SPC_DIR . 'includes/class-spc-plugin.php';

register_activation_hook( __FILE__, array( 'SPC_Plugin', 'activate' ) );

add_action( 'plugins_loaded', array( 'SPC_Plugin', 'instance' ) );
