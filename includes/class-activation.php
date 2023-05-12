<?php

namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Activation {
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function activate() {
		global $mini_audit_db_version;
		$mini_audit_db_version = '1.0';

		if ( is_ssl() ) {
			self::install();

			update_option( 'mini_audit_do_activation_redirect', true );
			update_option( 'mini_audit_permalinks_flushed', false );
		} else {
			deactivate_plugins( plugin_basename( GMB_CRUSH_MINI_AUDIT_FILE ) );
			wp_die(
				sprintf(
					__( 'This plugin requires SSL to function properly. Please activate SSL, then try <a href="%s">reactivating</a> the plugin again.' ),
					admin_url( 'plugins.php' )
				)
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	static function deactivate() {
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function install() {
		global $wpdb;
		global $mini_audit_db_version;

		$table_name = $wpdb->prefix . 'mini_audit_leads';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(50) DEFAULT '' NOT NULL,
			last_name varchar(50) DEFAULT '' NOT NULL,
			email varchar(50) DEFAULT '' NOT NULL,
			phone varchar(50) DEFAULT '' NOT NULL,
			keyword varchar(255) DEFAULT '' NOT NULL,
			location varchar(255) DEFAULT '' NOT NULL,
			place_name varchar(255) DEFAULT '' NOT NULL,
			place_id varchar(255) DEFAULT '' NOT NULL,
			place_address varchar(255) DEFAULT '' NOT NULL,
			place_postal_code varchar(50) DEFAULT '' NOT NULL,
			place_lat varchar(100) DEFAULT '' NOT NULL,
			place_lng varchar(100) DEFAULT '' NOT NULL,
			place_url varchar(100) DEFAULT '' NOT NULL,
			initial_report longtext DEFAULT '',
			report longtext DEFAULT '',
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		add_option( 'mini_audit_db_version', $mini_audit_db_version );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function insert_dummy_records() {
		$dummy_leads_list = dummy_leads_list();

		foreach ( $dummy_leads_list as $lead ) {
			create_lead( $lead );
		}
	}
}
