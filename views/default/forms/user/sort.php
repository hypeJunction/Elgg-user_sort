<?php
$fields = '';
if (elgg_is_active_plugin('search') && elgg_extract('show_search', $vars, true)) {
	$fields .= elgg_view_input('text', array(
		'name' => 'query',
		'value' => elgg_extract('query', $vars),
		'class' => 'user-sort-query',
		'label' => elgg_echo('user:sort:search:label'),
		'field_class' => 'user-sort-query-field',
		'placeholder' => elgg_echo('user:sort:search:placeholder'),
	));
}
if (elgg_extract('show_sort', $vars, true)) {
	$sort_options = user_sort_get_sort_options();
	if (!empty($sort_options)) {
		$sort_options_values = array();
		foreach ($sort_options as $sort_option) {
			$sort_options_values[$sort_option] = elgg_echo("user:sort:$sort_option");
		}
		$fields .= elgg_view_input('select', array(
			'name' => 'sort',
			'value' => elgg_extract('sort', $vars, 'time_created::desc'),
			'options_values' => $sort_options_values,
			'class' => 'user-sort-select',
			'label' => elgg_echo('user:sort:label'),
			'field_class' => 'user-sort-select-field',
		));
	}
}

if (!$fields) {
	return;
}

echo elgg_format_element('div', [
	'class' => 'user-sort-fieldset',
		], $fields);

echo elgg_view_input('submit', array(
	'class' => 'hidden',
));
?>
<script>
	require(['forms/user/sort']);
</script>