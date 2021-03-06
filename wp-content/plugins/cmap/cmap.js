jQuery(document).ready(function ($) {

	$('a.toggle-div').off().on('click', function(e){
		//alert();
		e.preventDefault();
		var $this = $(this);
		var uuid = $this.attr('data-uuid');
		var strShow = $this.attr('data-str-show');
		var strHide = $this.attr('data-str-hide');
		var div = $this.attr('data-div-id');

		if ( $this.data('toggled') ) {
			$this.data('toggled', false);
			$this.html( '<span class="ui-button-text">'+strShow+'</span>' );
			$( '#' + div ).slideUp();
		} else {
			$this.data('toggled', true);
			$this.html( '<span class="ui-button-text">'+strHide+'</span>' );
			$( '#' + div ).slideDown();
		}
	});

	$('a.toggle-map').off().on('click', function(e){
		e.preventDefault();
		var $this = $(this), fLatitude = 0, fLongitude = 0, uuid = $this.attr('data-uuid');
		
		if ( $this.data('toggled') ) {
			$this.data('toggled', false);
			//$( '#map-container-' + uuid ).slideUp('normal', function() { $(this).remove() } );
			$( '#map-container-' + uuid ).slideUp();
			$this.html( '<span class="ui-button-text">'+$this.attr('data-str-show')+'</span>' );
		} else {
			$this.data('toggled', true);
			$this.html( '<span class="ui-button-text">'+$this.attr('data-str-hide')+'</span>' );
			//$( $this.attr('data-gmap') ).appendTo( '#entry-id-' + uuid );
			$( '#map-container-' + uuid ).fadeIn();
			var icon = ($this.attr('data-memLevel')=="member")?'biz_map_icon.png':'alf_map_icon.png';
			var strAddress = $( '#map-' + uuid ).attr('data-address');
			var strMapType = $( '#map-' + uuid ).attr('data-maptype');
			var intZoom = parseInt( $( '#map-' + uuid ).attr('data-zoom') );
			
			if ( $('#map-' + uuid ).attr('data-latitude') ) fLatitude = parseFloat( $( '#map-' + uuid ).attr('data-latitude' ) );
			if ( $('#map-' + uuid ).attr('data-longitude') ) fLongitude = parseFloat( $( '#map-' + uuid ).attr('data-longitude') );

			if ( fLatitude == 0 && fLatitude == 0 ) {
				$( '#map-' + uuid ).goMap({
					markers: [ { address: '\'' + strAddress + '\'' } ],
					icon:THEME_PATH+'/img/biz_map_icon.png',
					zoom: intZoom,
					maptype: strMapType
				});
			} else {
				$( '#map-' + uuid ).goMap({
					markers: [ { latitude: fLatitude , longitude: fLongitude } ],
					icon:THEME_PATH+'/img/'+icon,
					zoom: intZoom,
					maptype: strMapType
				});
			}

		}
	});




	//$(".accordion" ).accordion({collapsible: true,active:false,heightStyle: "content"});
	//$('#tabs').tabs();
	 //$(".buttons").button();
	
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

	function showError(error) {
	  switch(error.code) {
		case error.PERMISSION_DENIED:
		  break;
		case error.POSITION_UNAVAILABLE:
		  break;
		case error.TIMEOUT:
		  break;
		case error.UNKNOWN_ERROR:
		  break;
		}
	}
	if( $('html').is($('.geolocation '))){	
		if (navigator.geolocation){
			navigator.geolocation.getCurrentPosition(function (position) {
				var lat = position.coords.latitude;
				var long = position.coords.longitude;
				$.each($('.cn-map-get-directions'),function(){
					var url = $(this).attr('href');
					$(this).attr('href',url+'&saddr='+lat+','+long);
				});
			},showError);
		}
	}
});