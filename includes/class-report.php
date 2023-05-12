<?php

namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Report {
	public function __construct() {
		add_action( 'init', [ $this, 'add_rewrite_rule' ] );
		add_action( 'query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'template_redirect', [ $this, 'template_redirect' ] );
		add_action( 'template_include', [ $this, 'template_include' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function add_rewrite_rule() {
		add_rewrite_rule( 'mini-audit-report/([a-z0-9-]+)[/]?$', 'index.php?audit_report_id=$matches[1]', 'top' );
		if ( ! get_option( 'mini_audit_permalinks_flushed' ) ) {
			flush_rewrite_rules( false );
			update_option( 'mini_audit_permalinks_flushed', true );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'audit_report_id';

		return $query_vars;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function template_redirect() {
		if ( get_query_var( 'audit_report_id' ) === false || '' === get_query_var( 'audit_report_id' ) ) {
			return;
		}

		$audit_report_id = sanitize_text_field( get_query_var( 'audit_report_id' ) );
		$lead            = ( new Query() )->get_lead( $audit_report_id );

		if ( ! $lead ) {
			wp_safe_redirect( home_url() );
			die;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $template
	 *
	 * @return void
	 */
	public function template_include( $template ) {
		if ( get_query_var( 'audit_report_id' ) === false || '' === get_query_var( 'audit_report_id' ) ) {
			return $template;
		}

		$audit_report_id = sanitize_text_field( get_query_var( 'audit_report_id' ) );
		$lead            = ( new Query() )->get_lead( $audit_report_id );

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$posted_data = json_decode( file_get_contents( 'php://input' ), true );

			$return = [
				'audit_report_id' => $audit_report_id,
				'posted_data'     => $posted_data,
				'updated'         => false,
			];

			if ( ! $lead ) {
				wp_send_json_error( new WP_Error( 'invalid_report_id', 'Invalid report ID, no information was retrieved.' ) );
			}

			if ( isset( $posted_data['id'] ) ) {
				$return['updated'] = ( new Query() )->update_lead(
					$audit_report_id,
					[
						'report' => maybe_serialize( $posted_data ),
					]
				);

				send_mail( $lead );
			}

			wp_send_json_success( $return );
		}

		if ( 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			wp_enqueue_style( 'mini-audit-report' );
			if ( empty( $lead['report'] ) ) {
				return plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/templates/report-empty.php';
			}

			wp_enqueue_script( 'mini-audit-report' );

			return plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/templates/report.php';
		}

		return $template;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$asset_file         = include( plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . 'build/report.asset.php' );
		$mini_audit_options = get_mini_audit_options();

		wp_register_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js',
			[],
			$asset_file['version'],
			true
		);

		wp_register_script(
			'mini-audit-report',
			plugins_url( 'build/report.js', GMB_CRUSH_MINI_AUDIT_FILE ),
			array_merge( $asset_file['dependencies'], [ 'chartjs' ] ),
			$asset_file['version'],
			true
		);

		wp_register_style(
			'mini-audit-report',
			plugins_url( 'build/report.css', GMB_CRUSH_MINI_AUDIT_FILE ),
			[ 'google-fonts' ],
			$asset_file['version']
		);

		$report_variables = '';

		$sections = get_report_sections();
		foreach ( array_keys( $sections ) as $value ) {
			$prefix           = str_replace( '_', '-', $value );
			$background_color = $mini_audit_options[ 'mini_audit_options_' . $value . '_button_background_color' ];
			$button_color     = $mini_audit_options[ 'mini_audit_options_' . $value . '_button_color' ];
			$report_variables .= "--{$prefix}-button-background-color: {$background_color};";
			$report_variables .= "--{$prefix}-button-color: {$button_color};";
		}

		$custom_css = ".mini-audit-report {{$report_variables}}";

		wp_add_inline_style( 'mini-audit-report', $custom_css );
	}
}

new Report();
