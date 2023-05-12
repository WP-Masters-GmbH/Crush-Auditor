<?php

namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Leads_Table extends \WP_List_Table {
	/**
	 * Undocumented function
	 */
	public function __construct() {
		parent::__construct(
			array(
				'plural' => 'leads',
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$page            = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$items_per_page  = 10;
		$pagination_args = [
			'total_items' => 0,
			'per_page'    => $items_per_page,
		];

		$this->items = [];

		if ( get_option( 'mini_audit_db_version' ) ) {
			$leads = ( new Query() )->get_leads(
				[
					'page'           => $page,
					'items_per_page' => $items_per_page,
					's'              => isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
				]
			);

			$this->items                    = $leads['results'];
			$pagination_args['total_items'] = $leads['found_rows'];
		}

		$this->set_pagination_args( $pagination_args );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function get_columns() {

		ob_start(); ?>
        <input type="checkbox"/>
		<?php
		$checkbox = ob_get_clean();

		$columns = array(
			'cb'         => $checkbox,
			'name'       => __( 'Name' ),
			'last_name'  => __( 'Last Name' ),
			'email'      => __( 'Email' ),
			'phone'      => __( 'Phone' ),
			'keyword'    => __( 'Targeted Keyword' ),
			'place_name' => __( 'Business Name' ),
			'location'   => __( 'Business Location' ),
			'created_at' => __( 'Date' ),
		);

		return $columns;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $item
	 * @param [type] $column_name
	 *
	 * @return void
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'name':
			case 'last_name':
			case 'email':
			case 'phone':
			case 'keyword':
			case 'place_name':
			case 'location':
			case 'created_at':
				return $item->$column_name;

			default:
				return __( '' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $item
	 *
	 * @return void
	 */
	public function column_cb( $item ) {

		ob_start(); ?>
        <input type="checkbox" name="lead[]" value="<?php echo esc_attr( $item->id ); ?>"/>
		<?php

		return ob_get_clean();
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $item
	 *
	 * @return void
	 */
	function column_name( $item ) {
		$view_link = home_url( '/mini-audit-report/' . $item->id );

		ob_start(); ?>
        <a target="_blank" href="<?php esc_url( $view_link ); ?>"><?php _e( 'View' ) ?></a>
		<?php
		$view = ob_get_clean();

		$actions = array(
			'view' => $view
		);

		return sprintf( '%1$s %2$s', $item->name, $this->row_actions( $actions ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);

		return $actions;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $which
	 *
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		if ( empty( $this->items ) ) {
			return;
		}

		?>
        <div class="alignleft actions">
			<?php
			if ( 'top' === $which ) {
				submit_button( __( 'Export All', 'mini-audit' ), 'primary', 'export_all', false );
			}
			?>
        </div>
		<?php
	}
}
