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
	
	$options['joins']['ue'] = "JOIN {$dbprefix}users_entity ue ON ue.guid = e.guid";

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
		case 'last_action' :
		case 'access_id' :
			array_unshift($order_by, "e.{$field} {$direction}");
			break;

		case 'friend_count' :
			$options['joins']['friend_count'] = "LEFT JOIN {$dbprefix}entity_relationships fr ON fr.guid_one = e.guid AND fr.relationship = 'friend'";
			$options['selects']['friend_count'] = "COUNT(fr.guid_two) as friend_count";
			$options['group_by'] = 'fr.guid_one';
			
			array_unshift($order_by, "friend_count {$direction}");
			break;
	}

	// Always order by name for matching fields
	$order_by[] = "ue.name {$direction}";

	$options['order_by'] = implode(', ', array_unique(array_filter($order_by)));

	return elgg_trigger_plugin_hook('sort_options', 'user', null, $options);
}
