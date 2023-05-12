<?php

namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

final class Mini_Audit {
	/**
	 * The single instance of the class.
	 * @var [type]
	 */
	protected static $_instance = null;

	/**
	 * Main Mini_Audit instance.
	 * Ensures only one instance of Mini_Audit is loaded or can be loaded.
	 *
	 * @return [type] [description]
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Mini_Audit constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include required files used in admin and on the frontend.
	 * @return [type] [description]
	 */
	private function includes() {
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/vendor/autoload.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/functions.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/template-functions.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-query-builder.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-query.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-activation.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-options.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-api.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-ajax.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-report.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-leads-table.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-leads.php';
		require plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/includes/class-shortcodes.php';

		$this->api    = new API();
		$this->loader = new \Twig\Loader\FilesystemLoader( plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . '/templates' );
		$this->twig   = new \Twig\Environment(
			$this->loader,
			[]
		);
	}

	/**
	 * Init hooks.
	 * @return [type] [description]
	 */
	private function hooks() {
		register_activation_hook( GMB_CRUSH_MINI_AUDIT_FILE, [ __NAMESPACE__ . '\Activation', 'activate' ] );
		register_deactivation_hook( GMB_CRUSH_MINI_AUDIT_FILE, [ __NAMESPACE__ . '\Activation', 'deactivate' ] );

		add_action( 'admin_init', [ $this, 'redirect' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function redirect() {
		if ( get_option( 'mini_audit_do_activation_redirect', false ) ) {
			delete_option( 'mini_audit_do_activation_redirect' );
			wp_safe_redirect( admin_url( add_query_arg( array( 'page' => 'mini-audit' ), 'admin.php' ) ) );
			exit();
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_register_style(
			'google-fonts',
			'https://fonts.googleapis.com/css2?family=Raleway&display=swap',
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param string $template
	 * @param array $args
	 *
	 * @return void
	 */
	public function render( $template = '', $args = [] ) {
		return $this->twig->render( $template, $args );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function dummy_response( $json = 'block-paving-layer.json' ) {
		$json = file_get_contents( plugin_dir_path( GMB_CRUSH_MINI_AUDIT_FILE ) . 'includes/' . $json );

		return json_decode( $json, true );
	}
}
