define(function(require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var spinner = require('elgg/spinner');
	
	$(document).on('submit', '.elgg-form-user-sort', function(e) {
		var $form = $(this);
		var $container = $form.closest('.user-sort-list');

		elgg.get($form.attr('action'), {
			data: $form.serialize(),
			beforeSend: spinner.start,
			complete: spinner.stop,
			success: function(output) {
				var id = $container.attr('id');
				var $new = $(output).find('#' + id);
				$container.replaceWith($new);
			}
		});

		return false;
	});

	$(document).on('change', '.user-sort-select', function(e) {
		$(this).closest('form').trigger('submit');
	});
	
});