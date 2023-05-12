<?php

namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Leads {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'leads_page' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function leads_page() {
		$hook_suffix = add_submenu_page(
			'mini-audit',
			__( 'Leads' ),
			__( 'Leads' ),
			'manage_options',
			'mini-audit-leads',
			[ $this, 'leads_page_html' ]
		);

		add_action( "load-{$hook_suffix}", [ $this, 'bulk_actions' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function leads_page_html() {
		$_wp_list_table = new Leads_Table();
		$_wp_list_table->prepare_items();

		$bulk_counts = array(
			'deleted' => isset( $_REQUEST['deleted'] ) ? absint( $_REQUEST['deleted'] ) : 0,
		);

		$bulk_messages         = array();
		$bulk_messages['lead'] = array(
			'deleted' => _n( '%s lead deleted.', '%s leads permanently deleted.', $bulk_counts['deleted'] ),
		);

		$bulk_counts = array_filter( $bulk_counts );

		$messages = array();
		foreach ( $bulk_counts as $message => $count ) {
			if ( isset( $bulk_messages['lead'][ $message ] ) ) {
				$messages[] = sprintf( $bulk_messages['lead'][ $message ], number_format_i18n( $count ) );
			}
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo __( 'Leads' ); ?></h1>
			<?php
			if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) { ?>
                <span class="subtitle">Search results for: <strong><?php echo esc_html( $_REQUEST['s'] ); ?></strong></span>
				<?php
			}

			if ( $messages ) { ?>
                <div id="message" class="updated notice is-dismissible">
                    <p><?php echo esc_html( implode( ' ', $messages ) ); ?></p></div>
				<?php
			}
			unset( $messages );
			?>
            <form method="get">
                <input type="hidden" name="page" value="mini-audit-leads">
				<?php
				$_wp_list_table->search_box( __( 'Search Leads' ), 'search-leads' );
				$_wp_list_table->display();
				?>
            </form>
        </div>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function bulk_actions() {
		$_wp_list_table = new Leads_Table();

		$pagenum  = $_wp_list_table->get_pagenum();
		$doaction = $_wp_list_table->current_action();

		if ( $doaction ) {
			check_admin_referer( 'bulk-leads' );

			$sendback = remove_query_arg( array( 'deleted', 'ids' ), wp_get_referer() );
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );

			$lead_ids = array();
			if ( ! empty( $_REQUEST['lead'] ) ) {
				$lead_ids = array_map( 'intval', $_REQUEST['lead'] );
			}

			if ( ( 'delete' === $doaction ) ) {
				$deleted = 0;

				foreach ( (array) $lead_ids as $lead_id ) {
					delete_lead( $lead_id );
					$deleted ++;
				}

				$sendback = add_query_arg( 'deleted', $deleted, $sendback );
			}

			$sendback = remove_query_arg( array( 'action', 'action2' ), $sendback );

			wp_safe_redirect( $sendback );
			exit();
		} elseif ( isset( $_REQUEST['export_all'] ) && - 1 != $_REQUEST['export_all'] ) {
			$this->bulk_actions_export_all();
		} elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array(
				'_wp_http_referer',
				'_wpnonce'
			), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit();
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function bulk_actions_export_all() {
		check_admin_referer( 'bulk-leads' );

		$mini_audit_leads = ( new Query_Builder() )
			->table( 'mini_audit_leads' )
			->get();

		if ( ! empty( $mini_audit_leads['results'] ) ) {
			header( 'Content-type: text/csv' );
			header( 'Content-Disposition: attachment; filename=audit_leads.csv' );

			$fp = fopen( 'php://output', 'w' );

			fputcsv(
				$fp,
				[
					__( 'ID' ),
					__( 'Name' ),
					__( 'Last Name' ),
					__( 'Email' ),
					__( 'Phone' ),
					__( 'Keyword' ),
					__( 'Location' ),
					__( 'Place ID' ),
					__( 'Place Name' ),
					__( 'Place Address' ),
					__( 'Place Postal Code' ),
					__( 'Place Lat' ),
					__( 'place Lng' ),
					__( 'Place URL' ),
					__( 'Created At' ),
					__( 'Updated At' ),
				]
			);

			foreach ( $mini_audit_leads['results'] as $lead ) {
				fputcsv( $fp, [
					sanitize_text_field( $lead->id ),
					sanitize_text_field( $lead->name ),
					sanitize_text_field( $lead->last_name ),
					sanitize_text_field( $lead->email ),
					sanitize_text_field( $lead->phone ),
					sanitize_text_field( $lead->keyword ),
					sanitize_text_field( $lead->location ),
					sanitize_text_field( $lead->place_id ),
					sanitize_text_field( $lead->place_name ),
					sanitize_text_field( $lead->place_address ),
					sanitize_text_field( $lead->place_postal_code ),
					sanitize_text_field( $lead->place_lat ),
					sanitize_text_field( $lead->place_lng ),
					sanitize_text_field( $lead->place_url ),
					sanitize_text_field( $lead->created_at ),
					sanitize_text_field( $lead->updated_at ),
				] );
			}

			fclose( $fp );
			exit();
		}
	}
}

new Leads();
