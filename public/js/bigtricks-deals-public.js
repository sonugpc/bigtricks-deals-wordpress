(function( $ ) {
	'use strict';

	$(function() {
		$('body').on('click', '.bt-deals-load-more', function(e) {
			e.preventDefault();

			var button = $(this);
			var wrapper = button.closest('.bt-deals-load-more-wrapper');
			var container = wrapper.prev('.bt-deals-archive-grid');
			var page = parseInt(button.data('page'), 10);
			var maxPages = parseInt(button.data('max-pages'), 10);
			var atts = button.data('atts');

			button.prop('disabled', true).text('Loading...');

			$.ajax({
				url: btdeals_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'load_more_deals',
					page: page,
					atts: atts,
					nonce: btdeals_ajax.nonce
				},
				success: function(response) {
					if (response.success) {
						container.append(response.data);
						button.data('page', page + 1);
						if (page + 1 > maxPages) {
							button.text('No More Deals').prop('disabled', true);
						} else {
							button.prop('disabled', false).text('Load More Deals');
						}
					} else {
						button.text('No More Deals').prop('disabled', true);
					}
				},
				error: function() {
					button.text('Error');
				}
			});
		});
	});

})( jQuery );
