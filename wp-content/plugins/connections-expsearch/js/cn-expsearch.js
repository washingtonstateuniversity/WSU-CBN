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
			navigator.geolocation.getCurrentPosition(function(Pos){
					//var cn_search_form_url = "http://cbn.wsu.edu/wordpress/cbn-search/";
					$.ajax({
						type: "POST",
						url: cn_search_form_url,
						cache: false,
						data: {
							"cn-cat	":"",
							"cn-state":"",
							"cn-near_addr":"",
							"cn-latitude":""+Pos.coords.latitude,
							"cn-longitude":""+Pos.coords.longitude,
							"cn-radius":"10",
							"cn-unit":"mi",
							"start_search":"Submit"
						},
						success: function(data, textStatus, jqXHR){
							var count = $(data).find('.cn-entry').length;
							//alert("found "+count+" locations");
							
							$('body').append('<div id="location_alert">There are '+count+' businesses that are near you <a href="#" id="veiw_locations">Click to  veiw them.</a> <a href="#" id="close_alert">[x]</a></div>');
							$('#location_alert').slideDown();
							$('#close_alert').off().on("click", function(e){
								e.preventDefault();
								$('#location_alert').slideUp();
							});
							var Form = '<form id="location_search_target" action="'+cn_search_form_url+'" enctype="multipart/form-data" method="POST" style="height:0px;width:0px; overflow:hidden;"><input type="hidden" name="location_alert" value="true"><input type="hidden" name="cn-cat"><input type="hidden" name="cn-state"><input type="hidden" name="cn-near_addr"><input type="hidden" name="cn-latitude" value="'+Pos.coords.latitude+'"><input type="hidden" name="cn-longitude" value="'+Pos.coords.longitude+'"><input type="hidden" name="cn-radius" value="10"><input type="hidden" name="cn-unit" value="mi"><input type="submit" name="start_search" value="Submit"></form>';
							$('body').append(Form);
							$('#veiw_locations').off().on("click", function(e){
								e.preventDefault();
								//$("#location_search_target").submit();
								$("#location_search_target").find('[type="submit"]').trigger('click');
							});
							
							
						}
					});
				},showError);
		}

		
		if( $('html').is($('.geolocation '))){
			$('#mylocation').on('click',function(e){
				//alert();
				e.preventDefault();
				getLocation();
			});
			if (navigator.geolocation && $('.location_posted').length<=0 && $('#cn-form').length<=0){
				setup_location_alert();
			}
		}else{
			$('#mylocation').closest('h2').remove();
		}
	
	});
}(this,document,jQuery));