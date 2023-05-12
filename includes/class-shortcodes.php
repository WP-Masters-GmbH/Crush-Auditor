<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Shortcodes {
	public function __construct() {
		add_action( 'init', [ $this, 'register_shortcodes' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'mini-audit', [ $this, 'mini_audit_html' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$asset_file         = include( plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . 'build/main.asset.php' );
		$mini_audit_options = get_mini_audit_options();

		wp_register_script(
			'axios',
			'https://unpkg.com/axios/dist/axios.min.js',
			[],
			'1.3.3',
			true
		);

		wp_register_script(
			'mini-audit',
			plugins_url( 'build/main.js', GMB_CRUSH_MINI_AUDIT_FILE ),
			array_merge( $asset_file['dependencies'], [ 'axios' ] ),
			$asset_file['version'],
			true
		);

		$session_token = uniqid();

		wp_localize_script(
			'mini-audit',
			'mini_audit',
			array(
				'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
				'autocomplete_places_url' => add_query_arg(
					array(
						'action'        => 'mini_audit_places',
						'session_token' => $session_token,
					),
					admin_url( 'admin-ajax.php' )
				),
				'security'                => wp_create_nonce( 'mini-audit' ),
				'session_token'           => $session_token,
				'script_debug'            => SCRIPT_DEBUG,
				'hero_status'             => $mini_audit_options['mini_audit_options_hero_status'],
				'hero_title'              => $mini_audit_options['mini_audit_options_hero_title'],
				'hero_description'        => $mini_audit_options['mini_audit_options_hero_description'],
				'i18n'                    => [
					'continue_button_text' => $mini_audit_options['mini_audit_continue_button_text'],
					'audit_button_text'    => $mini_audit_options['mini_audit_audit_button_text'],
				],
			)
		);

		wp_register_style(
			'mini-audit-phone-number-input',
			plugins_url( 'build/style-main.css', GMB_CRUSH_MINI_AUDIT_FILE ),
			[],
			$asset_file['version']
		);

		wp_register_style(
			'mini-audit',
			plugins_url( 'build/main.css', GMB_CRUSH_MINI_AUDIT_FILE ),
			[ 'google-fonts', 'mini-audit-phone-number-input' ],
			$asset_file['version']
		);

		$custom_css = "
		.mini-audit {
			--continue-button-background-color: {$mini_audit_options['mini_audit_continue_button_background_color']};
			--continue-button-color: {$mini_audit_options['mini_audit_continue_button_color']};
			--audit-button-background-color: {$mini_audit_options['mini_audit_audit_button_background_color']};
			--audit-button-color: {$mini_audit_options['mini_audit_audit_button_color']};
		}";

		wp_add_inline_style( 'mini-audit', $custom_css );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function mini_audit_html() {
		wp_enqueue_script( 'mini-audit' );
		wp_enqueue_style( 'mini-audit' );

		return '<div id="mini-audit">Loading...</div>';
	}
}

new Shortcodes();

