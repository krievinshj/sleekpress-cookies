<?php
/**
 * SleekPress UI — server-side loader for a plugin's Vue admin app.
 *
 * Vendored from the @sleekpress/ui package. Keep this file in sync via the
 * package's bin/sync.sh; do not edit per-plugin.
 *
 * @package SleekPressUI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SleekPress_UI' ) ) :

class SleekPress_UI {

	/**
	 * Enqueue a built admin app bundle and print its boot config.
	 *
	 * @param array $args {
	 *     @type string $handle       Script/style handle base, e.g. 'spc-admin'.
	 *     @type string $dist_dir     Absolute path to the build output dir (admin.js / admin.css live here).
	 *     @type string $dist_url     URL to that same dir.
	 *     @type string $asset_name   Build base name (default 'admin' → admin.js / admin.css).
	 *     @type string $version      Version string for cache-busting.
	 *     @type string $config_var   JS global to receive the config object, e.g. 'SPCAdmin'.
	 *     @type array  $config        Data to expose to the app (restBase, nonce, etc. are merged in).
	 *     @type string $rest_namespace REST namespace, e.g. 'spc/v1' (used to build restBase).
	 * }
	 * @return bool True if the bundle was found and enqueued.
	 */
	public static function enqueue_app( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'handle'         => 'sleekpress-admin',
				'dist_dir'       => '',
				'dist_url'       => '',
				'asset_name'     => 'admin',
				'version'        => '1.0.0',
				'config_var'     => 'SleekPressApp',
				'config'         => array(),
				'rest_namespace' => '',
			)
		);

		$js_path  = trailingslashit( $args['dist_dir'] ) . $args['asset_name'] . '.js';
		$css_path = trailingslashit( $args['dist_dir'] ) . $args['asset_name'] . '.css';

		if ( ! file_exists( $js_path ) ) {
			return false;
		}

		$js_url  = trailingslashit( $args['dist_url'] ) . $args['asset_name'] . '.js';
		$css_url = trailingslashit( $args['dist_url'] ) . $args['asset_name'] . '.css';

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style( $args['handle'], $css_url, array(), $args['version'] );
		}
		wp_enqueue_script( $args['handle'], $js_url, array(), $args['version'], true );

		$config = array_merge(
			array(
				// Relative URLs on purpose: an absolute rest_url() can resolve to
				// a host/scheme that differs from the page being viewed (common on
				// local dev), which makes the request cross-origin and drops the
				// auth cookie -> 403. A path-relative URL is always same-origin.
				'restBase'  => self::relative_url( $args['rest_namespace'] ? rest_url( $args['rest_namespace'] ) : rest_url() ),
				'restRoot'  => self::relative_url( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'adminUrl'  => admin_url(),
				'pluginUrl' => $args['dist_url'],
			),
			(array) $args['config']
		);

		wp_add_inline_script(
			$args['handle'],
			'window.' . preg_replace( '/[^A-Za-z0-9_]/', '', $args['config_var'] ) . ' = ' . wp_json_encode( $config ) . ';',
			'before'
		);

		return true;
	}

	/**
	 * Strip scheme + host from a URL, keeping path (+ query). Handles both the
	 * pretty (/wp-json/...) and the ?rest_route= forms.
	 */
	private static function relative_url( $url ) {
		$parts = wp_parse_url( $url );
		if ( false === $parts ) {
			return $url;
		}
		$out = isset( $parts['path'] ) ? $parts['path'] : '/';
		if ( ! empty( $parts['query'] ) ) {
			$out .= '?' . $parts['query'];
		}
		return $out;
	}

	/**
	 * Render the mount point + a graceful fallback if the bundle is missing.
	 *
	 * @param string $mount_id  The element id the Vue app mounts on.
	 * @param bool   $built      Whether enqueue_app() found a bundle.
	 * @param string $build_hint Path/command shown when not built.
	 */
	public static function render_root( $mount_id, $built, $build_hint = 'npm install && npm run build' ) {
		if ( ! $built ) {
			printf(
				'<div class="notice notice-error"><p><strong>%s</strong> %s <code>%s</code></p></div>',
				esc_html__( 'Admin UI not built.', 'sleekpress-cookies' ),
				esc_html__( 'Run the build step in the plugin directory:', 'sleekpress-cookies' ),
				esc_html( $build_hint )
			);
			return;
		}
		printf( '<div id="%s" class="sleekpress-ui-root"><noscript>%s</noscript></div>', esc_attr( $mount_id ), esc_html__( 'This screen requires JavaScript.', 'sleekpress-cookies' ) );
	}
}

endif;
