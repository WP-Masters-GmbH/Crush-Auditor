<?php

namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Options {
	private $nav_tabs                  = [];
	private $mini_audit_connect_page   = '';
	private $mini_audit_options_page   = '';
	private $mini_audit_email_page     = '';
	private $mini_audit_shortcode_page = '';
	private $mini_audit_payment_page   = '';

	/**
	 * Undocumented function
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();

	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function setup_globals() {
		$this->nav_tabs = array(
			'0' => array(
				'id'   => 'mini-audit',
				'href' => admin_url( add_query_arg( array( 'page' => 'mini-audit' ), 'admin.php' ) ),
				'name' => __( 'Account', 'mini-audit' ),
			),
			'1' => array(
				'id'   => 'mini-audit-options',
				'href' => admin_url( add_query_arg( array( 'page' => 'mini-audit-options' ), 'admin.php' ) ),
				'name' => __( 'Options', 'mini-audit' ),
			),
			'2' => array(
				'id'   => 'mini-audit-mail',
				'href' => admin_url( add_query_arg( array( 'page' => 'mini-audit-mail' ), 'admin.php' ) ),
				'name' => __( 'Email', 'mini-audit' ),
			),
			'3' => array(
				'id'   => 'mini-audit-shortcode',
				'href' => admin_url( add_query_arg( array( 'page' => 'mini-audit-shortcode' ), 'admin.php' ) ),
				'name' => __( 'Shortcode', 'mini-audit' ),
			),
			'4' => array(
				'id'   => 'mini-audit-payment',
				'href' => admin_url( add_query_arg( array( 'page' => 'mini-audit-payment' ), 'admin.php' ) ),
				'name' => __( 'Payment', 'mini-audit' ),
			),
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function setup_actions() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'options_page' ] );
		add_action( 'added_option', [ $this, 'do_authenticate' ], 10, 2 );
		add_filter( 'pre_update_option', [ $this, 're_authenticate' ], 10, 3 );
		add_filter( 'pre_update_option', [ $this, 'disconnect' ], 10, 3 );
		add_filter( 'admin_body_class', [ $this, 'admin_body_classes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function register_settings() {
		global $plugin_page;

		$sections            = get_report_sections();
		$connect_options     = get_mini_audit_connect_options();
		$disconnect_options  = get_mini_audit_disconnect_options();
		$connect_credentials = get_mini_audit_connect_credentials();
		$options             = get_mini_audit_options();

		// Connect options.
		register_setting( 'mini_audit', 'mini_audit_connect_options' );
		register_setting( 'mini_audit', 'mini_audit_disconnect_options' );
		register_setting(
			'mini_audit',
			'mini_audit_options',
			[
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
			]
		);

		if ( 'mini-audit' === $plugin_page ) {
			if ( ( 'not_disconnected' === $disconnect_options['mini_audit_disconnect_field_status'] ) ||
				( '' !== $connect_credentials['accessToken'] )
			) {
				add_settings_section(
					'mini_audit_disconnect',
					__( 'Your Account' ),
					[ $this, 'mini_audit_disconnect_section_callback' ],
					'mini_audit'
				);

				add_settings_field(
					'mini_audit_disconnect_field_status',
					__( 'Status', 'mini-audit' ),
					[ $this, 'mini_audit_disconnect_field_callback' ],
					'mini_audit',
					'mini_audit_disconnect',
					[
						'type'       => 'hidden',
						'name'       => 'mini_audit_disconnect_field_status',
						'value'      => 'disconnected',
						'class'      => 'mini_audit_disconnect_field',
						'user_email' => $connect_credentials['user']['email'],
					]
				);
			} else {
				add_settings_section(
					'mini_audit_connect',
					__( 'Connect Your Account' ),
					[ $this, 'mini_audit_connect_section_callback' ],
					'mini_audit'
				);

				add_settings_field(
					'mini_audit_connect_field_email',
					__( 'Email', 'mini-audit' ),
					[ $this, 'mini_audit_connect_field_callback' ],
					'mini_audit',
					'mini_audit_connect',
					[
						'type'        => 'email',
						'name'        => 'mini_audit_connect_field_email',
						'value'       => $connect_options['mini_audit_connect_field_email'],
						'placeholder' => __( 'Email Address' ),
						'class'       => 'mini_audit_connect_field',
					]
				);

				add_settings_field(
					'mini_audit_connect_field_password',
					__( 'Password', 'mini-audit' ),
					[ $this, 'mini_audit_connect_field_callback' ],
					'mini_audit',
					'mini_audit_connect',
					[
						'type'        => 'password',
						'name'        => 'mini_audit_connect_field_password',
						'value'       => $connect_options['mini_audit_connect_field_password'],
						'placeholder' => __( 'Password' ),
						'class'       => 'mini_audit_connect_field',
					]
				);

			}
		}

		if ( 'mini-audit-options' === $plugin_page ) {
			add_settings_section(
				'mini_audit_options_hero',
				__( 'Title and Description' ),
				[ $this, 'mini_audit_hero_section_callback' ],
				'mini_audit'
			);

			add_settings_field(
				'mini_audit_options_hero_status',
				__( 'Status', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_hero',
				[
					'type'  => 'switch',
					'name'  => 'mini_audit_options[mini_audit_options_hero_status]',
					'value' => $options['mini_audit_options_hero_status'],
					'id'    => 'mini_audit_options_hero_status',
				]
			);

			add_settings_field(
				'mini_audit_options_hero_title',
				__( 'Title', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_hero',
				[
					'type'  => 'text',
					'name'  => 'mini_audit_options[mini_audit_options_hero_title]',
					'value' => $options['mini_audit_options_hero_title'],
					'id'    => 'mini_audit_options_hero_title',
				]
			);

			add_settings_field(
				'mini_audit_options_hero_description',
				__( 'Description', 'mini-audit' ),
				[ $this, 'textarea' ],
				'mini_audit',
				'mini_audit_options_hero',
				[
					'type'  => 'text',
					'name'  => 'mini_audit_options[mini_audit_options_hero_description]',
					'value' => $options['mini_audit_options_hero_description'],
					'id'    => 'mini_audit_options_hero_description',
				]
			);

			add_settings_section(
				'mini_audit_options_continue_button',
				__( 'Continue Button' ),
				[ $this, 'mini_audit_continue_button_section_callback' ],
				'mini_audit'
			);

			add_settings_field(
				'mini_audit_continue_button_text',
				__( 'Text', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_continue_button',
				[
					'type'  => 'text',
					'name'  => 'mini_audit_options[mini_audit_continue_button_text]',
					'value' => $options['mini_audit_continue_button_text'],
					'id'    => 'mini_audit_continue_button_text',
				]
			);

			add_settings_field(
				'mini_audit_continue_button_background_color',
				__( 'Background Color', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_continue_button',
				[
					'type'  => 'color',
					'name'  => 'mini_audit_options[mini_audit_continue_button_background_color]',
					'value' => $options['mini_audit_continue_button_background_color'],
					'id'    => 'mini_audit_continue_button_background_color',
				]
			);

			add_settings_field(
				'mini_audit_continue_button_color',
				__( 'Text Color', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_continue_button',
				[
					'type'  => 'color',
					'name'  => 'mini_audit_options[mini_audit_continue_button_color]',
					'value' => $options['mini_audit_continue_button_color'],
					'id'    => 'mini_audit_continue_button_color',
				]
			);

			add_settings_section(
				'mini_audit_options_audit_button',
				__( 'Audit Button' ),
				[ $this, 'mini_audit_audit_button_section_callback' ],
				'mini_audit'
			);

			add_settings_field(
				'mini_audit_audit_button_text',
				__( 'Text', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_audit_button',
				[
					'type'  => 'text',
					'name'  => 'mini_audit_options[mini_audit_audit_button_text]',
					'value' => $options['mini_audit_audit_button_text'],
					'id'    => 'mini_audit_audit_button_text',
				]
			);

			add_settings_field(
				'mini_audit_audit_button_background_color',
				__( 'Background Color', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_audit_button',
				[
					'type'  => 'color',
					'name'  => 'mini_audit_options[mini_audit_audit_button_background_color]',
					'value' => $options['mini_audit_audit_button_background_color'],
					'id'    => 'mini_audit_audit_button_background_color',
				]
			);

			add_settings_field(
				'mini_audit_audit_button_color',
				__( 'Text Color', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_audit_button',
				[
					'type'  => 'color',
					'name'  => 'mini_audit_options[mini_audit_audit_button_color]',
					'value' => $options['mini_audit_audit_button_color'],
					'id'    => 'mini_audit_audit_button_color',
				]
			);

			foreach ( $sections as $key => $value ) {
				add_settings_section(
					'mini_audit_options_' . $key,
					$value,
					[ $this, 'mini_audit_options_section_callback' ],
					'mini_audit'
				);

				add_settings_field(
					'mini_audit_options_' . $key . '_status',
					__( 'Status', 'mini-audit' ),
					[ $this, 'input' ],
					'mini_audit',
					'mini_audit_options_' . $key,
					[
						'type'  => 'switch',
						'name'  => 'mini_audit_options[mini_audit_options_' . $key . '_status]',
						'value' => $options[ 'mini_audit_options_' . $key . '_status' ],
						'id'    => 'mini_audit_options_' . $key . '_status',
					]
				);

				add_settings_field(
					'mini_audit_options_' . $key . '_text',
					__( 'Text', 'mini-audit' ),
					[ $this, 'input' ],
					'mini_audit',
					'mini_audit_options_' . $key,
					[
						'type'  => 'text',
						'name'  => 'mini_audit_options[mini_audit_options_' . $key . '_text]',
						'value' => $options[ 'mini_audit_options_' . $key . '_text' ],
						'id'    => 'mini_audit_options_' . $key . '_text',
					]
				);

				add_settings_field(
					'mini_audit_options_' . $key . '_button_text',
					__( 'Button Text', 'mini-audit' ),
					[ $this, 'input' ],
					'mini_audit',
					'mini_audit_options_' . $key,
					[
						'type'  => 'text',
						'name'  => 'mini_audit_options[mini_audit_options_' . $key . '_button_text]',
						'value' => $options[ 'mini_audit_options_' . $key . '_button_text' ],
						'id'    => 'mini_audit_options_' . $key . '_button_text',
					]
				);

				add_settings_field(
					'mini_audit_options_' . $key . '_button_url',
					__( 'Button URL', 'mini-audit' ),
					[ $this, 'input' ],
					'mini_audit',
					'mini_audit_options_' . $key,
					[
						'type'  => 'text',
						'name'  => 'mini_audit_options[mini_audit_options_' . $key . '_button_url]',
						'value' => $options[ 'mini_audit_options_' . $key . '_button_url' ],
						'id'    => 'mini_audit_options_' . $key . '_button_url',
					]
				);

				add_settings_field(
					'mini_audit_options_' . $key . '_button_background_color',
					__( 'Background Color', 'mini-audit' ),
					[ $this, 'input' ],
					'mini_audit',
					'mini_audit_options_' . $key,
					[
						'type'  => 'color',
						'name'  => 'mini_audit_options[mini_audit_options_' . $key . '_button_background_color]',
						'value' => $options[ 'mini_audit_options_' . $key . '_button_background_color' ],
						'id'    => 'mini_audit_options_' . $key . '_button_background_color',
					]
				);

				add_settings_field(
					'mini_audit_options_' . $key . '_button_color',
					__( 'Text Color', 'mini-audit' ),
					[ $this, 'input' ],
					'mini_audit',
					'mini_audit_options_' . $key,
					[
						'type'  => 'color',
						'name'  => 'mini_audit_options[mini_audit_options_' . $key . '_button_color]',
						'value' => $options[ 'mini_audit_options_' . $key . '_button_color' ],
						'id'    => 'mini_audit_options_' . $key . '_button_color',
					]
				);
			}
		}

		if ( 'mini-audit-mail' === $plugin_page ) {
			add_settings_section(
				'mini_audit_options_mail',
				__( 'Mail' ),
				[ $this, 'mini_audit_options_mail_section_callback' ],
				'mini_audit'
			);

			add_settings_field(
				'mini_audit_options_mail_from_email',
				__( 'From Email', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_mail',
				[
					'type'  => 'text',
					'name'  => 'mini_audit_options[mini_audit_options_mail_from_email]',
					'value' => $options['mini_audit_options_mail_from_email'],
					'id'    => 'mini_audit_options_mail_from_email',
				]
			);

			add_settings_field(
				'mini_audit_options_mail_from_name',
				__( 'From Name', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_mail',
				[
					'type'  => 'text',
					'name'  => 'mini_audit_options[mini_audit_options_mail_from_name]',
					'value' => $options['mini_audit_options_mail_from_name'],
					'id'    => 'mini_audit_options_mail_from_name',
				]
			);

			add_settings_field(
				'mini_audit_options_mail_subject',
				__( 'Subject', 'mini-audit' ),
				[ $this, 'input' ],
				'mini_audit',
				'mini_audit_options_mail',
				[
					'type'  => 'text',
					'name'  => 'mini_audit_options[mini_audit_options_mail_subject]',
					'value' => $options['mini_audit_options_mail_subject'],
					'id'    => 'mini_audit_options_mail_subject',
				]
			);

			add_settings_field(
				'mini_audit_options_mail_content',
				__( 'Content', 'mini-audit' ),
				[ $this, 'textarea' ],
				'mini_audit',
				'mini_audit_options_mail',
				[
					'name'  => 'mini_audit_options[mini_audit_options_mail_content]',
					'value' => $options['mini_audit_options_mail_content'],
					'id'    => 'mini_audit_options_mail_content',
				]
			);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function options_page() {
		add_menu_page(
			__( 'Crush Auditor' ),
			__( 'Crush Auditor' ),
			'manage_options',
			'mini-audit',
			[ $this, 'options_page_html' ],
			plugins_url( 'Logo-GMB-Crush.webp', GMB_CRUSH_MINI_AUDIT_FILE )
		);

		$this->mini_audit_connect_page = add_submenu_page(
			'mini-audit',
			__( 'Account' ),
			__( 'Account' ),
			'manage_options',
			'mini-audit',
			[ $this, 'options_page_html' ]
		);

		$this->mini_audit_options_page = add_submenu_page(
			'mini-audit',
			__( 'Options' ),
			__( 'Options' ),
			'manage_options',
			'mini-audit-options',
			[ $this, 'options_page_html' ]
		);

		$this->mini_audit_email_page = add_submenu_page(
			'mini-audit',
			__( 'Email' ),
			__( 'Email' ),
			'manage_options',
			'mini-audit-mail',
			[ $this, 'options_page_html' ]
		);

		$this->mini_audit_shortcode_page = add_submenu_page(
			'mini-audit',
			__( 'Shortcode' ),
			__( 'Shortcode' ),
			'manage_options',
			'mini-audit-shortcode',
			[ $this, 'shortcode_page_html' ]
		);

		$this->mini_audit_payment_page = add_submenu_page(
			'mini-audit',
			__( 'Payment' ),
			__( 'Payment' ),
			'manage_options',
			'mini-audit-payment',
			[ $this, 'payment_page_html' ]
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $option
	 * @param [type] $old_value
	 * @param [type] $value
	 * @return void
	 */
	public function do_authenticate( $option, $value ) {
		if ( 'mini_audit_connect_options' === $option ) {
			if ( isset( $value['mini_audit_connect_field_email'], $value['mini_audit_connect_field_password'] ) ) {
				$this->authenticate( $value );
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $option
	 * @param [type] $old_value
	 * @param [type] $value
	 * @return void
	 */
	public function re_authenticate( $value, $option, $old_value) {
		if ( 'mini_audit_connect_options' === $option ) {
			if ( isset( $value['mini_audit_connect_field_email'], $value['mini_audit_connect_field_password'] ) ) {
				$this->authenticate( $value );
			}
		}

		return $value;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $value
	 * @return void
	 */
	public function authenticate( $value ) {
		try {
			$response = wp_remote_post(
				'https://staging-api.gmbcrush.com/auth/login',
				[
					'body' => [
						'email'    => $value['mini_audit_connect_field_email'],
						'password' => $value['mini_audit_connect_field_password'],
					],
				]
			);

			if ( is_wp_error( $response ) ) {
				add_settings_error( 'mini_audit_messages', 'mini_audit_message', $response->get_error_message(), 'error' );
				return;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$body      = json_decode( $response['body'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				if ( 200 === $response_code ) {
					update_option( 'mini_audit_connect_credentials', $body );
					update_option(
						'mini_audit_disconnect_options',
						[
							'mini_audit_disconnect_field_status' => 'not_disconnected',
						]
					);
				}

				if ( 400 === $response_code ) {
					add_settings_error( 'mini_audit_messages', 'mini_audit_message', $body['message'], 'error' );
				}
			}
		} catch ( \Exception $e ) {
			add_settings_error( 'mini_audit_messages', 'mini_audit_message', $e->getMessage(), 'error' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $option
	 * @param [type] $old_value
	 * @param [type] $value
	 * @return void
	 */
	public function disconnect( $value, $option, $old_value ) {
		if ( 'mini_audit_disconnect_options' === $option ) {
			if ( isset( $value['mini_audit_disconnect_field_status'] ) && ( 'disconnected' === $value['mini_audit_disconnect_field_status'] ) ) {
				delete_option( 'mini_audit_connect_options' );
				delete_option( 'mini_audit_connect_credentials' );
			}
		}

		return $value;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $classes
	 * @return void
	 */
	public function admin_body_classes( $classes ) {
		$screen = get_current_screen();

		if ( in_array( $screen->id, [ $this->mini_audit_connect_page, $this->mini_audit_options_page, $this->mini_audit_email_page, $this->mini_audit_shortcode_page, $this->mini_audit_payment_page ], true ) ) {
			$mini_audit_class = ' mini-audit';
			if ( isset( $this->nav_tabs ) && $this->nav_tabs ) {
				$mini_audit_class .= ' mini-audit-is-tabbed-screen';
			}

			return $classes . $mini_audit_class;
		}

		return $classes;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$asset_file = include( plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . 'build/mini-audit-options.asset.php' );

		wp_register_style(
			'mini-audit-options',
			plugins_url( 'build/mini-audit-options.css', GMB_CRUSH_MINI_AUDIT_FILE ),
			[],
			$asset_file['version']
		);

		$grid_columns = array_fill( 0, count( $this->nav_tabs ), '1fr' );
		wp_add_inline_style(
			'mini-audit-options',
			sprintf(
				'.mini-audit-tabs-wrapper {
					-ms-grid-columns: %1$s;
					grid-template-columns: %1$s;
				}
				',
				implode( ' ', $grid_columns )
			)
		);

		wp_enqueue_style( 'mini-audit-options' );
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_connect_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Please enter email address and password below to connect your account.' ); ?></p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_disconnect_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Please click on the disconnect button below to disconnect your account.' ); ?></p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_hero_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Update title and description below.' ); ?></p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_continue_button_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Update text and styles for continue button below.' ); ?></p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_audit_button_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Update text and styles for audit button below.' ); ?></p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_options_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Update text and styles for blur sections button below.' ); ?></p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_options_mail_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Update email address that emails are sent from.' ); ?></p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function input( $args ) {
		$field_class = 'field';
		$input_class = 'form-control';
		$type        = 'switch' === $args['type'] ? 'checkbox' : $args['type'];
		$role        = 'switch' === $args['type'] ? 'switch' : '';
		$value       = $args['value'];

		if ( 'switch' === $args['type'] ) {
			$field_class .= ' form-switch';
			$input_class  = 'form-check-input';
		}

		?>
		<div class="<?php echo esc_attr( $field_class ); ?>">
			<?php if ( 'switch' === $args['type'] ) : ?>
				<input
					id="<?php echo esc_attr( $args['id'] ); ?>"
					class="<?php echo esc_attr( $input_class ); ?>"
					type="<?php echo esc_attr( $type ); ?>"
					role="<?php echo esc_attr( $role ); ?>"
					name="<?php echo esc_attr( $args['name'] ); ?>"
					value="1" <?php checked( $value, 1 ); ?> />
				<label class="form-check-label" for="<?php echo esc_attr( $args['id'] ); ?>">Toggle to hide or show this section.</label>
			<?php else : ?>
				<input
					id="<?php echo esc_attr( $args['id'] ); ?>"
					class="<?php echo esc_attr( $input_class ); ?>"
					type="<?php echo esc_attr( $type ); ?>"
					name="<?php echo esc_attr( $args['name'] ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
				>
			<?php endif; ?>
		</div>		
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function textarea( $args ) {
		?>
		<textarea id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" rows="10" cols="50"><?php echo esc_attr( $args['value'] ); ?></textarea>
		<?php if ( 'mini_audit_options_mail_content' === $args['id'] ) : ?>
			<p class="description">
				<?php echo __( 'Note: You can use the codes <code>[user]</code>, <code>[report_url]</code>, and <code>[company]</code> in the textarea above. These will be replaced with dynamic content before the email is sent to the users.' ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_disconnect_field_callback( $args ) {
		$allowed_html = [
			'strong' => [],
		];

		?>
		<input
			type="<?php echo esc_attr( $args['type'] ); ?>"
			id="<?php echo esc_attr( $args['name'] ); ?>"
			name="mini_audit_disconnect_options[<?php echo esc_attr( $args['name'] ); ?>]"
			value="<?php echo esc_attr( $args['value'] ); ?>"
		>
		<p class="status">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php echo esc_html( __( 'Connected' ) ); ?></span>
		</p>
		<p class="description">
			<?php
			echo wp_kses(
				sprintf(
					__( 'You have connected your account using <strong>%s</strong>. You can disconnect and connect with a different account at any time.' ),
					$args['user_email']
				),
				$allowed_html
			);
			?>
		</p>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $args
	 * @return void
	 */
	public function mini_audit_connect_field_callback( $args ) {
		?>
		<input
			type="<?php echo esc_attr( $args['type'] ); ?>"
			id="<?php echo esc_attr( $args['name'] ); ?>"
			name="mini_audit_connect_options[<?php echo esc_attr( $args['name'] ); ?>]"
			value="<?php echo esc_attr( $args['value'] ); ?>"
			placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
		>

		<?php if ( 'password' === $args['type'] ) : ?>
			<p class="description">
				<?php
				printf(
					__( 'Don\'t have an account? <a href="%s" target="_blank">Signup</a>.', 'text_domain' ),
					'https://www.gmbcrush.com/pricing/'
				);
				?>
			</p>
			<p class="description">
				<?php
				printf(
					__( 'Forgot Your Password? <a href="%s" target="_blank">Reset Password</a>.', 'text_domain' ),
					'https://app.gmbcrush.com/forgot-password'
				);
				?>
			</p>
			<?php
		endif;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function options_page_html() {
		global $plugin_page;

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$submit_button_text = __( 'Save Settings', 'mini-audit' );
		$active_tab         = __( 'Options', 'mini-audit' );

		if ( 'mini-audit' === $plugin_page ) {
			$connect_credentials = get_mini_audit_connect_credentials();
			$submit_button_text  = __( 'Connect' );
			$active_tab          = __( 'Account', 'mini-audit' );

			if ( isset( $connect_credentials['accessToken'] ) && ( '' !== $connect_credentials['accessToken'] ) ) {
				$submit_button_text = __( 'Disconnect' );
			}
		}

		if ( 'mini-audit-mail' === $plugin_page ) {
			$active_tab = __( 'Email', 'mini-audit' );
		}

		$this->tabbed_screen_header(
			__( 'Crush Auditor Settings', 'mini-audit' ),
			$active_tab
		);

		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( 'mini_audit_messages', 'mini_audit_message', __( 'Settings Saved', 'mini_audit' ), 'updated' );
		}

		// show error/update messages
		settings_errors( 'mini_audit_messages' );

		?>
		<div class="mini-audit-body">
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "wporg"
				settings_fields( 'mini_audit' );
				// output setting sections and their fields
				// (sections are registered for "mini_audit", each field is registered to a specific section)
				do_settings_sections( 'mini_audit' );
				// output save settings button
				submit_button( $submit_button_text );
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
	public function payment_page_html() {
		$this->tabbed_screen_header(
			__( 'Crush Auditor Settings', 'mini-audit' ),
			__( 'Payment', 'mini-audit' )
		);

		?>
		<div class="mini-audit-body">
			Coming soon...
		</div>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function shortcode_page_html() {
		$this->tabbed_screen_header(
			__( 'Crush Auditor Settings', 'mini-audit' ),
			__( 'Shortcode', 'mini-audit' )
		);

		?>
		<div class="mini-audit-body">
			<div class="block">
				<p>To insert the WordPress shortcode into a post or page, follow these steps:</p>
				<ul>
					<li>Log in to your WordPress dashboard.</li>
					<li>Navigate to the post or page where you want to add the shortcode.</li>
					<li>In the post editor, click on the position where you want the shortcode to appear.</li>
					<li>Type the shortcode into the editor, using the following format: <code>[mini-audit]</code>.</li>
					<li>Publish or update the post or page to see the shortcode in action.</li>
				</ul>
			
				<p>None: You can insert the shortcode <code>[mini-audit]</code> into any page to display the form.</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param string $title
	 * @param string $active_tab
	 * @param string $context
	 * @return void
	 */
	public function tabbed_screen_header( $title = '', $active_tab = '', $context = 'settings' ) {
		?>
		<div class="mini-audit-header">
			<div class="mini-audit-title-section">
				<h1><span class="mini-audit-badge"></span> <?php echo esc_html( $title ); ?></h1>
			</div>
			<nav class="mini-audit-tabs-wrapper">
				<?php $this->admin_tabs( esc_html( $active_tab ) ); ?>
			</nav>
		</div>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param string $active_tab
	 * @param string $context
	 * @param boolean $echo
	 * @return void
	 */
	public function admin_tabs( $active_tab = '', $context = 'settings', $echo = true ) {
		$tabs_html    = '';
		$idle_class   = 'mini-audit-nav-tab';
		$active_class = 'mini-audit-nav-tab active';

		$tabs_html = array();

		// Loop through tabs and build navigation.
		foreach ( array_values( $this->nav_tabs ) as $tab_data ) {
			$is_current  = (bool) ( $tab_data['name'] == $active_tab );
			$tab_class   = $is_current ? $active_class : $idle_class;
			$tabs_html[] = '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['name'] ) . '</a>';
		}

		if ( ! $echo ) {
			return $tabs_html;
		}

		echo implode( "\n", $tabs_html );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function sanitize_callback( $input ) {
		$sanitary_values = array();

		$default_options = get_mini_audit_default_options();
		foreach ( array_keys( $default_options ) as $value ) {
			if ( isset( $input[ $value ] ) ) {
				$sanitary_values[ $value ] = $input[ $value ];
			}
		}

		return $sanitary_values;
	}
}

new Options();
