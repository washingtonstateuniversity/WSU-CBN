jQuery(document).ready(function ($) {

	$('a.toggle-map').click( function() {
		var $this = $(this);
		var uuid = $this.attr('data-uuid');
		var strShow = $this.attr('data-str-show');
		var strHide = $this.attr('data-str-hide');
		var fLatitude = 0;
		var fLongitude = 0;

		if ( $this.data('toggled') ) {

			$this.data('toggled', false);
			//$( '#map-container-' + uuid ).slideUp('normal', function() { $(this).remove() } );
			$( '#map-container-' + uuid ).slideUp();
			$this.text( strShow );

		} else {

			$this.data('toggled', true);
			$this.text( strHide );
			//$( $this.attr('data-gmap') ).appendTo( '#entry-id-' + uuid );
			$( '#map-container-' + uuid ).fadeIn();

			var strAddress = $( '#map-' + uuid ).attr('data-address');
			var strMapType = $( '#map-' + uuid ).attr('data-maptype');
			var intZoom = parseInt( $( '#map-' + uuid ).attr('data-zoom') );
			if ( $('#map-' + uuid ).attr('data-latitude') ) fLatitude = parseFloat( $( '#map-' + uuid ).attr('data-latitude' ) );
			if ( $('#map-' + uuid ).attr('data-longitude') ) fLongitude = parseFloat( $( '#map-' + uuid ).attr('data-longitude') );

			//console.log(fLatitude);
			//console.log(fLongitude);

			if ( fLatitude == 0 && fLatitude == 0 ) {

				$( '#map-' + uuid ).goMap({
					markers: [ { address: '\'' + strAddress + '\'' } ] ,
					zoom: intZoom,
					maptype: strMapType
				});

			} else {

				$( '#map-' + uuid ).goMap({
					markers: [ { latitude: fLatitude , longitude: fLongitude } ] ,
					zoom: intZoom,
					maptype: strMapType
				});

			}

		}

		return false
	});

	$('a.toggle-div').click( function() {
		var $this = $(this);
		var uuid = $this.attr('data-uuid');
		var strShow = $this.attr('data-str-show');
		var strHide = $this.attr('data-str-hide');
		var div = $this.attr('data-div-id');

		if ( $this.data('toggled') ) {

			$this.data('toggled', false);
			$this.text( strShow );
			$( '#' + div ).slideUp();

		} else {

			$this.data('toggled', true);
			$this.text( strHide );
			$( '#' + div ).slideDown();

		}

		return false
	});

	$(".accordion" ).accordion({collapsible: true,active:false,heightStyle: "content"});
	$('select[name^=cn-cat]').chosen();
	// Render map on single entry page
	var cnSingleMap = $('#cn-gmap-single').length ? $('#cn-gmap-single') : false;

	if ( cnSingleMap != false ) {
		var fLatitude = 0;
		var fLongitude = 0;
		var uuid = cnSingleMap.attr('data-gmap-id');
		var strAddress = $( '#map-' + uuid ).attr('data-address');
		var strMapType = $( '#map-' + uuid ).attr('data-maptype');
		var intZoom = parseInt( $( '#map-' + uuid ).attr('data-zoom') );

		if ( $('#map-' + uuid ).attr('data-latitude') ) fLatitude = parseFloat( $( '#map-' + uuid ).attr('data-latitude' ) );
		if ( $('#map-' + uuid ).attr('data-longitude') ) fLongitude = parseFloat( $( '#map-' + uuid ).attr('data-longitude') );

		if ( fLatitude == 0 && fLatitude == 0 ) {

			$( '#map-' + uuid ).goMap({
				markers: [ { address: '\'' + strAddress + '\'' } ] ,
				zoom: intZoom,
				maptype: strMapType
			});

		} else {

			$( '#map-' + uuid ).goMap({
				markers: [ { latitude: fLatitude , longitude: fLongitude } ] ,
				zoom: intZoom,
				maptype: strMapType
			});

		}

	}

});