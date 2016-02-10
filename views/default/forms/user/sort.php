<?php
$sort_options = user_sort_get_sort_options();
if (empty($sort_options)) {
	return;
}

$sort_options_values = array();
foreach ($sort_options as $sort_option) {
	$sort_options_values[$sort_option] = elgg_echo("user:sort:$sort_option");
}

echo elgg_view_input('select', array(
	'name' => 'sort',
	'value' => elgg_extract('sort', $vars, 'time_created::desc'),
	'options_values' => $sort_options_values,
	'class' => 'user-sort-select',
	'label' => elgg_echo('user:sort:label'),
	'field_class' => 'user-sort-select-field',
));

echo elgg_view_input('submit', array(
	'class' => 'hidden',
));
?>
<script>
	require(['forms/user/sort']);
</script>