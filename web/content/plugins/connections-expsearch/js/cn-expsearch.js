( function(obj,document,$){
	jQuery(document).ready(function ($) {
		function getLocation(){
			$('#mylocation').closest('h2').append('<span> (looking up now) </span>');
			if (navigator.geolocation){
				navigator.geolocation.getCurrentPosition(setPosition,showError);
			} else {
				$('#mylocation').closest('h2').html("Geolocation is not supported by this browser.");
			}
		}
		function setPosition(position) {
			$('#mylocation').closest('h2').html('Searching in a 50mi radius of your current location <a href="#" id="clearLocation"> ( [x] clear ) </a>');
			$('[name="cn-latitude"]').val(position.coords.latitude);
			$('[name="cn-longitude"]').val(position.coords.longitude);
			$('#clearLocation').off().on('click',function(e){
				e.preventDefault();
				clearPosition();
			});	
		}
		function clearPosition() {
			$('#mylocation').closest('h2').html('<a id="mylocation" style="" hidefocus="true" href="#">Search near my location</a>');
			$('[name="cn-latitude"]').val('');
			$('[name="cn-longitude"]').val('');
			$('#mylocation').off().on('click',function(e){
				//alert();
				e.preventDefault();
				getLocation();
			});	
		}
		function showError(error) {
		  switch(error.code) {
			case error.PERMISSION_DENIED:
			  $('#mylocation').closest('h2').html("User denied the request for Geolocation.");
			  break;
			case error.POSITION_UNAVAILABLE:
			  $('#mylocation').closest('h2').html("Location information is unavailable.");
			  break;
			case error.TIMEOUT:
			  $('#mylocation').closest('h2').html("The request to get user location timed out.");
			  break;
			case error.UNKNOWN_ERROR:
			  $('#mylocation').closest('h2').html("An unknown error occurred.");
			  break;
			}
		}
		
		function setup_location_alert(){
			//alert('I would have suggested something to you.');
		}
			
		
		
		
		if( $('html').is($('.geolocation '))){
			$('#mylocation').on('click',function(e){
				//alert();
				e.preventDefault();
				getLocation();
			});
			if (navigator.geolocation){
				setup_location_alert();
			}
		}else{
			$('#mylocation').closest('h2').remove();
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	});
}(this,document,jQuery));