<?php

$options = (array) elgg_extract('options', $vars);
$callback = elgg_extract('callback', $vars, 'elgg_list_entities');

if (!is_array($options) || empty($options) || !is_callable($callback)) {
	return;
}

$sort = elgg_extract('sort', $options, get_input('sort', 'alpha::asc'));
$base_url = elgg_extract('base_url', $options);
if (!$base_url) {
	$base_url = current_page_url();
}

$base_url = elgg_http_remove_url_query_element($base_url, 'sort');
$base_url = elgg_http_remove_url_query_element($base_url, 'limit');
$base_url = elgg_http_remove_url_query_element($base_url, elgg_extract('offset_key', $options, 'offset'));

$form = elgg_view_form('user/sort', array(
	'action' => $base_url,
	'method' => 'GET',
	'disable_security' => true,
		), array(
	'sort' => $sort,
		));

list($sort_field, $sort_direction) = explode('::', $sort);
$options = user_sort_add_sort_options($options, $sort_field, $sort_direction);

$list = call_user_func($callback, $options);

// make sure it's not an empty list with no results <p>
if (!preg_match_all("/<ul.*>.*<\/ul>/s", $list)) {
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
