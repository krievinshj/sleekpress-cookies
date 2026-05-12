<?php
/**
 * admin-ajax.php bridge for the Vue admin app.
 *
 * The browser fetches /wp-admin/admin-ajax.php (which is under the /wp-admin/
 * cookie path, so the auth cookie is reliably sent — unlike /wp-json/, whose
 * cookie auth can be flaky on some local/dev setups). This handler verifies a
 * plain admin-ajax nonce + the manage_options capability, then dispatches the
 * request internally to the existing /spc/v1/* REST routes via rest_do_request()
 * — no duplicated controller logic, and the REST routes' own permission_callback
 * still runs.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Ajax {

	// Used as both the admin-ajax action name and the nonce action (the UI
	// loader creates the nonce with wp_create_nonce( ajax_action )).
	const ACTION = 'spc_api';

	public function __construct() {
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'handle' ) );
	}

	public function handle() {
		check_ajax_referer( self::ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array( 'code' => 'rest_forbidden', 'message' => __( 'Insufficient permissions.', 'sleekpress-cookies' ) ), 403 );
		}

		$route  = isset( $_POST['route'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['route'] ) ), '/' ) : '';
		$method = isset( $_POST['method'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['method'] ) ) ) : 'GET';

		if ( '' === $route || ! preg_match( '#^[A-Za-z0-9/_\-]+$#', $route ) ) {
			wp_send_json( array( 'code' => 'spc_bad_route', 'message' => 'Bad route.' ), 400 );
		}
		if ( ! in_array( $method, array( 'GET', 'POST', 'PUT', 'DELETE' ), true ) ) {
			$method = 'GET';
		}

		$payload = array();
		if ( isset( $_POST['payload'] ) ) {
			$decoded = json_decode( wp_unslash( $_POST['payload'] ), true );
			if ( is_array( $decoded ) ) {
				$payload = $decoded;
			}
		}

		$request = new WP_REST_Request( $method, '/' . SPC_Rest::NS . '/' . $route );
		if ( ! empty( $payload ) ) {
			$request->set_body_params( $payload );
			$request->set_query_params( $payload ); // so GET-style params work too
			$request->set_header( 'Content-Type', 'application/json' );
		}

		$response = rest_do_request( $request );
		$status   = $response->get_status();
		$data     = $response->get_data();

		// Mirror the REST status so the JS client treats errors the same way.
		status_header( $status ?: 200 );
		wp_send_json( $data, $status ?: 200 );
	}
}
