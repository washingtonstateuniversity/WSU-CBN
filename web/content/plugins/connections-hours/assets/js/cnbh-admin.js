;jQuery(document).ready(function ($) {

	/**
	 * Business Hours Add / Remove period.
	 */
	var CNBH_Period = {

		init : function() {
			this.clone();
			this.add();
			this.remove();
			this.reindex();
		},
		clone : function( day, period ) {

			// Clone the hidden row.
			clone = $( '#cnbh-period' ).clone( true );

			// Change the id and name attributes to the supplied day/period variable.
			clone.find( 'input' ).each( function() {

				var name = $( this ).attr( 'name' );

				name = name.replace( /(\[day\]\[period\])/, '[' + parseInt( day ) + '][' + parseInt( period ) + ']' );

				// Disable manual text entry on the time inputs.
				// Bind the timepicker to the inputs.
				// Set the name and id attributes.
				$( this ).attr( 'name', name ).attr( 'id', name ).addClass('cn-timepicker').timepicker( cnbhDateTimePickerOptions ).prop( 'readonly', true );
			});

			// Add the data-key attribute to the buttons.
			clone.find('.button').attr( 'data-day', day ).attr( 'data-period', period );

			// Remove the #cnbh-period id attrribute and add teh cnbh-day-# class and then unhide the cloned <tr>.
			clone.removeAttr('id').addClass( 'cnbh-day-' + day ).toggle();

			return clone;
		},
		add : function() {

			$('.cnbh-add-period').on('click', function() {

				day  = $( this ).attr('data-day');
				row  = $( this ).closest('tr');

				// Increment the period counter for the day.
				data = $('#cnbh-day-' + day ).cnCount( 1 ).data();

				// Insert the cloned row after the current row.
				row.after( CNBH_Period.clone( day, data.count ) );

				// Hide the "+" button that was clicked.
				// $( this ).toggle();

				// After adding a row, the periods need to be reindexed.
				CNBH_Period.reindex( day );
			});
		},
		remove : function() {

			$('.cnbh-remove-period').on('click', function() {

				day = $( this ).attr('data-day'); //
				row = $( this ).closest('tr');

				// Decrement the period counter for the day.
				data = $('#cnbh-day-' + day ).cnCount( -1 ).data();

				// If all period except the base period has been removed;
				// show the "+" button so a new period can be added.
				// if ( data.count == 0 ) {

				// 	row.prev().find('.button.cnbh-add-period').toggle();
				// }

				row.remove();

				// After removing a row, the periods need to be reindexed.
				CNBH_Period.reindex( day );
			});
		},
		reindex : function( day ) {

			// Process each row of the specified day.
			$( '.cnbh-day-' + day ).each( function( i, row ) {

				// In each row find the inputs.
				$( row ).find( 'input' ).each( function() {

					// Grab the name of the current row being processed.
					var name = $( this ).attr( 'name' );

					// Replace the name with the current day and index.
					name = name.replace( /\[(\d+)\]\[(\d+)\]/, '[' + parseInt( day ) + '][' + parseInt( i ) + ']' );

					// Update both the name and id attributes with the new day and index.
					$( this ).attr( 'name', name ).attr( 'id', name );
				});

			});
		}
	}

	CNBH_Period.init();

	// Disable manual text entry on the time inputs.
	// Bind the timepicker to the inputs.
	$('.cn-timepicker').timepicker(	cnbhDateTimePickerOptions ).prop( 'readonly', true );

	// Counter Functions Credit:
	// http://stackoverflow.com/a/5656660
	$.fn.cnCount = function( val ) {

		return this.each( function() {

			var data = $( this ).data();

			if ( ! ( 'count' in data ) ) {

				data['count'] = 0;
			}

			data['count'] += val;
		});
	};

});
