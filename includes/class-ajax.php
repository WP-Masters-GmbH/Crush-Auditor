<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class AJAX {
	public function __construct() {
		add_action( 'wp_ajax_mini_audit', [ $this, 'mini_audit' ] );
		add_action( 'wp_ajax_nopriv_mini_audit', [ $this, 'mini_audit' ] );
		add_action( 'wp_ajax_mini_audit_locations', [ $this, 'mini_audit_locations' ] );
		add_action( 'wp_ajax_nopriv_mini_audit_locations', [ $this, 'mini_audit_locations' ] );
		add_action( 'wp_ajax_mini_audit_places', [ $this, 'mini_audit_places' ] );
		add_action( 'wp_ajax_nopriv_mini_audit_places', [ $this, 'mini_audit_places' ] );
		add_action( 'wp_ajax_mini_audit_debug', [ $this, 'mini_audit_debug' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function mini_audit() {
		check_ajax_referer( 'mini-audit', 'security' );

		$connect_credentials = get_mini_audit_connect_credentials();
		if ( ! isset( $connect_credentials['user'], $connect_credentials['user']['firstName'], $connect_credentials['user']['lastName'], $connect_credentials['user']['email'] ) ) {
			$error = new WP_Error( '001', __( 'No user information was retrieved.' ) );
			wp_send_json_error( $error );
		}

		$fields = [
			'name'              => '',
			'last_name'         => '',
			'email'             => '',
			'phone'             => '',
			'keyword'           => '',
			'location'          => '',
			'place_id'          => '',
			'place_name'        => '',
			'place_address'     => '',
			'place_postal_code' => '',
			'place_url'         => '',
			'place_lat'         => '',
			'place_lng'         => '',
		];

		foreach ( array_keys( $fields ) as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$fields[ $field ] = sanitize_text_field( $_POST[ $field ] );
			}
		}

		$session_token = isset( $_POST['session_token'] ) ? sanitize_text_field( $_POST['session_token'] ) : uniqid();
		$place_details = mini_audit()->api->get_place_details( $fields['place_id'], $session_token );

		if ( is_wp_error( $place_details ) ) {
			wp_send_json_error( $place_details );
		}

		$fields['place_name']    = $place_details->result->name;
		$fields['place_address'] = $place_details->result->formatted_address;
		$fields['place_url']     = $place_details->result->url;

		foreach ( $place_details->result->address_components as $address_component ) {
			if ( in_array( 'postal_code', $address_component->types ) ) {
				$fields['place_postal_code'] = $address_component->short_name;
			}
		}

		$lead_id = create_lead( $fields );

		$body = [
			'device'    => 'desktop',
			'username'  => $connect_credentials['user']['firstName'] . ' ' . $connect_credentials['user']['lastName'],
			'email'     => $connect_credentials['user']['email'],
			'query'     => $fields['keyword'],
			'latitude'  => $fields['place_lat'],
			'longitude' => $fields['place_lng'],
			'business'  => [
				'type'     => 'new',
				'name'     => $fields['place_name'],
				'address'  => $fields['place_address'],
				'mapsLink' => $fields['place_url'],
				'placeId'  => $fields['place_id'],
				'zipCode'  => $fields['place_postal_code'],
			],
			'webhook'   => home_url( '/mini-audit-report/' . $lead_id ),
		];

		$mini_audit = mini_audit()->api->mini_audit( $body );
		if ( is_wp_error( $mini_audit ) ) {
			wp_send_json_error( $mini_audit );
		}

		wp_send_json_success(
			[
				'fields'        => $fields,
				'body'          => $body,
				'place_details' => $place_details,
				'lead_id'       => $lead_id,
				'mini_audit'    => $mini_audit,
			]
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function mini_audit_locations() {
		$query     = sanitize_text_field( $_GET['query'] );
		$locations = mini_audit()->api->get_locations( $query );
		if ( is_wp_error( $locations ) ) {
			return [];
		}

		wp_send_json( $locations );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function mini_audit_places() {
		$return = [];

		if ( ! isset( $_GET['q'] ) ) {
			return $return;
		}

		$places = mini_audit()->api->get_places( sanitize_text_field( $_GET['q'] ), uniqid() );

		foreach ( $places->predictions as $prediction ) {
			$return[] = [
				'value' => $prediction->place_id,
				'label' => $prediction->description,
			];
		}

		wp_send_json( $return );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function mini_audit_debug() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( [] );
		}

		delete_option( 'mini_audit_connect_options' );
		delete_option( 'mini_audit_connect_credentials' );

		wp_send_json( [] );

		// $dummy_leads_list = dummy_leads_list();

		// foreach ( $dummy_leads_list as $lead ) {
		// 	$lead_id = create_lead( $lead );

		// 	( new Query() )->update_lead(
		// 		$lead_id,
		// 		[
		// 			'report' => maybe_serialize( mini_audit()->dummy_response() ),
		// 		]
		// 	);
		// }

		// delete_option( 'mini_audit_db_version' );
		// delete_option( 'mini_audit_connect_options' );
		// delete_option( 'mini_audit_connect_credentials' );
		// delete_option( 'mini_audit_disconnect_options' );
		// delete_option( 'mini_audit_options' );

		// $db_query = $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mini_audit_leads" );
		// wp_send_json( [ $db_query ] );

		// $mini_audit = mini_audit()->api->mini_audit( get_dummy_mini_audit_body() );

		// wp_send_json( [ ( new Query() )->get_lead( 1 ) ] );
		// wp_send_json( [ send_mail( ( new Query() )->get_lead( 1 ) ) ] );

		// $connect_credentials = get_mini_audit_connect_credentials();
		// wp_send_json( $connect_credentials );

		wp_send_json( [ get_mini_audit_options() ] );
	}
}

new AJAX();

