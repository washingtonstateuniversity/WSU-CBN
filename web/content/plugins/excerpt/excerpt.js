jQuery(document).ready(function ($) {

	// As the page is rendered the excerpt is hidden via the CSS style. Hide the details and show the excerpt.
	$('.cn-hide').css('display', 'none');
	$('.cn-excerpt').css('display', 'block');

	$('h3.cn-accordion-item,span.cn-show-more').click( function() {
		var $this = $(this);
		var uuid = $this.attr('data-uuid');

		if ( $( '#cn-detail-' + uuid ).css('display') == 'block' ) {

			// Hide detail; show excerpt.
			$( '#cn-detail-' + uuid ).slideUp( 1000, function() {
				$( '#cn-excerpt-' + uuid ).fadeIn(1000);
				$( '#cn-toggle-' + uuid ).toggleClass('cn-open');
			});

		} else {

			// Hide excerpt; show detail.
			$( '#cn-excerpt-' + uuid ).fadeOut( 1000, function() {
				$( '#cn-detail-' + uuid ).slideDown(1000);
				$( '#cn-toggle-' + uuid ).toggleClass('cn-open');
			});
		}

	});

	$('select[name^=cn-cat]').chosen();
});