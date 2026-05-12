<?php
/**
 * Main plugin bootstrap.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Plugin {

	/** @var SPC_Plugin */
	private static $instance = null;

	/** @var SPC_Admin */
	public $admin;

	/** @var SPC_Frontend */
	public $frontend;

	/** @var SPC_Rest */
	public $rest;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->admin    = new SPC_Admin();
		$this->frontend = new SPC_Frontend();
		$this->rest     = new SPC_Rest();

		load_plugin_textdomain( 'sleekpress-cookies', false, dirname( plugin_basename( SPC_FILE ) ) . '/languages' );
	}

	/**
	 * Activation: seed default settings.
	 */
	public static function activate() {
		if ( false === get_option( SPC_OPT_SETTINGS ) ) {
			add_option( SPC_OPT_SETTINGS, SPC_Settings::defaults() );
		} else {
			// Merge in any newly-added defaults.
			update_option( SPC_OPT_SETTINGS, wp_parse_args( get_option( SPC_OPT_SETTINGS ), SPC_Settings::defaults() ) );
		}

		if ( false === get_option( SPC_OPT_COOKIES ) ) {
			add_option( SPC_OPT_COOKIES, SPC_Cookie_DB::default_cookie_table() );
		}
	}
}
