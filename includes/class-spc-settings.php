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
			'banner_width'         => 26.25, // rem; 26.25rem ≈ 420px. Box positions only.

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
	 * Sanitise a (possibly partial) settings payload coming from the admin app
	 * against the current stored settings. Unknown keys are ignored.
	 */
	public static function sanitize( array $in ) {
		$s = self::get();

		$bools = array( 'enabled', 'hide_for_admins', 'show_branding', 'show_revisit_badge', 'gcm_enabled', 'gcm_url_passthrough', 'gcm_ads_redaction' );
		foreach ( $bools as $k ) {
			if ( array_key_exists( $k, $in ) ) {
				$s[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
			}
		}

		if ( array_key_exists( 'consent_expiry_days', $in ) ) {
			$s['consent_expiry_days'] = max( 1, min( 3650, (int) $in['consent_expiry_days'] ) );
		}
		if ( array_key_exists( 'border_radius', $in ) ) {
			$s['border_radius'] = max( 0, min( 40, (int) $in['border_radius'] ) );
		}
		if ( array_key_exists( 'gcm_wait_for_update', $in ) ) {
			$s['gcm_wait_for_update'] = max( 0, min( 10000, (int) $in['gcm_wait_for_update'] ) );
		}
		if ( array_key_exists( 'banner_width', $in ) ) {
			$s['banner_width'] = min( 60, max( 16, (float) $in['banner_width'] ) );
		}

		$texts = array( 'title', 'btn_accept_text', 'btn_decline_text', 'btn_adjust_text', 'btn_save_text', 'privacy_link_text', 'openai_model' );
		foreach ( $texts as $k ) {
			if ( array_key_exists( $k, $in ) ) {
				$s[ $k ] = sanitize_text_field( (string) $in[ $k ] );
			}
		}
		if ( array_key_exists( 'message', $in ) ) {
			$s['message'] = wp_kses_post( (string) $in['message'] );
		}
		if ( array_key_exists( 'privacy_url', $in ) ) {
			$s['privacy_url'] = esc_url_raw( (string) $in['privacy_url'] );
		}
		if ( array_key_exists( 'openai_api_key', $in ) ) {
			$s['openai_api_key'] = trim( sanitize_text_field( (string) $in['openai_api_key'] ) );
		}
		foreach ( array( 'gtm_id', 'ga4_id' ) as $k ) {
			if ( array_key_exists( $k, $in ) ) {
				$s[ $k ] = preg_replace( '/[^A-Za-z0-9\-]/', '', trim( (string) $in[ $k ] ) );
			}
		}
		foreach ( array( 'color_bg', 'color_text', 'color_primary', 'color_primary_text', 'color_secondary', 'color_secondary_text' ) as $k ) {
			if ( array_key_exists( $k, $in ) ) {
				$c = self::sanitize_color_value( (string) $in[ $k ] );
				if ( null !== $c ) {
					$s[ $k ] = $c;
				}
			}
		}
		if ( array_key_exists( 'position', $in ) && in_array( $in['position'], array( 'bottom-left', 'bottom-right', 'bottom-bar' ), true ) ) {
			$s['position'] = $in['position'];
		}
		if ( array_key_exists( 'theme', $in ) ) {
			$s['theme'] = ( 'dark' === $in['theme'] ) ? 'dark' : 'light';
		}
		if ( isset( $in['gcm_default'] ) && is_array( $in['gcm_default'] ) ) {
			foreach ( array( 'functional', 'analytics', 'advertisement' ) as $c ) {
				if ( isset( $in['gcm_default'][ $c ] ) ) {
					$s['gcm_default'][ $c ] = ( 'granted' === $in['gcm_default'][ $c ] ) ? 'granted' : 'denied';
				}
			}
		}

		return wp_parse_args( $s, self::defaults() );
	}

	/**
	 * Validate a colour value. Accepts a hex literal (#fff / #ffffff / #ffffffff)
	 * or a CSS `var(--name)` reference (optionally with a hex / nested-var
	 * fallback) so users can wire colours up to their theme's design tokens.
	 *
	 * @return string|null  Cleaned value, or null if the input is rejected.
	 */
	public static function sanitize_color_value( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return null;
		}
		if ( preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $v ) ) {
			return strtolower( $v );
		}
		// var(--name) | var(--name, #hex) | var(--name, var(--other))
		if ( preg_match(
			'/^var\(\s*--[A-Za-z0-9_-]+(?:\s*,\s*(?:#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})|var\(\s*--[A-Za-z0-9_-]+\s*\)))?\s*\)$/i',
			$v
		) ) {
			return $v;
		}
		return null;
	}

	/**
	 * Category metadata in a JS-friendly shape.
	 */
	public static function categories_payload() {
		$out = array();
		foreach ( self::categories() as $key => $cat ) {
			$out[] = array(
				'key'         => $key,
				'label'       => $cat['label'],
				'description' => $cat['description'],
				'locked'      => ! empty( $cat['locked'] ),
				'gcm'         => $cat['gcm'],
			);
		}
		return $out;
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
