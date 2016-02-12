<?php

$options = (array) elgg_extract('options', $vars);
$callback = elgg_extract('callback', $vars, 'elgg_list_entities');

if (!is_array($options) || empty($options) || !is_callable($callback)) {
	return;
}

$query = elgg_extract('query', $vars, get_input('query'));
$vars['query'] = $query;

$sort = elgg_extract('sort', $vars, get_input('sort', 'alpha::asc'));
$vars['sort'] = $sort;

$base_url = elgg_extract('base_url', $options);
if (!$base_url) {
	$base_url = current_page_url();
}

$base_url = elgg_http_remove_url_query_element($base_url, 'query');
$base_url = elgg_http_remove_url_query_element($base_url, 'sort');
$base_url = elgg_http_remove_url_query_element($base_url, 'limit');
$base_url = elgg_http_remove_url_query_element($base_url, elgg_extract('offset_key', $options, 'offset'));

$form = elgg_view_form('user/sort', array(
	'action' => $base_url,
	'method' => 'GET',
	'disable_security' => true,
		), $vars);

list($sort_field, $sort_direction) = explode('::', $sort);
$options = user_sort_add_sort_options($options, $sort_field, $sort_direction);

if (!empty($query) && elgg_is_active_plugin('search')) {
	$options['query'] = $query;
	if (version_compare(elgg_get_version(true), '2.1', '>=')) {
		// search hooks in earlier versions reset 'joins' and 'wheres' and 'order_by'
		$results = elgg_trigger_plugin_hook('search', 'user', $options, array());
		$entities = elgg_extract('entities', $results);
		$list = elgg_view_entity_list($entities, $options);
	} else {
		$options = user_sort_add_search_query_options($options, $query);
		$list = call_user_func($callback, $options);
	}
} else {
	$list = call_user_func($callback, $options);
}

// make sure it's not an empty list with no results <p>
if (empty($query) && !preg_match_all("/<ul.*>.*<\/ul>/s", $list)) {
	echo $list;
	return;
}

$id = elgg_extract('list_id', $options);
if (!$id) {
	$id = md5($base_url);
}

echo elgg_format_element('div', [
	'id' => "user-sort-$id",
	'class' => 'user-sort-list',
		], $form . $list);
