<?php
/**
 * Bundled database of well-known third-party scripts and the cookies they set.
 *
 * Used by the scanner to (a) detect services from script signatures in page
 * HTML, and (b) provide a sane default cookie table on activation.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Cookie_DB {

	/**
	 * Known services. Each: signatures (substrings to look for in page HTML /
	 * enqueued script URLs), provider name, category, and the cookies it sets.
	 *
	 * @return array
	 */
	public static function services() {
		return array(
			'google-analytics-4' => array(
				'provider'   => 'Google',
				'category'   => 'analytics',
				'signatures' => array( 'googletagmanager.com/gtag/js', 'gtag(', 'google-analytics.com/g/collect', 'G-' ),
				'cookies'    => array(
					array( 'name' => '_ga', 'domain' => '', 'duration' => '1 year 1 month 4 days', 'description' => 'Google Analytics sets this cookie to calculate visitor, session and campaign data and to keep track of site usage for the site\'s analytics report. The cookie stores information anonymously and assigns a randomly generated number to recognise unique visitors.' ),
					array( 'name' => '_ga_*', 'domain' => '', 'duration' => '1 year 1 month 4 days', 'description' => 'Google Analytics sets this cookie to store and count page views.' ),
					array( 'name' => '_gid', 'domain' => '', 'duration' => '1 day', 'description' => 'Google Analytics sets this cookie to store information on how visitors use a website while also creating an analytics report of the website\'s performance.' ),
					array( 'name' => '_gat', 'domain' => '', 'duration' => '1 minute', 'description' => 'Google Analytics sets this cookie to throttle the request rate.' ),
				),
			),
			'google-tag-manager' => array(
				'provider'   => 'Google',
				'category'   => 'analytics',
				'signatures' => array( 'googletagmanager.com/gtm.js', 'GTM-' ),
				'cookies'    => array(),
			),
			'google-ads'         => array(
				'provider'   => 'Google',
				'category'   => 'advertisement',
				'signatures' => array( 'googleadservices.com', 'google.com/ads', 'gtag/js', 'AW-', 'doubleclick.net' ),
				'cookies'    => array(
					array( 'name' => '_gcl_au', 'domain' => '', 'duration' => '3 months', 'description' => 'Google Tag Manager sets the cookie to experiment advertisement efficiency of websites using their services.' ),
					array( 'name' => 'IDE', 'domain' => '.doubleclick.net', 'duration' => '1 year 24 days', 'description' => 'Google DoubleClick IDE cookies store information about how the user uses the website to present them with relevant ads according to the user profile.' ),
					array( 'name' => 'test_cookie', 'domain' => '.doubleclick.net', 'duration' => '15 minutes', 'description' => 'doubleclick.net sets this cookie to determine if the browser supports cookies.' ),
				),
			),
			'facebook-pixel'     => array(
				'provider'   => 'Meta Platforms, Inc.',
				'category'   => 'advertisement',
				'signatures' => array( 'connect.facebook.net', 'fbq(', 'facebook.com/tr' ),
				'cookies'    => array(
					array( 'name' => '_fbp', 'domain' => '', 'duration' => '3 months', 'description' => 'Facebook sets this cookie to display advertisements when on Facebook or on a digital platform powered by Facebook advertising after visiting the website.' ),
					array( 'name' => 'fr', 'domain' => '.facebook.com', 'duration' => '3 months', 'description' => 'Facebook sets this cookie to show relevant advertisements by tracking user behaviour across the web, on sites with a Facebook pixel or Facebook social plugin.' ),
				),
			),
			'hotjar'             => array(
				'provider'   => 'Hotjar Ltd.',
				'category'   => 'analytics',
				'signatures' => array( 'static.hotjar.com', 'hotjar.com', 'hj(' ),
				'cookies'    => array(
					array( 'name' => '_hjSessionUser_*', 'domain' => '', 'duration' => '1 year', 'description' => 'Hotjar sets this cookie to ensure data from subsequent visits to the same site is attributed to the same user ID, which persists in the Hotjar User ID.' ),
					array( 'name' => '_hjSession_*', 'domain' => '', 'duration' => '1 hour', 'description' => 'Hotjar sets this cookie to hold the current session data.' ),
				),
			),
			'youtube'            => array(
				'provider'   => 'Google',
				'category'   => 'advertisement',
				'signatures' => array( 'youtube.com/embed', 'youtube-nocookie.com', 'youtube.com/iframe_api' ),
				'cookies'    => array(
					array( 'name' => 'VISITOR_INFO1_LIVE', 'domain' => '.youtube.com', 'duration' => '6 months', 'description' => 'YouTube sets this cookie to measure bandwidth, determining whether the user gets the new or old player interface.' ),
					array( 'name' => 'YSC', 'domain' => '.youtube.com', 'duration' => 'session', 'description' => 'YouTube sets this cookie to track the views of embedded videos on YouTube pages.' ),
					array( 'name' => 'PREF', 'domain' => '.youtube.com', 'duration' => '8 months', 'description' => 'YouTube sets this cookie to store the user\'s video preferences using embedded YouTube videos.' ),
				),
			),
			'vimeo'              => array(
				'provider'   => 'Vimeo, Inc.',
				'category'   => 'functional',
				'signatures' => array( 'player.vimeo.com', 'vimeo.com/api' ),
				'cookies'    => array(
					array( 'name' => 'vuid', 'domain' => '.vimeo.com', 'duration' => '1 year 1 month 4 days', 'description' => 'Vimeo installs this cookie to collect tracking information by setting a unique ID to embed videos on the website.' ),
				),
			),
			'linkedin'           => array(
				'provider'   => 'LinkedIn Corporation',
				'category'   => 'advertisement',
				'signatures' => array( 'snap.licdn.com', 'platform.linkedin.com', 'ads.linkedin.com', '_linkedin_partner_id' ),
				'cookies'    => array(
					array( 'name' => 'bcookie', 'domain' => '.linkedin.com', 'duration' => '1 year', 'description' => 'LinkedIn sets this cookie from LinkedIn share buttons and ad tags to recognise browser IDs.' ),
					array( 'name' => 'li_sugr', 'domain' => '.linkedin.com', 'duration' => '3 months', 'description' => 'LinkedIn sets this cookie to collect user behaviour data to optimise the website and make advertisements on the website more relevant.' ),
				),
			),
			'tiktok'             => array(
				'provider'   => 'TikTok',
				'category'   => 'advertisement',
				'signatures' => array( 'analytics.tiktok.com', 'ttq.load' ),
				'cookies'    => array(
					array( 'name' => '_ttp', 'domain' => '', 'duration' => '1 year 1 month 4 days', 'description' => 'TikTok set this cookie to track and improve the performance of advertising campaigns and to personalise the user experience.' ),
				),
			),
			'woocommerce'        => array(
				'provider'   => 'WooCommerce',
				'category'   => 'necessary',
				'signatures' => array( '/wc-ajax/', 'woocommerce-no-js', 'wc_add_to_cart_params', 'wc-blocks', '/woocommerce/assets/', 'woocommerce_params' ),
				'cookies'    => array(
					array( 'name' => 'woocommerce_cart_hash', 'domain' => '', 'duration' => 'session', 'description' => 'WooCommerce sets this cookie to track the cart contents and detect when the cart has been changed, so the mini-cart and checkout can be updated.' ),
					array( 'name' => 'woocommerce_items_in_cart', 'domain' => '', 'duration' => 'session', 'description' => 'WooCommerce sets this cookie to indicate whether the cart contains items, so it can update the cart display without a full page reload.' ),
					array( 'name' => 'wp_woocommerce_session_*', 'domain' => '', 'duration' => '2 days', 'description' => 'WooCommerce sets this cookie with a unique code for each customer so it can find their cart data in the database.' ),
					array( 'name' => 'woocommerce_recently_viewed', 'domain' => '', 'duration' => 'session', 'description' => 'WooCommerce sets this cookie to power the "recently viewed products" widget.' ),
					array( 'name' => 'tk_ni', 'domain' => '', 'duration' => 'session', 'description' => 'WooCommerce/Automattic sets this cookie to register whether the user has opted out of activity tracking.' ),
				),
			),
			'automattic-tracks'  => array(
				'provider'   => 'Automattic, Inc.',
				'category'   => 'analytics',
				'signatures' => array( 'pix.wp.com', 'stats.wp.com', 'tracks.js', 'wccom-tracker', 'wpcom_analytics' ),
				'cookies'    => array(
					array( 'name' => 'tk_ai', 'domain' => '', 'duration' => '1 year', 'description' => 'Automattic Tracks (bundled with WooCommerce admin / Jetpack) sets this cookie to store an anonymous user identifier used for product analytics across Automattic services.' ),
					array( 'name' => 'tk_qs', 'domain' => '', 'duration' => 'session', 'description' => 'Automattic Tracks sets this cookie as a per-session queue identifier used while batching analytics events.' ),
					array( 'name' => 'tk_lr', 'domain' => '', 'duration' => '1 year', 'description' => 'Automattic Tracks sets this cookie to store the last referrer URL for attribution.' ),
					array( 'name' => 'tk_or', 'domain' => '', 'duration' => '5 years', 'description' => 'Automattic Tracks sets this cookie to store the original referrer URL for attribution.' ),
					array( 'name' => 'tk_r3d', 'domain' => '', 'duration' => '3 days', 'description' => 'Automattic Tracks sets this cookie as part of its analytics queue/retry mechanism.' ),
					array( 'name' => 'tk_tc', 'domain' => '', 'duration' => 'session', 'description' => 'Automattic Tracks sets this cookie to record campaign/click information for marketing attribution.' ),
				),
			),
			'sourcebuster'       => array(
				'provider'   => 'Sourcebuster.js',
				'category'   => 'advertisement',
				'signatures' => array( 'sbjs.js', 'sourcebuster', 'sbjs_writeCookies', 'sbjs.init(' ),
				'cookies'    => array(
					array( 'name' => 'sbjs_first', 'domain' => '', 'duration' => '6 months', 'description' => 'Sourcebuster sets this cookie to record the visitor\'s first traffic source (UTM source/medium/campaign, referrer and entrance page) for marketing attribution.' ),
					array( 'name' => 'sbjs_first_add', 'domain' => '', 'duration' => '6 months', 'description' => 'Sourcebuster sets this cookie to record extra first-touch attribution data such as the timestamp and entrance page of the visitor\'s first visit.' ),
					array( 'name' => 'sbjs_current', 'domain' => '', 'duration' => '6 months', 'description' => 'Sourcebuster sets this cookie to record the traffic source of the visitor\'s current session (UTM tags and referrer) for marketing attribution.' ),
					array( 'name' => 'sbjs_current_add', 'domain' => '', 'duration' => '6 months', 'description' => 'Sourcebuster sets this cookie to record extra current-session attribution data such as the entrance page and timestamp.' ),
					array( 'name' => 'sbjs_session', 'domain' => '', 'duration' => '30 minutes', 'description' => 'Sourcebuster sets this cookie to keep track of the current session: number of pages viewed and the entrance page.' ),
					array( 'name' => 'sbjs_udata', 'domain' => '', 'duration' => '6 months', 'description' => 'Sourcebuster sets this cookie to store basic user data — visit count, user agent and IP — used for marketing attribution.' ),
					array( 'name' => 'sbjs_migrations', 'domain' => '', 'duration' => '6 months', 'description' => 'Sourcebuster sets this cookie as an internal flag tracking which cookie-schema migrations have been applied between versions of the library.' ),
				),
			),
			'hubspot'            => array(
				'provider'   => 'HubSpot, Inc.',
				'category'   => 'analytics',
				'signatures' => array( 'js.hs-scripts.com', 'js.hsforms.net', 'hs-analytics.net' ),
				'cookies'    => array(
					array( 'name' => '__hstc', 'domain' => '', 'duration' => '6 months', 'description' => 'HubSpot sets this main cookie for tracking visitors. It contains the domain, initial timestamp, last timestamp, current timestamp and session number.' ),
					array( 'name' => 'hubspotutk', 'domain' => '', 'duration' => '6 months', 'description' => 'HubSpot sets this cookie to keep track of the visitors to the website. This cookie is passed to HubSpot on form submission and used when deduplicating contacts.' ),
					array( 'name' => '__hssc', 'domain' => '', 'duration' => '1 hour', 'description' => 'HubSpot sets this cookie to keep track of sessions and to determine if HubSpot should increment the session number and timestamps in the __hstc cookie.' ),
				),
			),
			'stripe'             => array(
				'provider'   => 'Stripe, Inc.',
				'category'   => 'necessary',
				'signatures' => array( 'js.stripe.com', 'stripe.com/v3' ),
				'cookies'    => array(
					array( 'name' => '__stripe_mid', 'domain' => '', 'duration' => '1 year', 'description' => 'Stripe sets this cookie for fraud prevention purposes. It identifies the device used to access the website, allowing the website to be formatted accordingly.' ),
					array( 'name' => '__stripe_sid', 'domain' => '', 'duration' => '30 minutes', 'description' => 'Stripe sets this cookie for fraud prevention purposes. It identifies the device used to access the website.' ),
				),
			),
			'cloudflare'         => array(
				'provider'   => 'Cloudflare, Inc.',
				'category'   => 'necessary',
				'signatures' => array( 'cd-cgi/challenge-platform', 'cloudflare.com', 'cdnjs.cloudflare.com' ),
				'cookies'    => array(
					array( 'name' => '__cf_bm', 'domain' => '', 'duration' => '30 minutes', 'description' => 'Cloudflare set the cookie to support Cloudflare Bot Management.' ),
					array( 'name' => 'cf_clearance', 'domain' => '', 'duration' => '1 year', 'description' => 'Cloudflare sets this cookie to store the proof of challenges passed by the visitor.' ),
				),
			),
			'wordpress-core'     => array(
				'provider'   => 'WordPress.org',
				'category'   => 'necessary',
				'signatures' => array(), // Always present; added to defaults explicitly.
				'cookies'    => array(
					array( 'name' => 'wordpress_test_cookie', 'domain' => '', 'duration' => 'session', 'description' => 'WordPress sets this cookie to determine whether cookies are enabled on the user\'s browser.' ),
					array( 'name' => 'wp-settings-*', 'domain' => '', 'duration' => '1 year', 'description' => 'WordPress sets this cookie to customise the view of the admin interface and the main site interface.' ),
				),
			),
			'spc-self'           => array(
				'provider'   => get_bloginfo( 'name' ),
				'category'   => 'necessary',
				'signatures' => array(),
				'cookies'    => array(
					array( 'name' => 'spc_consent', 'domain' => '', 'duration' => '1 year', 'description' => 'Stores the visitor\'s cookie consent choices for this website.' ),
				),
			),
		);
	}

	/**
	 * Look up which known service set a given cookie name (supports trailing *).
	 *
	 * @return array|null array{ provider, category, cookie } or null.
	 */
	public static function lookup_cookie( $cookie_name ) {
		foreach ( self::services() as $service ) {
			foreach ( $service['cookies'] as $cookie ) {
				$pattern = $cookie['name'];
				if ( self::name_matches( $pattern, $cookie_name ) ) {
					return array(
						'provider' => $service['provider'],
						'category' => $service['category'],
						'cookie'   => array_merge(
							array( 'domain' => '', 'duration' => '', 'description' => '' ),
							$cookie,
							array( 'name' => $cookie_name )
						),
					);
				}
			}
		}
		return null;
	}

	public static function name_matches( $pattern, $name ) {
		if ( $pattern === $name ) {
			return true;
		}
		if ( substr( $pattern, -1 ) === '*' ) {
			$prefix = substr( $pattern, 0, -1 );
			return $prefix !== '' && strpos( $name, $prefix ) === 0;
		}
		// Also treat a known "_ga_*" style as matching "_ga_ABC123".
		return false;
	}

	/**
	 * Default cookie table seeded on activation.
	 */
	public static function default_cookie_table() {
		$table = array(
			'necessary'     => array(),
			'functional'    => array(),
			'analytics'     => array(),
			'advertisement' => array(),
			'others'        => array(),
		);
		foreach ( array( 'wordpress-core', 'spc-self' ) as $key ) {
			$service = self::services()[ $key ];
			foreach ( $service['cookies'] as $cookie ) {
				$table[ $service['category'] ][] = array_merge(
					array( 'domain' => '', 'duration' => '', 'description' => '', 'provider' => $service['provider'] ),
					$cookie
				);
			}
		}
		return $table;
	}
}
