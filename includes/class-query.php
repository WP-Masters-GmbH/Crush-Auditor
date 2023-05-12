<?php

namespace Upnrunn;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Query class.
 * @var [type]
 */
class Query {
	/**
	 * Undocumented function
	 *
	 * @param integer $lead_id
	 * @return void
	 */
	public function get_lead($lead_id = 0) {
		return (new Query_Builder())->table('mini_audit_leads')
			->where('id', $lead_id)
			->first();
	}

	public function get_leads($args = []) {
		$defaults = array(
			'page'           => 1,
			'items_per_page' => 10,
			's'              => '',
		);

		$args = wp_parse_args($args, $defaults);

		$query_builder = (new Query_Builder())
			->table('mini_audit_leads')
			->limit($args['items_per_page'], absint(($args['page'] - 1) * $args['items_per_page']));

		if ('' !== $args['s']) {
			$query_builder->or_where('name', '%' . $args['s'] . '%', 'LIKE');
			// $query_builder->or_where( 'email', '%' . $args['s'] . '%', 'LIKE' );
		}

		return $query_builder->get();
	}

	/**
	 * Undocumented function
	 *
	 * @param integer $lead_id
	 * @return void
	 */
	function update_lead($lead_id = 0, $data = []) {
		return (new Query_Builder())->table('mini_audit_leads')
			->where('id', $lead_id)
			->update($data);
	}
}
