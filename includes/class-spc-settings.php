<?php
/**
 * Settings storage, defaults and helpers.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Settings {

	/**
	 * The cookie categories used everywhere. Order matters for display.
	 *
	 * Each maps to one or more Google Consent Mode v2 signals.
	 */
	public static function categories() {
		return array(
			'necessary'     => array(
				'label'       => __( 'Necessary', 'sleekpress-cookies' ),
				'description' => __( 'Necessary cookies are required to enable the basic features of this site, such as providing secure log-in or adjusting your consent preferences. These cookies do not store any personally identifiable data.', 'sleekpress-cookies' ),
				'locked'      => true,
				'gcm'         => array( 'security_storage' ),
			),
			'functional'    => array(
				'label'       => __( 'Functional', 'sleekpress-cookies' ),
				'description' => __( 'Functional cookies help perform certain functionalities like sharing the content of the website on social media platforms, collecting feedback, and other third-party features.', 'sleekpress-cookies' ),
				'locked'      => false,
				'gcm'         => array( 'functionality_storage', 'personalization_storage' ),
			),
			'analytics'     => array(
				'label'       => __( 'Analytics', 'sleekpress-cookies' ),
				'description' => __( 'Analytical cookies are used to understand how visitors interact with the website. These cookies help provide information on metrics such as the number of visitors, bounce rate, traffic source, etc.', 'sleekpress-cookies' ),
				'locked'      => false,
				'gcm'         => array( 'analytics_storage' ),
			),
			'advertisement' => array(
				'label'       => __( 'Advertisement', 'sleekpress-cookies' ),
				'description' => __( 'Advertisement cookies are used to provide visitors with customised advertisements based on the pages you visited previously and to analyse the effectiveness of the ad campaigns.', 'sleekpress-cookies' ),
				'locked'      => false,
				'gcm'         => array( 'ad_storage', 'ad_user_data', 'ad_personalization' ),
			),
			'others'        => array(
				'label'       => __( 'Others', 'sleekpress-cookies' ),
				'description' => __( 'Other uncategorised cookies are those that are being analysed and have not been classified into a category as yet.', 'sleekpress-cookies' ),
				'locked'      => false,
				'gcm'         => array(),
			),
		);
	}

	public static function defaults() {
		return array(
			// General.
			'enabled'              => 1,
			'hide_for_admins'      => 0,
			'consent_expiry_days'  => 365,

			// Banner content.
			'title'                => __( 'We value your privacy', 'sleekpress-cookies' ),
			'message'              => __( 'We use cookies to enhance your browsing experience, serve personalised ads or content, and analyse our traffic. By clicking "Accept", you consent to our use of cookies.', 'sleekpress-cookies' ),
			'privacy_url'          => '', // Empty => auto from WP privacy policy page.
			'privacy_link_text'    => __( 'Privacy Policy', 'sleekpress-cookies' ),
			'btn_accept_text'      => __( 'Accept', 'sleekpress-cookies' ),
			'btn_decline_text'     => __( 'Decline', 'sleekpress-cookies' ),
			'btn_adjust_text'      => __( 'Adjust', 'sleekpress-cookies' ),
			'btn_save_text'        => __( 'Save my preferences', 'sleekpress-cookies' ),
			'show_branding'        => 1,
			'show_revisit_badge'   => 1,

			// Appearance.
			'position'             => 'bottom-left', // bottom-left, bottom-right, bottom-bar.
			'theme'                => 'light', // light, dark.
			'color_bg'             => '#ffffff',
			'color_text'           => '#1f2933',
			'color_primary'        => '#2563eb', // Accept button.
			'color_primary_text'   => '#ffffff',
			'color_secondary'      => '#e5e7eb', // Decline / Adjust button bg.
			'color_secondary_text' => '#1f2933',
			'border_radius'        => 12,

			// Tags.
			'gtm_id'               => '', // GTM-XXXXXXX. If set, plugin prints the GTM snippet.
			'ga4_id'               => '', // G-XXXXXXXXXX. If set (and no GTM), plugin prints gtag.js.

			// Google Consent Mode v2.
			'gcm_enabled'          => 1,
			'gcm_wait_for_update'  => 500,
			'gcm_url_passthrough'  => 1,
			'gcm_ads_redaction'    => 1,
			// Per-category default state before consent. necessary is always granted.
			'gcm_default'          => array(
				'functional'    => 'denied',
				'analytics'     => 'denied',
				'advertisement' => 'denied',
			),

			// AI.
			'openai_api_key'       => '',
			'openai_model'         => 'gpt-4o-mini',
		);
	}

	/**
	 * @return array
	 */
	public static function get() {
		return wp_parse_args( (array) get_option( SPC_OPT_SETTINGS, array() ), self::defaults() );
	}

	public static function get_value( $key, $fallback = '' ) {
		$all = self::get();
		return isset( $all[ $key ] ) ? $all[ $key ] : $fallback;
	}

	public static function save( array $new ) {
		update_option( SPC_OPT_SETTINGS, wp_parse_args( $new, self::defaults() ) );
	}

	/**
	 * Resolve the privacy policy URL: explicit setting, else WP core privacy page.
	 */
	public static function privacy_url() {
		$url = trim( (string) self::get_value( 'privacy_url' ) );
		if ( '' === $url && function_exists( 'get_privacy_policy_url' ) ) {
			$url = get_privacy_policy_url();
		}
		return $url;
	}

	/**
	 * Cookie table: category => array of cookie rows.
	 */
	public static function cookies() {
		$data = get_option( SPC_OPT_COOKIES );
		if ( ! is_array( $data ) ) {
			$data = SPC_Cookie_DB::default_cookie_table();
		}
		// Ensure every category key exists.
		foreach ( array_keys( self::categories() ) as $cat ) {
			if ( ! isset( $data[ $cat ] ) || ! is_array( $data[ $cat ] ) ) {
				$data[ $cat ] = array();
			}
		}
		return $data;
	}

	public static function save_cookies( array $table ) {
		$clean = array();
		foreach ( array_keys( self::categories() ) as $cat ) {
			$clean[ $cat ] = array();
			if ( empty( $table[ $cat ] ) || ! is_array( $table[ $cat ] ) ) {
				continue;
			}
			foreach ( $table[ $cat ] as $row ) {
				$clean[ $cat ][] = array(
					'name'        => sanitize_text_field( $row['name'] ?? '' ),
					'domain'      => sanitize_text_field( $row['domain'] ?? '' ),
					'duration'    => sanitize_text_field( $row['duration'] ?? '' ),
					'description' => sanitize_textarea_field( $row['description'] ?? '' ),
					'provider'    => sanitize_text_field( $row['provider'] ?? '' ),
				);
			}
		}
		update_option( SPC_OPT_COOKIES, $clean );
	}
}
