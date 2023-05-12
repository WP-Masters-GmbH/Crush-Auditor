<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Exception;

/**
 * Query_Builder class.
 * @var [type]
 */
class Query_Builder {
	public $query;
	public $results;
	public $found_rows    = 0;
	public $max_num_pages = 0;

	protected function reset(): void {
		$this->query = new \stdClass();
	}

	/**
	 * Build a base FROM query.
	 */
	public function table( string $table ): Query_Builder {
		global $wpdb;

		$this->reset();

		$this->query->table = $wpdb->prefix . $table;
		$this->query->type  = 'SELECT';
		$this->query->base  = 'FROM ' . $this->query->table;

		return $this;
	}

	/**
	 * Build a base SELECT query.
	 */
	public function select( array $select ): Query_Builder {
		$this->query->select = $select;

		return $this;
	}

	/**
	 * Build a INSERT row query.
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function insert( array $data ) {
		global $wpdb;

		$wpdb->insert( $this->query->table, $data );

		return $wpdb->insert_id;
	}

	/**
	 * Build a DELETE row query.
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function delete() {
		global $wpdb;

		$where = [];
		foreach ( $this->query->where as $where_item ) {
			$where[ $where_item[0] ] = $where_item[1];
		}

		return $wpdb->delete( $this->query->table, $where );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function update( $data = []) {
		global $wpdb;

		$where = [];
		foreach ( $this->query->where as $where_item ) {
			$where[ $where_item[0] ] = $where_item[1];
		}

		return $wpdb->update( $this->query->table, $data, $where );
	}

	/**
	 * Add a WHERE condition.
	 */
	public function where( string $field, string $value, string $operator = '=' ): Query_Builder {
		if ( ! in_array( $this->query->type, [ 'SELECT', 'UPDATE', 'DELETE' ], true ) ) {
			throw new Exception( __( 'WHERE can only be added to SELECT, UPDATE OR DELETE' ) );
		}

		$this->query->where[] = [ $field, $value, $operator ];

		return $this;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $field
	 * @param string $value
	 * @param string $operator
	 * @return Query_Builder
	 */
	public function or_where( string $field, string $value, string $operator = '=' ): Query_Builder {
		if ( ! in_array( $this->query->type, [ 'SELECT', 'UPDATE', 'DELETE' ], true ) ) {
			throw new Exception( __( 'WHERE can only be added to SELECT, UPDATE OR DELETE' ) );
		}

		$this->query->or_where[] = [ $field, $value, $operator ];

		return $this;
	}

	/**
	 * Add a LIMIT constraint.
	 */
	public function limit( int $limit, int $offset = 0 ): Query_Builder {
		if ( ! in_array( $this->query->type, [ 'SELECT' ], true ) ) {
			throw new Exception( __( 'LIMIT can only be added to SELECT' ) );
		}

		$this->query->limit  = $limit;
		$this->query->offset = $offset;

		return $this;
	}

	/**
	 * Get the final query string.
	 */
	public function get_sql(): string {
		global $wpdb;

		$query = $this->query;

		$found_rows = '';
		$distinct = '';
		$fields = isset($this->query->select) ? implode( ', ', $this->query->select ): '*';

		if ( ! empty( $this->query->limit ) ) {
			$found_rows = 'SQL_CALC_FOUND_ROWS';
		}
		
		$where = '';

		if ( ! empty( $query->where ) ) {
			$_where = [];
			foreach ( $query->where as $where_item ) {
				$_where[] = $wpdb->prepare( "{$where_item[0]} {$where_item[2]} %s", $where_item[1] );
			}

			$where = implode( ' AND ', $_where );
		}

		if ( ! empty( $query->or_where ) ) {
			$or_where = [];
			foreach ( $query->or_where as $where_item ) {
				$or_where[] = $wpdb->prepare( "{$where_item[0]} {$where_item[2]} %s", $where_item[1] );
			}

			$where = implode( ' OR ', $or_where );
		}

		$sql = "
			SELECT $found_rows $distinct $fields 
			FROM {$this->query->table}
		";

		if('' !==$where ) {
			$sql .= " WHERE $where";
		}

		if ( isset( $query->limit ) ) {
			$sql .= ' LIMIT ' . $query->limit . ' OFFSET ' . $query->offset;
		}

		return $sql;
	}

	/**
	 * Build SELECT a Row query.
	 * @return [type] [description]
	 */
	public function first() {
		global $wpdb;

		if ( ! in_array( $this->query->type, [ 'SELECT' ], true ) ) {
			throw new Exception( __( 'SELECT a Row can only be added to SELECT' ) );
		}

		return $wpdb->get_row( $this->get_sql(), ARRAY_A );
	}

	/**
	 * Build SELECT Generic Results query.
	 * @return [type] [description]
	 */
	public function get() {
		global $wpdb;

		if ( ! in_array( $this->query->type, [ 'SELECT' ], true ) ) {
			throw new Exception( 'SELECT Generic Results can only be added to SELECT' );
		}

		$this->results = $wpdb->get_results( $this->get_sql() );

		$this->set_found_rows( $this->get_sql() );

		return [
			'results'       => $this->results,
			'found_rows'    => $this->found_rows,
			'max_num_pages' => $this->max_num_pages,
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function set_found_rows() {
		global $wpdb;

		if ( ! empty( $this->query->limit ) ) {
			$this->found_rows    = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			$this->max_num_pages = ceil( $this->found_rows / $this->query->limit );
		} else {
			if ( is_array( $this->results ) ) {
				$this->found_rows = count( $this->results );
			} else {
				if ( null === $this->results ) {
					$this->found_rows = 0;
				} else {
					$this->found_rows = 1;
				}
			}
		}
	}
}
