<?php
/**
 * REST endpoints: visitor cookie reporting + admin scan / AI calls.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Rest {

	const NS = 'spc/v1';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	public function register() {
		register_rest_route(
			self::NS,
			'/observe',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'observe' ),
			)
		);

		$can_manage = function () {
			return current_user_can( 'manage_options' );
		};
		$admin = array(
			'methods'             => 'POST',
			'permission_callback' => $can_manage,
		);

		register_rest_route( self::NS, '/scan', $admin + array( 'callback' => array( $this, 'scan' ) ) );
		register_rest_route( self::NS, '/ai-categorize', $admin + array( 'callback' => array( $this, 'ai_categorize' ) ) );
		register_rest_route( self::NS, '/merge', $admin + array( 'callback' => array( $this, 'merge' ) ) );

		register_rest_route(
			self::NS,
			'/settings',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => $can_manage,
					'callback'            => array( $this, 'get_settings' ),
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => $can_manage,
					'callback'            => array( $this, 'save_settings' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/cookies',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => $can_manage,
					'callback'            => array( $this, 'get_cookies' ),
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => $can_manage,
					'callback'            => array( $this, 'save_cookies' ),
				),
			)
		);
	}

	/**
	 * Public: a visitor's browser reporting the cookie names it currently has.
	 * Body: { cookies: { "name": "domain", ... } }
	 */
	public function observe( WP_REST_Request $req ) {
		$cookies = $req->get_param( 'cookies' );
		if ( ! is_array( $cookies ) ) {
			return new WP_REST_Response( array( 'ok' => false ), 400 );
		}
		// Trim to a sane size.
		$cookies = array_slice( $cookies, 0, 60, true );
		SPC_Scanner::record_observed( $cookies );
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	public function scan() {
		$result = SPC_Scanner::scan();
		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Body: { cookies: [ { name, domain, duration, provider }, ... ] }
	 */
	public function ai_categorize( WP_REST_Request $req ) {
		$cookies = $req->get_param( 'cookies' );
		if ( ! is_array( $cookies ) ) {
			return new WP_REST_Response( array( 'error' => 'no cookies' ), 400 );
		}
		$out = SPC_AI::categorize( $cookies );
		if ( is_wp_error( $out ) ) {
			return new WP_REST_Response( array( 'error' => $out->get_error_message() ), 502 );
		}
		return new WP_REST_Response( array( 'cookies' => $out ), 200 );
	}

	/**
	 * Body: { rows: [ { name, category, domain, duration, description, provider }, ... ] }
	 */
	public function merge( WP_REST_Request $req ) {
		$rows = $req->get_param( 'rows' );
		if ( ! is_array( $rows ) ) {
			return new WP_REST_Response( array( 'error' => 'no rows' ), 400 );
		}
		$table = SPC_Scanner::merge_into_table( $rows );
		return new WP_REST_Response( array( 'ok' => true, 'cookies' => $table ), 200 );
	}

	/* ---------------- settings ---------------- */

	private function settings_payload() {
		$lang = SPC_Settings::resolved_language();
		$langs = array();
		foreach ( SPC_I18n::languages() as $code => $label ) {
			$langs[] = array( 'value' => $code, 'label' => $label );
		}
		return array(
			'settings'           => SPC_Settings::get(),
			'categories'         => SPC_Settings::categories_payload(),
			'privacyAuto'        => SPC_Settings::privacy_url(),
			'wpPrivacyUrl'       => function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '',
			'aiReady'            => SPC_AI::is_configured(),
			'version'            => SPC_VERSION,
			'lastScan'           => SPC_Scanner::last_scan(),
			'languages'          => $langs,
			'resolvedLanguage'   => $lang,
			'languageName'       => SPC_I18n::language_name( $lang ),
			'translatedDefaults' => SPC_I18n::strings( $lang ),
		);
	}

	public function get_settings() {
		return new WP_REST_Response( $this->settings_payload(), 200 );
	}

	public function save_settings( WP_REST_Request $req ) {
		$in = $req->get_param( 'settings' );
		if ( ! is_array( $in ) ) {
			return new WP_REST_Response( array( 'error' => 'no settings' ), 400 );
		}
		SPC_Settings::save( SPC_Settings::sanitize( $in ) );
		return new WP_REST_Response( $this->settings_payload(), 200 );
	}

	/* ---------------- cookie list ---------------- */

	private function cookies_payload() {
		return array(
			'cookies'    => SPC_Settings::cookies(),
			'categories' => SPC_Settings::categories_payload(),
		);
	}

	public function get_cookies() {
		return new WP_REST_Response( $this->cookies_payload(), 200 );
	}

	public function save_cookies( WP_REST_Request $req ) {
		$table = $req->get_param( 'cookies' );
		if ( ! is_array( $table ) ) {
			return new WP_REST_Response( array( 'error' => 'no cookies' ), 400 );
		}
		SPC_Settings::save_cookies( $table );
		return new WP_REST_Response( $this->cookies_payload(), 200 );
	}
}
