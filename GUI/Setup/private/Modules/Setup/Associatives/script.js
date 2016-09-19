/**
 * setup
 *
 * @namespace #setup
 */

/**
 *
 */
$(document).ready(function(event)
{
	$('#ajax-test a').click(function(event) {
		event.preventDefault();
		$.ajax(_AJAX_URI_, {
			cache: false,
			method: 'POST',
			data: {
				page: 'setup'
			},
			success: function(response) {
				$('#ajax-test div').html(response);
			},
			error: function(response) {
				$('#ajax-test div').html('Failed with ajax request');
			}
		});
	});
});
