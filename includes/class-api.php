<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class API {
	public function __construct() {
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $body
	 * @return void
	 */
	public function mini_audit( $body ) {
		$connect_credentials = get_mini_audit_connect_credentials();
		if ( ! isset( $connect_credentials['accessToken'] ) || ( '' === $connect_credentials['accessToken'] ) ) {
			return new \WP_Error( 'invalid_token', __( 'Invalid access token.', 'mini-audit' ) );
		}

		try {
			$url  = 'https://staging-api.gmbcrush.com/competitors-lists/mini-audit';
			$args = [
				'method'  => 'POST',
				'body'    => $body,
				'headers' => array(
					'Authorization' => 'Bearer ' . $connect_credentials['accessToken'],
				),
				'timeout' => 3000,
			];

			$response = wp_remote_post( $url, $args );

			if ( ( ! is_wp_error( $response ) ) && ( 201 === wp_remote_retrieve_response_code( $response ) ) ) {
				$body = json_decode( $response['body'] );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					return $body;
				}
			}

			return $response;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'error', $e->getMessage() );
		}

		return new \WP_Error( 'error', __( 'Sorry, something went wrong! Please try again later.', 'mini-audit' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function get_locations( $query = 'London' ) {
		$connect_credentials = get_mini_audit_connect_credentials();
		if ( ! isset( $connect_credentials['accessToken'] ) || ( '' === $connect_credentials['accessToken'] ) ) {
			return new \WP_Error( 'invalid_token', __( 'Invalid access token.', 'mini-audit' ) );
		}

		try {
			$url  = add_query_arg( 'query', $query, 'https://staging-api.gmbcrush.com/geocode' );
			$args = [
				'headers' => array(
					'Authorization' => 'Bearer ' . $connect_credentials['accessToken'],
				),
				'timeout' => 3000,
			];

			$response = wp_remote_get( $url, $args );

			if ( ( ! is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
				$body = json_decode( $response['body'] );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					return $body;
				}
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'error', $e->getMessage() );
		}

		return new \WP_Error( 'error', __( 'Sorry, something went wrong! Please try again later.', 'mini-audit' ) );
	}


	/**
	 * Undocumented function
	 *
	 * @param string $input
	 * @param string $session_token
	 * @return void
	 */
	function get_places( $input = '', $session_token = '' ) {
		$connect_credentials = get_mini_audit_connect_credentials();
		if ( ! isset( $connect_credentials['accessToken'] ) || ( '' === $connect_credentials['accessToken'] ) ) {
			return new \WP_Error( 'invalid_token', __( 'Invalid access token.', 'mini-audit' ) );
		}

		$transient_key = 'mini_audit_places_' . sanitize_title( $input );
		$places        = get_transient( $transient_key );

		if ( $places ) {
			return $places;
		}

		try {
			$url = add_query_arg(
				array(
					'input'        => $input,
					'sessionToken' => $session_token,
				),
				'https://staging-api.gmbcrush.com/autocomplete'
			);

			$args = [
				'headers' => array(
					'Authorization' => 'Bearer ' . $connect_credentials['accessToken'],
				),
				'timeout' => 3000,
			];

			$response = wp_remote_get( $url, $args );

			if ( ( ! is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
				$body = json_decode( $response['body'] );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					set_transient( $transient_key, $body, DAY_IN_SECONDS );
					return $body;
				}
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'error', $e->getMessage() );
		}

		return new \WP_Error( 'error', __( 'Sorry, something went wrong! Please try again later.', 'mini-audit' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param string $place_id
	 * @param string $session_token
	 * @return void
	 */
	function get_place_details( $place_id = '', $session_token = '' ) {
		$connect_credentials = get_mini_audit_connect_credentials();
		if ( ! isset( $connect_credentials['accessToken'] ) || ( '' === $connect_credentials['accessToken'] ) ) {
			return new \WP_Error( 'invalid_token', __( 'Invalid access token.', 'mini-audit' ) );
		}

		$transient_key = 'mini_audit_place_details_' . sanitize_title( $place_id );
		$place_details = get_transient( $transient_key );

		if ( $place_details ) {
			return $place_details;
		}

		try {
			$url = add_query_arg(
				array(
					'placeId'      => $place_id,
					'sessionToken' => $session_token,
				),
				'https://staging-api.gmbcrush.com/place-details'
			);

			$args = [
				'headers' => array(
					'Authorization' => 'Bearer ' . $connect_credentials['accessToken'],
				),
				'timeout' => 3000,
			];

			$response = wp_remote_get( $url, $args );

			if ( ( ! is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
				$body = json_decode( $response['body'] );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					set_transient( $transient_key, $body, DAY_IN_SECONDS );
					return $body;
				}
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'error', $e->getMessage() );
		}

		return new \WP_Error( 'error', __( 'Sorry, something went wrong! Please try again later.', 'mini-audit' ) );
	}
}

