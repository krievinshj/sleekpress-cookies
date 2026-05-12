<?php
/**
 * Cookie scanner: matches known third-party scripts found in page HTML against
 * the bundled cookie database, and merges in cookies observed in real visitors'
 * browsers (reported via the front-end REST endpoint).
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Scanner {

	const OPT_OBSERVED = 'spc_observed_cookies';
	const OPT_LAST_SCAN = 'spc_last_scan';

	/**
	 * URLs to fetch when scanning. Home page plus a few recent posts/pages.
	 *
	 * @return string[]
	 */
	public static function scan_urls() {
		$urls = array( home_url( '/' ) );

		$page_on_front = (int) get_option( 'page_on_front' );
		if ( $page_on_front ) {
			$urls[] = get_permalink( $page_on_front );
		}

		$recent = get_posts(
			array(
				'numberposts' => 5,
				'post_type'   => array( 'post', 'page' ),
				'post_status' => 'publish',
				'orderby'     => 'modified',
				'order'       => 'DESC',
			)
		);
		foreach ( $recent as $p ) {
			$urls[] = get_permalink( $p );
		}

		// Privacy policy / contact often have embeds & maps.
		$privacy = get_option( 'wp_page_for_privacy_policy' );
		if ( $privacy ) {
			$urls[] = get_permalink( $privacy );
		}

		return array_values( array_unique( array_filter( $urls ) ) );
	}

	/**
	 * Run a scan. Returns the discovered cookie list (not yet saved).
	 *
	 * @return array{ services: array, cookies: array, scanned_urls: array, errors: array }
	 */
	public static function scan() {
		$services_db    = SPC_Cookie_DB::services();
		$found_services = array();
		$errors         = array();
		$scanned        = array();

		foreach ( self::scan_urls() as $url ) {
			$res = wp_remote_get(
				$url,
				array(
					'timeout'    => 20,
					'user-agent' => 'SleekPressCookies/' . SPC_VERSION . ' (+cookie scanner)',
				)
			);
			if ( is_wp_error( $res ) ) {
				$errors[] = sprintf( '%s: %s', $url, $res->get_error_message() );
				continue;
			}
			$scanned[] = $url;
			$html      = (string) wp_remote_retrieve_body( $res );
			if ( '' === $html ) {
				continue;
			}
			foreach ( $services_db as $slug => $service ) {
				if ( isset( $found_services[ $slug ] ) || empty( $service['signatures'] ) ) {
					continue;
				}
				foreach ( $service['signatures'] as $sig ) {
					if ( '' !== $sig && false !== stripos( $html, $sig ) ) {
						$found_services[ $slug ] = $service;
						break;
					}
				}
			}
		}

		// Always include the always-on services.
		foreach ( array( 'wordpress-core', 'spc-self' ) as $slug ) {
			if ( isset( $services_db[ $slug ] ) ) {
				$found_services[ $slug ] = $services_db[ $slug ];
			}
		}

		// Build the discovered cookie list from matched services.
		$cookies = array();
		foreach ( $found_services as $slug => $service ) {
			foreach ( $service['cookies'] as $cookie ) {
				$cookies[ $cookie['name'] ] = array(
					'name'        => $cookie['name'],
					'domain'      => $cookie['domain'] ?? '',
					'duration'    => $cookie['duration'] ?? '',
					'description' => $cookie['description'] ?? '',
					'provider'    => $service['provider'],
					'category'    => $service['category'],
					'source'      => 'database',
				);
			}
		}

		// Merge cookies observed in visitors' browsers.
		foreach ( self::get_observed() as $name => $meta ) {
			if ( isset( $cookies[ $name ] ) ) {
				continue;
			}
			$lookup = SPC_Cookie_DB::lookup_cookie( $name );
			if ( $lookup ) {
				$cookies[ $name ] = array(
					'name'        => $name,
					'domain'      => $lookup['cookie']['domain'] ?? '',
					'duration'    => $lookup['cookie']['duration'] ?? '',
					'description' => $lookup['cookie']['description'] ?? '',
					'provider'    => $lookup['provider'],
					'category'    => $lookup['category'],
					'source'      => 'observed+db',
				);
			} else {
				$cookies[ $name ] = array(
					'name'        => $name,
					'domain'      => $meta['domain'] ?? '',
					'duration'    => '',
					'description' => '',
					'provider'    => '',
					'category'    => 'others',
					'source'      => 'observed',
				);
			}
		}

		// Flag which ones are already saved in the cookie table.
		$saved_names = array();
		foreach ( SPC_Settings::cookies() as $rows ) {
			foreach ( $rows as $row ) {
				$saved_names[ $row['name'] ] = true;
			}
		}
		foreach ( $cookies as $name => &$c ) {
			$c['known'] = isset( $saved_names[ $name ] );
		}
		unset( $c );

		ksort( $cookies );

		update_option(
			self::OPT_LAST_SCAN,
			array(
				'time'         => time(),
				'scanned_urls' => $scanned,
				'count'        => count( $cookies ),
			)
		);

		return array(
			'services'     => array_keys( $found_services ),
			'cookies'      => array_values( $cookies ),
			'scanned_urls' => $scanned,
			'errors'       => $errors,
		);
	}

	/**
	 * Record a cookie name seen in a visitor's browser. $names is name => domain.
	 */
	public static function record_observed( array $names ) {
		$observed = self::get_observed();
		$changed  = false;
		foreach ( $names as $name => $domain ) {
			$name = trim( (string) $name );
			if ( '' === $name || strlen( $name ) > 120 ) {
				continue;
			}
			if ( ! isset( $observed[ $name ] ) ) {
				$observed[ $name ] = array(
					'domain'    => sanitize_text_field( (string) $domain ),
					'first_seen' => time(),
				);
				$changed = true;
			}
		}
		if ( $changed ) {
			// Cap the list to avoid runaway growth.
			if ( count( $observed ) > 300 ) {
				$observed = array_slice( $observed, -300, null, true );
			}
			update_option( self::OPT_OBSERVED, $observed, false );
		}
	}

	public static function get_observed() {
		$o = get_option( self::OPT_OBSERVED );
		return is_array( $o ) ? $o : array();
	}

	public static function clear_observed() {
		delete_option( self::OPT_OBSERVED );
	}

	/**
	 * Merge a list of discovered cookie rows into the saved cookie table.
	 * Each row needs: name, category, plus optional domain/duration/description/provider.
	 * Existing rows (same name) are updated in place; category changes move the row.
	 */
	public static function merge_into_table( array $rows ) {
		$table = SPC_Settings::cookies();
		$valid_cats = array_keys( SPC_Settings::categories() );

		// Index existing rows by name with their category.
		$existing = array();
		foreach ( $table as $cat => $list ) {
			foreach ( $list as $i => $row ) {
				$existing[ $row['name'] ] = array( 'cat' => $cat, 'i' => $i );
			}
		}

		foreach ( $rows as $row ) {
			$name = sanitize_text_field( $row['name'] ?? '' );
			if ( '' === $name ) {
				continue;
			}
			$cat = ( isset( $row['category'] ) && in_array( $row['category'], $valid_cats, true ) ) ? $row['category'] : 'others';
			$new_row = array(
				'name'        => $name,
				'domain'      => sanitize_text_field( $row['domain'] ?? '' ),
				'duration'    => sanitize_text_field( $row['duration'] ?? '' ),
				'description' => sanitize_textarea_field( $row['description'] ?? '' ),
				'provider'    => sanitize_text_field( $row['provider'] ?? '' ),
			);
			if ( isset( $existing[ $name ] ) ) {
				$old_cat = $existing[ $name ]['cat'];
				$old_i   = $existing[ $name ]['i'];
				// Keep existing non-empty fields if the new ones are blank.
				$prev = $table[ $old_cat ][ $old_i ];
				foreach ( array( 'domain', 'duration', 'description', 'provider' ) as $f ) {
					if ( '' === $new_row[ $f ] && ! empty( $prev[ $f ] ) ) {
						$new_row[ $f ] = $prev[ $f ];
					}
				}
				if ( $old_cat === $cat ) {
					$table[ $cat ][ $old_i ] = $new_row;
				} else {
					unset( $table[ $old_cat ][ $old_i ] );
					$table[ $old_cat ] = array_values( $table[ $old_cat ] );
					$table[ $cat ][]   = $new_row;
					// Rebuild index since indices shifted.
					$existing = array();
					foreach ( $table as $c => $l ) {
						foreach ( $l as $j => $r ) {
							$existing[ $r['name'] ] = array( 'cat' => $c, 'i' => $j );
						}
					}
				}
			} else {
				$table[ $cat ][] = $new_row;
				$existing[ $name ] = array( 'cat' => $cat, 'i' => count( $table[ $cat ] ) - 1 );
			}
		}

		SPC_Settings::save_cookies( $table );
		return $table;
	}

	public static function last_scan() {
		$s = get_option( self::OPT_LAST_SCAN );
		return is_array( $s ) ? $s : null;
	}
}
