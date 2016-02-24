<?php

/**
 * User List Sort
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'user_sort_init');

/**
 * Initialize the plugin
 * @return void
 */
function user_sort_init() {
	elgg_extend_view('elgg.css', 'forms/user/sort.css');
}

/**
 * Returns as list of sort options
 * @return array
 */
function user_sort_get_sort_options() {

	$fields = array();

	$plugin = elgg_get_plugin_from_id('user_sort');
	$settings = $plugin->getAllSettings();
	foreach ($settings as $k => $val) {
		if (!$val) {
			continue;
		}
		list($sort, $option) = explode('::', $k);
		if ($sort && in_array(strtolower($option), array('asc', 'desc'))) {
			$fields[] = $k;
		}
	}
	return elgg_trigger_plugin_hook('sort_fields', 'user', null, $fields);
}

/**
 * Adds sort options to the ege* options array
 * 
 * @param array  $options   ege* options
 * @param string $field     Sort field
 * @param string $direction Sort direction (asc|desc)
 * @return array
 */
function user_sort_add_sort_options(array $options = array(), $field = 'time_created', $direction = 'desc') {

	$dbprefix = elgg_get_config('dbprefix');
	$direction = strtoupper($direction);
	if (!in_array($direction, array('ASC', 'DESC'))) {
		$direction = 'DESC';
	}

	$order_by = explode(',', elgg_extract('order_by', $options, ''));
	array_walk($order_by, 'trim');

	$options['joins']['users_entity'] = "JOIN {$dbprefix}users_entity users_entity ON users_entity.guid = e.guid";

	switch ($field) {

		case 'type' :
		case 'subtype' :
		case 'guid' :
		case 'owner_guid' :
		case 'container_guid' :
		case 'site_guid' :
		case 'enabled' :
		case 'time_created';
		case 'time_updated' :
		case 'access_id' :
			array_unshift($order_by, "e.{$field} {$direction}");
			break;

		case 'last_action' :
			$options['selects']['last_action'] = "GREATEST(e.time_created, e.last_action, e.time_updated, users_entity.last_login) as last_action";
			array_unshift($order_by, "last_action {$direction}");
			break;

		case 'friend_count' :
			$options['joins']['friend_count'] = "LEFT JOIN {$dbprefix}entity_relationships friend_count ON friend_count.guid_one = e.guid AND friend_count.relationship = 'friend'";
			$options['selects']['friend_count'] = "COUNT(friend_count.guid_two) as friend_count";
			$options['group_by'] = 'e.guid';

			array_unshift($order_by, "friend_count {$direction}");
			break;
	}

	if ($field == 'alpha') {
		$order_by[] = "users_entity.name {$direction}";
	} else {
		// Always order by name for matching fields
		$order_by[] = "users_entity.name ASC";
	}

	$options['order_by'] = implode(', ', array_unique(array_filter($order_by)));

	$params = array(
		'field' => $field,
		'direction' => $direction,
	);

	return elgg_trigger_plugin_hook('sort_options', 'user', $params, $options);
}

/**
 * Adds relationship/metadata filters to the ege* options array
 *
 * @param array  $options    ege* options
 * @param string $rel        Filter name
 * @param string $page_owner Page owner
 * @return array
 */
function user_sort_add_rel_options(array $options = array(), $rel = '', $page_owner = null) {
	$params = array(
		'rel' => $rel,
		'page_owner' => $page_owner,
	);
	return elgg_trigger_plugin_hook('rel_options', 'user', $params, $options);
}

/**
 * Adds search query options to the ege* options array
 *
 * @param array  $options   ege* options
 * @param string $query     Query
 * @return array
 */
function user_sort_add_search_query_options(array $options = array(), $query = '') {

	if (!elgg_is_active_plugin('search') || !$query) {
		return $options;
	}

	$query = sanitize_string($query);

	$advanced = elgg_extract('advanced_search', $options, false);

	$dbprefix = elgg_get_config('dbprefix');
	$options['joins']['users_entity'] = "JOIN {$dbprefix}users_entity users_entity ON users_entity.guid = e.guid";

	$fields = array('name');
	if (elgg_get_plugin_setting('username', 'user_sort', true)) {
		$fields[] = 'username';
	}
	$where = search_get_where_sql('users_entity', $fields, ['query' => $query], false);

	$profile_fields = array_keys((array) elgg_get_config('profile_fields'));
	$profile_fields = array_diff($profile_fields, $fields);

	if ($advanced && !empty($profile_fields)) {
		$options['joins']['profile_fields_md'] = "JOIN {$dbprefix}metadata profile_fields_md on e.guid = profile_fields_md.entity_guid";
		$options['joins']['profile_fields_msv'] = "JOIN {$dbprefix}metastrings profile_fields_msv ON n_table.value_id = profile_fields_msv.id";

		$clauses = _elgg_entities_get_metastrings_options('metadata', array(
			'metadata_names' => $profile_fields,
			'metadata_values' => null,
			'metadata_name_value_pairs' => null,
			'metadata_name_value_pairs_operator' => null,
			'metadata_case_sensitive' => null,
			'order_by_metadata' => null,
			'metadata_owner_guids' => null,
		));

		$options['joins'] = array_merge($clauses['joins'], $options['joins']);
		$profile_fields_md_where = "(({$clauses['wheres'][0]}) AND profile_fields_msv.string LIKE '%$query%')";

		$options['wheres'][] = "(($where) OR ($profile_fields_md_where))";
	} else {
		$options['wheres'][] = "$where";
	}

	return $options;
}
