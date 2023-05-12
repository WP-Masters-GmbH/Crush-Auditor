<?php

namespace Upnrunn;

/**
 * Undocumented function
 *
 * @param array $args
 *
 * @return void
 */
function create_lead( $args = [] ) {
	global $wpdb;

	$defaults = [
		'name'              => '',
		'last_name'         => '',
		'email'             => '',
		'phone'             => '',
		'keyword'           => '',
		'location'          => '',
		'place_name'        => '',
		'place_id'          => '',
		'place_address'     => '',
		'place_postal_code' => '',
		'place_lat'         => '',
		'place_lng'         => '',
		'place_url'         => '',
		'created_at'        => current_time( 'mysql' ),
		'updated_at'        => current_time( 'mysql' ),
	];

	$args = wp_parse_args( $args, $defaults );

	$table_name = $wpdb->prefix . 'mini_audit_leads';

	$wpdb->insert( $table_name, $args, array(
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s'
	) );

	return $wpdb->insert_id;
}

/**
 * Undocumented function
 *
 * @param integer $lead_id
 *
 * @return void
 */
function delete_lead( $lead_id = 0 ) {
	return ( new Query_Builder() )->table( 'mini_audit_leads' )
	                              ->where( 'id', $lead_id )
	                              ->delete();
}

/**
 * Undocumented function
 *
 * @return array
 */
function dummy_leads_list() {
	return [
		[
			'name'              => 'Kenneth',
			'last_name'         => 'Berg',
			'email'             => 'ParkerEHaas@jourrapide.com',
			'phone'             => '705-997-5835',
			'keyword'           => 'block paving layer',
			'location'          => __( '5 Isaac John St, Ikeja 101233, Lagos, Nigeria' ),
			'place_id'          => 'ChIJAZodwnKSOxAROIJnRuFBnw0',
			'place_name'        => __( 'Tradex Retail Africa' ),
			'place_address'     => __( '5 Isaac John St, Ikeja 101233, Lagos, Nigeria' ),
			'place_postal_code' => '101233',
			'place_lat'         => '6.5761224',
			'place_lng'         => '3.3602636',
			'place_url'         => 'https://maps.google.com/?cid=981575679594693176',
		],
		[
			'name'              => 'Roy',
			'last_name'         => 'Martinez',
			'email'             => 'ParkerEHaas@jourrapide.com',
			'phone'             => '613-598-0398',
			'keyword'           => 'block paving layer',
			'location'          => __( '5 Isaac John St, Ikeja 101233, Lagos, Nigeria' ),
			'place_id'          => 'ChIJAZodwnKSOxAROIJnRuFBnw0',
			'place_name'        => __( 'UBER CAPITAL BANGLADESH' ),
			'place_address'     => __( '5 Isaac John St, Ikeja 101233, Lagos, Nigeria' ),
			'place_postal_code' => '101233',
			'place_lat'         => '6.5761224',
			'place_lng'         => '3.3602636',
			'place_url'         => 'https://maps.google.com/?cid=981575679594693176',
		],
		[
			'name'              => 'Christopher',
			'last_name'         => 'Chavez',
			'email'             => 'ParkerEHaas@jourrapide.com',
			'phone'             => '780-689-0723',
			'keyword'           => 'block paving layer',
			'location'          => __( '5 Isaac John St, Ikeja 101233, Lagos, Nigeria' ),
			'place_id'          => 'ChIJAZodwnKSOxAROIJnRuFBnw0',
			'place_name'        => __( 'Infix Credit | Infix Business Info Limited' ),
			'place_address'     => __( '5 Isaac John St, Ikeja 101233, Lagos, Nigeria' ),
			'place_postal_code' => '101233',
			'place_lat'         => '6.5761224',
			'place_lng'         => '3.3602636',
			'place_url'         => 'https://maps.google.com/?cid=981575679594693176',
		],
	];
}

/**
 * Undocumented function
 *
 * @return void
 */
