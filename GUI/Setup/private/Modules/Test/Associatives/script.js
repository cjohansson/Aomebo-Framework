/**
 * setup
 *
 * @namespace #test
 */

function testAjax()
{
	$('#test #ajax-tests .success').hide();
	$('#test #ajax-tests .error').hide();
	$.ajax(_AJAX_URI_, {
		cache: false,
		method: 'POST',
		data: {
			page: 'test'
		},
		success: function(given) {
			var expected = $('#test #ajax-tests .expected').html();
			$('#test #ajax-tests .given').html(given);
			if (given == expected) {
				$('#test #ajax-tests .success').show();
			} else {
				console.log('given doesnt equal expected');
				console.log(given);
				console.log(expected);
				$('#test #ajax-tests .error').show();
			}
		},
		error: function(given) {
			$('#test #ajax-tests .given').html(given);
			$('#test #ajax-tests .error').show();
		}
	});
}

/**
 *
 */
$(document).ready(function(event) {
	testAjax();
});
