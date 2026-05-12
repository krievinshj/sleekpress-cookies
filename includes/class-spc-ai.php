<?php
/**
 * OpenAI-backed categorisation / description helper for discovered cookies.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_AI {

	public static function is_configured() {
		return '' !== trim( (string) SPC_Settings::get_value( 'openai_api_key' ) );
	}

	/**
	 * Ask the model to assign a category and write a plain-English description
	 * for each cookie row passed in.
	 *
	 * @param array $cookies List of array{ name, domain, duration, provider, description }.
	 * @return array|WP_Error List of array{ name, category, provider, description }.
	 */
	public static function categorize( array $cookies ) {
		$key = trim( (string) SPC_Settings::get_value( 'openai_api_key' ) );
		if ( '' === $key ) {
			return new WP_Error( 'spc_no_api_key', __( 'No OpenAI API key configured.', 'sleekpress-cookies' ) );
		}
		if ( empty( $cookies ) ) {
			return array();
		}

		$model = SPC_Settings::get_value( 'openai_model', 'gpt-4o-mini' );

		$categories = array_keys( SPC_Settings::categories() );

		$system = 'You are a privacy/GDPR assistant. For each browser cookie you are given, return a JSON object. '
			. 'Classify it into exactly one of these categories: ' . implode( ', ', $categories ) . '. '
			. 'Use "necessary" for strictly functional/session/security cookies, "functional" for preferences and embedded media, '
			. '"analytics" for measurement/statistics, "advertisement" for marketing/retargeting, and "others" only if genuinely unknown. '
			. 'Also write a concise, factual, one or two sentence description of what the cookie does and who sets it, '
			. 'written for a public cookie policy. If you can infer the provider/company, fill the "provider" field, otherwise leave it as the given value.';

		$payload_cookies = array();
		foreach ( $cookies as $c ) {
			$payload_cookies[] = array(
				'name'     => (string) ( $c['name'] ?? '' ),
				'domain'   => (string) ( $c['domain'] ?? '' ),
				'duration' => (string) ( $c['duration'] ?? '' ),
				'provider' => (string) ( $c['provider'] ?? '' ),
			);
		}

		$user = "Cookies to classify (JSON):\n" . wp_json_encode( $payload_cookies )
			. "\n\nRespond ONLY with a JSON object of the form {\"cookies\":[{\"name\":\"...\",\"category\":\"...\",\"provider\":\"...\",\"description\":\"...\"}, ...]} keeping the same names.";

		$body = array(
			'model'           => $model,
			'messages'        => array(
				array( 'role' => 'system', 'content' => $system ),
				array( 'role' => 'user', 'content' => $user ),
			),
			'temperature'     => 0.2,
			'response_format' => array( 'type' => 'json_object' ),
		);

		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'timeout' => 45,
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );
		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error( 'spc_openai_http', sprintf( __( 'OpenAI request failed (HTTP %d): %s', 'sleekpress-cookies' ), $code, wp_strip_all_tags( $raw ) ) );
		}

		$decoded = json_decode( $raw, true );
		$content = $decoded['choices'][0]['message']['content'] ?? '';
		$parsed  = json_decode( (string) $content, true );
		if ( ! is_array( $parsed ) || empty( $parsed['cookies'] ) || ! is_array( $parsed['cookies'] ) ) {
			return new WP_Error( 'spc_openai_parse', __( 'Could not parse the AI response.', 'sleekpress-cookies' ) );
		}

		$valid_cats = array_keys( SPC_Settings::categories() );
		$out        = array();
		foreach ( $parsed['cookies'] as $row ) {
			$cat = isset( $row['category'] ) && in_array( $row['category'], $valid_cats, true ) ? $row['category'] : 'others';
			$out[] = array(
				'name'        => sanitize_text_field( $row['name'] ?? '' ),
				'category'    => $cat,
				'provider'    => sanitize_text_field( $row['provider'] ?? '' ),
				'description' => sanitize_textarea_field( $row['description'] ?? '' ),
			);
		}
		return $out;
	}
}