function prepare_report() {
	$audit_report_id = sanitize_text_field( get_query_var( 'audit_report_id' ) );
	$options         = get_mini_audit_options();
	$lead            = ( new Query() )->get_lead( $audit_report_id );

	if ( $lead && isset( $lead['report'] ) ) {
		$report = maybe_unserialize( $lead['report'] );
		if ( isset( $report['data'], $report['data']['keyword'], $report['data']['device'], $report['data']['location'], $report['data']['competitors'] ) ) {
			$competitors = [];
			foreach ( $report['data']['competitors'] as $key => $value ) {
				$competitors[] = [
					'name'    => $value['name'],
					'rank'    => sprintf( __( '#%d' ), $key + 1 ),
					'claimed' => $value['isClaimed'],
				];
			}

			$rank_absolute = __( '+20' );
			if ( isset( $report['data'], $report['data']['statistics'], $report['data']['statistics']['rankRating'] ) && count( $report['data']['statistics']['rankRating'] ) ) {
				$_rank = intval( $report['data']['statistics']['rankRating'][0][0] );
				if ( $_rank && 20 >= $_rank ) {
					$rank_absolute = sprintf( __( '#%d' ), $_rank );
				}
			}

			$agenda_args = [
				'keyword'     => $report['data']['keyword'],
				'device'      => $report['data']['device'],
				'location'    => $lead['location'],
				'latitude'    => $report['data']['location']['latitude'],
				'longitude'   => $report['data']['location']['longitude'],
				'rank'        => $rank_absolute,
				'competitors' => $competitors,
				'business'    => isset( $report['data']['statistics'], $report['data']['statistics']['categories'], $report['data']['statistics']['categories']['business'] ) ? $report['data']['statistics']['categories']['business'] : '',
				'performance' => [
					'mark_title' => __( 'GBP Health' ),
					'mark'       => __( '0%' ),
					'chartjs'    => '',
				],
				'i18n'        => [
					'performance_compared' => __( 'Google Business Performance compared:' ),
					'verified'             => __( 'Verified' ),
					'keyword'              => __( 'Keyword:' ),
					'location'             => __( 'Location:' ),
					'performance_title'    => __( 'Performance' ),
				],
			];

			if ( isset( $report['data']['statistics'], $report['data']['statistics']['healthScore'] ) ) {
				$agenda_args['performance']['chartjs'] = json_encode(
					[
						'type' => 'doughnut',
						'data' => [
							'labels'   => [
								__( 'On Page' ),
								__( 'Reviews' ),
								__( 'Rating' ),
								__( 'Category Frequency' )
							],
							'datasets' => [
								[
									'data'            => array_slice( (array) $report['data']['statistics']['healthScore'], 0, 4 ),
									'backgroundColor' => [
										'#ff1d05',
										'#0192f5',
										'#9a33c4',
										'#e6b800',
									],
								],
							],
						]
					]
				);

				$agenda_args['performance']['mark'] = sprintf( __( '%s%%' ), number_format( $report['data']['statistics']['healthScore'][4], 2, '.', '' ) );
			}

			echo mini_audit()->render( 'report-agenda.html', $agenda_args );
		}

		$table_items     = [
			[
				'index'   => 1,
				'name'    => 'Acme Inc.',
				'address' => '123 Main St.',
			],
			[
				'index'   => 2,
				'name'    => 'Bravo Ltd.',
				'address' => '456 Market Ave.',
			],
			[
				'index'   => 3,
				'name'    => 'Charlie Corp.',
				'address' => '789 Ocean Blvd.',
			],
			[
				'index'   => 4,
				'name'    => 'Delta Inc.',
				'address' => '246 River Rd.',
			],
			[
				'index'   => 5,
				'name'    => 'Echo Enterprises',
				'address' => '135 Mountain View',
			],
			[
				'index'   => 6,
				'name'    => 'Foxtrot LLC',
				'address' => '678 Sunset Strip',
			],
			[
				'index'   => 7,
				'name'    => 'Golf Co.',
				'address' => '901 Valley Rd.',
			],
			[
				'index'   => 8,
				'name'    => 'Hotel Ltd.',
				'address' => '567 Downtown St.',
			],
			[
				'index'   => 9,
				'name'    => 'India Inc.',
				'address' => '1234 Uptown Ave.',
			],
			[
				'index'   => 10,
				'name'    => 'Juliet Corp.',
				'address' => '9876 Midtown Rd.',
			],
		];
		$report_sections = get_report_sections();
		foreach ( $report_sections as $key => $value ) {
			if ( $options[ 'mini_audit_options_' . $key . '_status' ] ) {
				shuffle( $table_items );
				echo mini_audit()->render(
					'blur.html',
					[
						'id'                       => str_replace( '_', '-', $key ),
						'title'                    => esc_html( $value ),
						'table_caption'            => __( 'Here is the list of dummy items' ),
						'table_columns'            => [ __( 'Index' ), __( 'Name' ), __( 'Address' ) ],
						'table_items'              => $table_items,
						'blur_section_text'        => esc_html( $options[ 'mini_audit_options_' . $key . '_text' ] ),
						'blur_section_button_text' => esc_html( $options[ 'mini_audit_options_' . $key . '_button_text' ] ),
						'blur_section_button_url'  => esc_url( $options[ 'mini_audit_options_' . $key . '_button_url' ] ),
					]
				);
			}
		}
	}
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_mini_audit_connect_options() {
	$default_connect_options = [
		'mini_audit_connect_field_email'    => '',
		'mini_audit_connect_field_password' => '',
	];

	$mini_audit_connect_options = get_option( 'mini_audit_connect_options' );
	if ( ! $mini_audit_connect_options ) {
		return $default_connect_options;
	}

	return $mini_audit_connect_options;
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_mini_audit_connect_credentials() {
	$default_credentials = [
		'accessToken' => '',
		'user'        => [
			'email' => '',
		],
	];

	$mini_audit_connect_credentials = get_option( 'mini_audit_connect_credentials' );
	if ( ! $mini_audit_connect_credentials ) {
		return $default_credentials;
	}

	return $mini_audit_connect_credentials;
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_mini_audit_disconnect_options() {
	$default_disconnect_options = [
		'mini_audit_disconnect_field_status' => 'disconnected',
	];

	$mini_audit_disconnect_options = get_option( 'mini_audit_disconnect_options' );
	if ( ! $mini_audit_disconnect_options ) {
		return $default_disconnect_options;
	}

	return $mini_audit_disconnect_options;
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_mini_audit_default_options() {
	$defaults = [
		'mini_audit_options_mail_from_email'          => get_bloginfo( 'admin_email' ),
		'mini_audit_options_mail_from_name'           => get_bloginfo( 'name' ),
		'mini_audit_options_mail_subject'             => __( 'Your Report is Ready' ),
		'mini_audit_options_mail_content'             => 'Dear [user],

We wanted to inform you that your report has been generated and is now available for viewing. You can access your report by visiting the following link: [report_url]

Please let us know if you have any issues accessing the report or if you have any questions about the information it contains.

Thank you for using our service.

Best regards,
[company]',
		'mini_audit_continue_button_text'             => __( 'Continue' ),
		'mini_audit_audit_button_text'                => __( 'Continue' ),
		'mini_audit_continue_button_background_color' => '#000000',
		'mini_audit_continue_button_color'            => '#ffffff',
		'mini_audit_audit_button_background_color'    => '#000000',
		'mini_audit_audit_button_color'               => '#ffffff',
	];

	$sections = get_report_sections();
	foreach ( array_keys( $sections ) as $value ) {
		$defaults[ 'mini_audit_options_' . $value . '_status' ]                  = 0;
		$defaults[ 'mini_audit_options_' . $value . '_text' ]                    = __( 'Find your opportunities' );
		$defaults[ 'mini_audit_options_' . $value . '_button_text' ]             = __( 'Contact Us' );
		$defaults[ 'mini_audit_options_' . $value . '_button_url' ]              = __( 'https://www.gmbcrush.com/' );
		$defaults[ 'mini_audit_options_' . $value . '_button_background_color' ] = '#000000';
		$defaults[ 'mini_audit_options_' . $value . '_button_color' ]            = '#ffffff';
	}

	$defaults['mini_audit_options_hero_status']      = 0;
	$defaults['mini_audit_options_hero_title']       = __( 'Unlock the power of your Google Business Profile with just a few clicks' );
	$defaults['mini_audit_options_hero_description'] = __( 'Analyze its performance, diagnose why it isn\'t ranking well, and get tips for optimization.' );

	return $defaults;
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_mini_audit_options() {
	$defaults           = get_mini_audit_default_options();
	$mini_audit_options = get_option( 'mini_audit_options' );

	if ( ! $mini_audit_options ) {
		return $defaults;
	}

	return wp_parse_args( $mini_audit_options, $defaults );
}

function get_report_sections() {
	return [
		'categories'                => __( 'Categories' ),
		'ranking'                   => __( 'Ranking' ),
		'business_attributes'       => __( 'Business Attributes' ),
		'google_ad_and_kw_insights' => __( 'Google Ad & Kw Insights' ),
		'zip_code'                  => __( 'Zip Code' ),
		'photos'                    => __( 'Photos' ),
		'posts'                     => __( 'Posts' ),
		'reviews'                   => __( 'Reviews' ),
		'gmb_title_and_description' => __( 'Gmb Title & Description' ),
		'brand_rating'              => __( 'Brand Rating' ),
		'opening_hours'             => __( 'Opening Hours' ),
		'summary'                   => __( 'Summary' ),
	];
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_dummy_mini_audit_body() {
	return [
		'query'     => 'block paving layer',
		'latitude'  => '51.319195',
		'longitude' => '-2.204079',
		'device'    => 'desktop',
		'username'  => 'Plugin Development',
		'email'     => 'gmbcrushplugindev@gmail.com',
		'business'  => [
			'name'     => 'Testaccio market',
			'type'     => 'new',
			'address'  => '03 Gloucester Rd, Trowbridge BA14 0AD',
			'mapsLink' => 'https://www.google.com/maps?cid=12084243359021068232',
			'placeId'  => 'ChIJw6T_Xo3Vc0gRyO_SWU_bs6c',
			'zipCode'  => '00153',
		],
		'webhook'   => 'https://www.staging10.gmbcrush.com/mini-audit-report/393393/',
	];
}

/**
 * Undocumented function
 *
 * @param array $lead
 *
 * @return void
 */
function send_mail( $lead = [] ) {
	$mini_audit_options = get_mini_audit_options();
	$to                 = $lead['email'];
	$subject            = $mini_audit_options['mini_audit_options_mail_subject'];
	$headers            = array( 'Content-Type: text/html; charset=UTF-8' );
	$headers[]          = sprintf( 'From: %s <%s>', $mini_audit_options['mini_audit_options_mail_from_name'], $mini_audit_options['mini_audit_options_mail_from_email'] );

	$link         = home_url( '/mini-audit-report/' . $lead['id'] );
	$mail_content = $mini_audit_options['mini_audit_options_mail_content'];

	ob_start();
	?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>[title]</title>
    </head>
    <body>
	<?php echo wpautop( $mail_content ); ?>
    </body>
    </html>
	<?php
	$body = ob_get_clean();
	$body = str_replace( '[title]', $mini_audit_options['mini_audit_options_mail_subject'], $body );
	$body = str_replace( '[user]', $lead['name'], $body );
	$body = str_replace( '[report_url]', sprintf( '<a href="%s">%s</a>', $link, $link ), $body );
	$body = str_replace( '[company]', get_bloginfo( 'name' ), $body );

	// return $headers;

	return wp_mail( $to, $subject, $body, $headers );
}
