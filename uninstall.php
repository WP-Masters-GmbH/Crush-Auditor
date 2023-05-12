<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;

delete_option( 'mini_audit_db_version' );
delete_option( 'mini_audit_connect_options' );
delete_option( 'mini_audit_connect_credentials' );
delete_option( 'mini_audit_disconnect_options' );
delete_option( 'mini_audit_options' );
delete_option( 'mini_audit_permalinks_flushed' );

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mini_audit_leads" );
