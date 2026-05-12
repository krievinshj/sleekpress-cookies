<?php
/**
 * Admin: registers the menu page and boots the Vue admin app (built bundle).
 * All UI lives in /admin-app and is served from /assets/admin/dist.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Admin {

	const SLUG = 'sleekpress-cookies';
	const CAP  = 'manage_options';
	const ROOT = 'spc-admin-app';

	/** @var bool Whether the built bundle was found & enqueued. */
	private $built = false;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function menu() {
		add_menu_page(
			__( 'SleekPress Cookies', 'sleekpress-cookies' ),
			__( 'Cookies', 'sleekpress-cookies' ),
			self::CAP,
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-privacy',
			76
		);
	}

	private function is_our_page( $hook ) {
		return 'toplevel_page_' . self::SLUG === $hook;
	}

	public function assets( $hook ) {
		if ( ! $this->is_our_page( $hook ) || ! class_exists( 'SleekPress_UI' ) ) {
			return;
		}
		$this->built = SleekPress_UI::enqueue_app(
			array(
				'handle'         => 'spc-admin',
				'dist_dir'       => SPC_DIR . 'assets/admin/dist',
				'dist_url'       => SPC_URL . 'assets/admin/dist',
				'asset_name'     => 'admin',
				'version'        => SPC_VERSION,
				'config_var'     => 'SPCAdmin',
				'rest_namespace' => SPC_Rest::NS,
				'config'         => array(
					'mountId'    => self::ROOT,
					'pageSlug'   => self::SLUG,
					'docsUrl'    => 'https://sleekpress.com/docs/cookies/',
					'pluginName' => __( 'SleekPress Cookies', 'sleekpress-cookies' ),
				),
			)
		);
	}

	public function render() {
		echo '<div class="wrap" style="margin:0;padding:0;">';
		echo '<h1 class="screen-reader-text">' . esc_html__( 'SleekPress Cookies', 'sleekpress-cookies' ) . '</h1>';
		if ( class_exists( 'SleekPress_UI' ) ) {
			SleekPress_UI::render_root( self::ROOT, $this->built, 'cd ' . esc_html( basename( SPC_DIR ) ) . ' && npm install && npm run build' );
		} else {
			echo '<div class="notice notice-error"><p>SleekPress UI loader missing.</p></div>';
		}
		echo '</div>';
	}
}
