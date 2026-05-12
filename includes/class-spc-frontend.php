<?php
/**
 * Front-end: consent-mode bootstrap in <head>, GTM/GA injection, and the
 * consent banner / preferences modal.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Frontend {

	const COOKIE = 'spc_consent';

	public function __construct() {
		// Very early in <head> so consent defaults beat any tag.
		add_action( 'wp_head', array( $this, 'print_head' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'print_banner' ) );
		add_shortcode( 'sleekpress_cookie_settings', array( $this, 'shortcode_link' ) );
	}

	private function active() {
		$s = SPC_Settings::get();
		if ( empty( $s['enabled'] ) ) {
			return false;
		}
		if ( ! empty( $s['hide_for_admins'] ) && current_user_can( 'manage_options' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Decode the consent cookie, or null if not set.
	 */
	private function current_consent() {
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return null;
		}
		$raw  = wp_unslash( $_COOKIE[ self::COOKIE ] );
		$data = json_decode( (string) $raw, true );
		return is_array( $data ) ? $data : null;
	}

	/**
	 * Build category => 'granted'|'denied' from a consent array (or defaults).
	 */
	private function category_states( $consent, $s ) {
		$cats   = SPC_Settings::categories();
		$states = array();
		foreach ( $cats as $key => $cat ) {
			if ( ! empty( $cat['locked'] ) ) {
				$states[ $key ] = 'granted';
				continue;
			}
			if ( is_array( $consent ) ) {
				$states[ $key ] = ! empty( $consent[ $key ] ) ? 'granted' : 'denied';
			} else {
				$def = $s['gcm_default'][ $key ] ?? 'denied';
				$states[ $key ] = ( 'granted' === $def ) ? 'granted' : 'denied';
			}
		}
		return $states;
	}

	/**
	 * Translate category states into Google Consent Mode v2 signal map.
	 */
	private function gcm_signals( $category_states ) {
		$cats    = SPC_Settings::categories();
		$signals = array();
		foreach ( $category_states as $key => $state ) {
			if ( empty( $cats[ $key ]['gcm'] ) ) {
				continue;
			}
			foreach ( $cats[ $key ]['gcm'] as $signal ) {
				$signals[ $signal ] = $state;
			}
		}
		// Security storage always granted.
		$signals['security_storage'] = 'granted';
		return $signals;
	}

	public function print_head() {
		if ( ! $this->active() ) {
			return;
		}
		$s       = SPC_Settings::get();
		$consent = $this->current_consent();

		echo "\n<!-- SleekPress Cookies -->\n";
		echo "<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);}</script>\n";

		if ( ! empty( $s['gcm_enabled'] ) ) {
			$default_states = $this->category_states( null, $s );
			$default        = $this->gcm_signals( $default_states );
			$default['wait_for_update'] = (int) $s['gcm_wait_for_update'];

			$extra = '';
			if ( ! empty( $s['gcm_url_passthrough'] ) ) {
				$extra .= "gtag('set', 'url_passthrough', true);\n";
			}
			if ( ! empty( $s['gcm_ads_redaction'] ) ) {
				$extra .= "gtag('set', 'ads_data_redaction', true);\n";
			}

			echo "<script>\n";
			echo "gtag('consent', 'default', " . wp_json_encode( $default ) . ");\n";
			echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput
			// If we already have a stored choice, apply it immediately so tags
			// loaded on this very page request see the updated state.
			if ( is_array( $consent ) ) {
				$update = $this->gcm_signals( $this->category_states( $consent, $s ) );
				echo "gtag('consent', 'update', " . wp_json_encode( $update ) . ");\n";
				echo "dataLayer.push({'event':'spc_consent_update','spc_consent':" . wp_json_encode( $this->category_states( $consent, $s ) ) . "});\n";
			}
			echo "</script>\n";
		}

		// Optionally print the GTM / GA loader (only if the site owner asked us to).
		$gtm = trim( (string) $s['gtm_id'] );
		$ga4 = trim( (string) $s['ga4_id'] );
		if ( '' !== $gtm ) {
			$gtm_id = esc_js( $gtm );
			echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$gtm_id}');</script>\n";
		} elseif ( '' !== $ga4 ) {
			$ga4_id = esc_js( $ga4 );
			echo "<script async src='https://www.googletagmanager.com/gtag/js?id={$ga4_id}'></script>\n";
			echo "<script>gtag('js', new Date());gtag('config', '{$ga4_id}');</script>\n";
		}
		echo "<!-- /SleekPress Cookies -->\n";
	}

	/**
	 * GTM <noscript> iframe right after <body>. WP has no body_open on old
	 * themes, so this is best-effort.
	 */
	public function body_open() {
		$gtm = trim( (string) SPC_Settings::get_value( 'gtm_id' ) );
		if ( '' === $gtm || ! $this->active() ) {
			return;
		}
		printf(
			'<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>',
			esc_attr( $gtm )
		);
	}

	public function enqueue() {
		if ( ! $this->active() ) {
			return;
		}
		// body_open is added here so it only registers on front-end.
		add_action( 'wp_body_open', array( $this, 'body_open' ) );

		wp_enqueue_style( 'spc-banner', SPC_URL . 'assets/css/spc-banner.css', array(), SPC_VERSION );
		wp_enqueue_script( 'spc-banner', SPC_URL . 'assets/js/spc-banner.js', array(), SPC_VERSION, true );

		$s        = SPC_Settings::get();
		$consent  = $this->current_consent();
		$cats     = SPC_Settings::categories();
		$cookies  = SPC_Settings::cookies();

		// Public-facing category data (label, description, locked) + cookies.
		$cat_payload = array();
		foreach ( $cats as $key => $cat ) {
			$cat_payload[] = array(
				'key'         => $key,
				'label'       => $cat['label'],
				'description' => $cat['description'],
				'locked'      => ! empty( $cat['locked'] ),
				'cookies'     => array_map(
					function ( $r ) {
						return array(
							'name'        => $r['name'],
							'provider'    => $r['provider'] ?? '',
							'duration'    => $r['duration'] ?? '',
							'description' => $r['description'] ?? '',
						);
					},
					$cookies[ $key ] ?? array()
				),
			);
		}

		wp_localize_script(
			'spc-banner',
			'SPC',
			array(
				'rest'        => array(
					'observe' => esc_url_raw( rest_url( SPC_Rest::NS . '/observe' ) ),
				),
				'cookieName'  => self::COOKIE,
				'expiryDays'  => (int) $s['consent_expiry_days'],
				'gcmEnabled'  => ! empty( $s['gcm_enabled'] ),
				'gcmMap'      => $this->category_gcm_map(),
				'categories'  => $cat_payload,
				'consent'     => $consent, // null if undecided.
				'hasConsent'  => is_array( $consent ),
				'texts'       => array(
					'title'       => $s['title'],
					'message'     => wp_kses_post( $s['message'] ),
					'accept'      => $s['btn_accept_text'],
					'decline'     => $s['btn_decline_text'],
					'adjust'      => $s['btn_adjust_text'],
					'save'        => $s['btn_save_text'],
					'prefTitle'   => __( 'Customise consent preferences', 'sleekpress-cookies' ),
					'privacyText' => $s['privacy_link_text'],
					'showCookies' => __( 'Show cookies', 'sleekpress-cookies' ),
					'hideCookies' => __( 'Hide cookies', 'sleekpress-cookies' ),
					'always'      => __( 'Always active', 'sleekpress-cookies' ),
				),
				'privacyUrl'  => SPC_Settings::privacy_url(),
				'position'    => $s['position'],
				'showBadge'   => ! empty( $s['show_revisit_badge'] ),
				'showBrand'   => ! empty( $s['show_branding'] ),
			)
		);

		// Colour variables.
		$css = sprintf(
			// Must match the selector used in spc-banner.css (#spc-root) so the
			// custom properties actually override the bundled defaults — a
			// ":root" rule would lose to "#spc-root" on specificity.
			'#spc-root{--spc-bg:%s;--spc-text:%s;--spc-primary:%s;--spc-primary-text:%s;--spc-secondary:%s;--spc-secondary-text:%s;--spc-radius:%dpx;}',
			esc_html( $s['color_bg'] ),
			esc_html( $s['color_text'] ),
			esc_html( $s['color_primary'] ),
			esc_html( $s['color_primary_text'] ),
			esc_html( $s['color_secondary'] ),
			esc_html( $s['color_secondary_text'] ),
			(int) $s['border_radius']
		);
		if ( 'dark' === $s['theme'] ) {
			$css .= '.spc-banner,.spc-modal__box{color-scheme:dark;}';
		}
		wp_add_inline_style( 'spc-banner', $css );
	}

	/**
	 * category key => array of GCM signals (for the JS to build update calls).
	 */
	private function category_gcm_map() {
		$map = array();
		foreach ( SPC_Settings::categories() as $key => $cat ) {
			$map[ $key ] = $cat['gcm'];
		}
		return $map;
	}

	public function print_banner() {
		if ( ! $this->active() ) {
			return;
		}
		// The actual markup is built by JS from the localized data so that
		// translations / kses happen once; we just drop a mount point.
		echo '<div id="spc-root" aria-live="polite"></div>';
	}

	public function shortcode_link( $atts ) {
		$atts = shortcode_atts( array( 'text' => __( 'Cookie settings', 'sleekpress-cookies' ) ), $atts, 'sleekpress_cookie_settings' );
		return sprintf( '<a href="#" class="spc-open-prefs">%s</a>', esc_html( $atts['text'] ) );
	}
}
