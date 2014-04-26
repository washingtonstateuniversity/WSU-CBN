var pullman = new google.maps.LatLng(46.73191920826778,-117.15296745300293);
var basemark;
var ib = [];
var ibh = [];
var markerLog = [];
var shapes = [];

var ibHover = false;
jQuery.noConflict(); //:-\
jQuery(document).ready(function($) {

jQuery('.AffiliateChoice').on("change",function(){
	if(jQuery(this).val()==0){
		jQuery('.gradinfo').show();
	}else{
		jQuery('.gradinfo').hide();
	}
});



function prep(){
	jQuery(' [placeholder] ').defaultValue();
	jQuery("a").each(function() {jQuery(this).attr("hideFocus", "true").css("outline", "none");});
}
function centerOnAddress(map,add,city,state,zip,contry,calllback){
	
	var address =   add + ' '
					+ city + ' '
					+ state + ' '
					+ zip + ' '
					+ ( contry==''?' USA':contry );
	geocoder = new google.maps.Geocoder();
	geocoder.geocode( { 'address': address }, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
				if (results && results[0]&& results[0].geometry && results[0].geometry.viewport) 
				map.fitBounds(results[0].geometry.viewport);
				if(typeof(calllback)!=="undefined"){ calllback(results[0].geometry.location.lat(),results[0].geometry.location.lng() ) }
			}else{
				//alert('ERROR:'+status);
			}
		}else{
			//alert('ERROR:'+status);
		}
	});
}

function iniMap(url,callback){	
	var json = jQuery.parseJSON(jQuery('#mapJson').text());
	var map_op = {'zoom':15};//, "center":pullman };
	map_op = jQuery.extend(map_op,{"mapTypeControl":false,"panControl":false});
	jQuery('#front_cbn_map').gmap(map_op).bind('init', function() { 
		var map = jQuery('#front_cbn_map').gmap("get","map");
		jQuery.each(json.markers,function(i,marker){
			
			
			var boxText = document.createElement("div");
			boxText.style.cssText = "border: 1px solid rgb(102, 102, 102); background: none repeat scroll 0% 0% rgb(226, 226, 226); padding: 2px; display: inline-block; font-size: 10px !important; font-weight: normal !important;";
			boxText.innerHTML = "<h3 style='font-weight: normal !important; padding: 0px; margin: 0px;'>"+marker.title+"</h3>";
			var myHoverOptions = {
				alignBottom:true,
				 content: boxText//boxText
				,disableAutoPan: false
				,pixelOffset: new google.maps.Size(15,-15)
				,zIndex: 999
				,boxStyle: {
					minWidth: "250px"
				 }
				,infoBoxClearance: new google.maps.Size(1, 1)
				,isHidden: false
				,pane: "floatPane"
				,boxClass:"hoverbox"
				,enableEventPropagation: false
				,disableAutoPan:true
				,onOpen:function(){}
				
			};
			ib[i] = marker.id;
			ibh[i] = new InfoBox(myHoverOptions,function(){});
			jQuery('#front_cbn_map').gmap('addMarker', jQuery.extend({ 
				'position': new google.maps.LatLng(marker.position.latitude, marker.position.longitude),
				'z-index':1,
				'bounds':true,
				'icon':THEME_PATH+'/img/biz_map_icon.png'
			},{}),function(ops,marker){
				markerLog[i]=marker;
			})
			.click(function(){
				var htmlDiv = jQuery('[data-id='+ib[i]+']').html();

				jQuery('#data_display').html(htmlDiv);
				jQuery('#data_display').find('.cn-bio-anchor').remove();
				jQuery('#data_display').find('.cn-map-anchor').remove();
			})
			.mouseover(function(event){
				jQuery.each(ibh, function(i) {ibh[i].close();});
				jQuery('.infoBox').hover( 
					function() { ibHover =  true; }, 
					function() { ibHover =  false;  } 
				); 
				if(ibHover!=true)ibh[i].open(jQuery('#front_cbn_map').gmap('get','map'), markerLog[i]);
			})
			.mouseout(function(event){jQuery.each(ibh, function(i) {ibh[i].close();});});
		});
		$('#front_cbn_map').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers'), {
		  maxZoom: null,
          gridSize: 60,
          styles: [{
					url: THEME_PATH+'/img/m1.png',
					height: 52,
					width: 53,
					anchor: [15, 0],
					textColor: '#ffffff',
					fontWeight:"bold",
					textSize: 15
				  }, {
					url: THEME_PATH+'/img/m2.png',
					height: 55,
					width: 56,
					anchor: [15, 0],
					textColor: '#c2c2c2',
					fontWeight:"bold",
					padding:"10px 0px 0px 0px",
					textSize: 15
				  }, {
					url: THEME_PATH+'/img/m3.png',
					height: 65,
					width: 66,
					anchor: [25, 0],
					textColor: '#d2d2d2',
					fontWeight:"bold",
					padding:"10px 0px 0px 0px",
					textSize: 22
				  }]
			
			}));

		if(jQuery('#front_cbn_map.byState').length>0 || jQuery('#front_cbn_map.byCountry').length>0){
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': jQuery('#front_cbn_map').attr('rel')}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
						if (results && results[0]&& results[0].geometry && results[0].geometry.viewport) 
						map.fitBounds(results[0].geometry.viewport);
					}
				}
			});
		}
		
	});
	return jQuery('#front_cbn_map');
}


function iniSingleMap(obj,callback){
	var mapObj = obj.find('.map')	
	var json = jQuery.parseJSON(obj.find('.mapJson').text());
	var map_op = {'zoom':12};
	map_op = jQuery.extend(map_op,{"mapTypeControl":false,"panControl":false});
	mapObj.gmap(map_op).bind('init', function() { 
		var map = mapObj.gmap("get","map");
		jQuery.each(json.markers,function(i,marker){
			
			
			var boxText = document.createElement("div");
			boxText.style.cssText = "border: 1px solid rgb(102, 102, 102); background: none repeat scroll 0% 0% rgb(226, 226, 226); padding: 2px; display: inline-block; font-size: 10px !important; font-weight: normal !important;";
			boxText.innerHTML = "<h3 style='font-weight: normal !important; padding: 0px; margin: 0px;'>"+marker.title+"</h3>";
			var myHoverOptions = {
				alignBottom:true,
				 content: boxText//boxText
				,disableAutoPan: false
				,pixelOffset: new google.maps.Size(15,-15)
				,zIndex: 999
				,boxStyle: {
					minWidth: "250px"
				 }
				,infoBoxClearance: new google.maps.Size(1, 1)
				,isHidden: false
				,pane: "floatPane"
				,boxClass:"hoverbox"
				,enableEventPropagation: false
				,disableAutoPan:true
				,onOpen:function(){}
				
			};
			ibh[i] = new InfoBox(myHoverOptions,function(){});
			mapObj.gmap('addMarker', jQuery.extend({ 
				'position': new google.maps.LatLng(marker.position.latitude, marker.position.longitude),
				'z-index':1,
				'bounds':true,
				'icon':THEME_PATH+'/img/biz_map_icon.png'
			},{}),function(ops,marker){
				markerLog[i]=marker;
				mapObj.gmap("setOptions",{'zoom':13});
			}).mouseover(function(event){
				jQuery.each(ibh, function(i) {ibh[i].close();});
				jQuery('.infoBox').hover( 
					function() { ibHover =  true; }, 
					function() { ibHover =  false;  } 
				); 
				if(ibHover!=true)ibh[i].open(mapObj.gmap('get','map'), markerLog[i]);
			})
			.mouseout(function(event){jQuery.each(ibh, function(i) {ibh[i].close();});});
		});
		/*
		if(jQuery('#front_cbn_map.byState').length>0 || jQuery('#front_cbn_map.byCountry').length>0){
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': jQuery('#front_cbn_map').attr('rel')}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
						if (results && results[0]&& results[0].geometry && results[0].geometry.viewport) 
						map.fitBounds(results[0].geometry.viewport);
					}
				}
			});
		}
		*/
	});
}




	if(jQuery('#tabs').length>0){
		jQuery( "#tabs" ).tabs({
			activate: function( event, ui ) {
				if(ui.newPanel.attr('id')=="tabs-1"){
					//map.gmap("refresh");
					var map = iniMap();
				}else{
						
				}
			}
		});
		$(".accordion" ).accordion({collapsible: true,active:false,heightStyle: "content"});
/*
		jQuery.each(jQuery('.accordion'),function(){
			jQuery(this).accordion({collapsible: true,active: false, heightStyle: "content" ,
				activate: function(event, ui) { 
					if(ui.oldPanel.length)ui.oldPanel.find('.map').gmap('destroy');
					if(ui.newPanel.length)iniSingleMap(ui.newPanel);
				 }
			 });
		});
		
	jQuery('.more').on('click',function(e){
		e.stopPropagation();
		e.preventDefault();
		jQuery(jQuery(this).attr("href")).toggle("showOrHide",function(){
					if(!jQuery(this).is(':visible'))jQuery(this).find('.map').gmap('destroy');
					if(jQuery(this).is(':visible'))iniSingleMap(jQuery(this));
			
			});
	});
		
		*/
		
		
		//jQuery('.accordion').accordion({ header: '.biz',collapsible: true,active: false });/**/
	}
	
	if(jQuery('#cbn_map').length){
	
		var lat = jQuery('#lat').val();
		var lng = jQuery('#lng').val();
		var business_City = '';
		var business_state = '';
		var business_Zip = '';
		var business_country_CountryAbbr = '';
		
		
		
		
		
		jQuery('#cbn_map').gmap({
			'center': (typeof(lat)==='undefined' || lat=='')? pullman : new google.maps.LatLng(lat,lng),
			'zoom':15,
			'zoomControl': false,
			'mapTypeControl': {  panControl: true,  mapTypeControl: true, overviewMapControl: true},
			'panControlOptions': {'position':google.maps.ControlPosition.LEFT_BOTTOM},
			'streetViewControl': false 
		}).bind('init', function () {
			
			function makeMapChange(){
				business_Address1 = jQuery('#business_Address1').val();
				business_City = jQuery('#business_City').val();
				business_state = jQuery('#business_state').val();
				business_Zip = jQuery('#business_Zip').val();
				business_country_CountryAbbr = jQuery('#business_country_CountryAbbr').val();
				
				var map = jQuery('#cbn_map').gmap("get","map");
				centerOnAddress(map,business_Address1,business_City,business_state,business_Zip,business_country_CountryAbbr,function(lat,lng){
					jQuery('#cbn_map').gmap("setOptions",{position:new google.maps.LatLng(lat,lng)},markerLog[0]);
					jQuery('#lat').val(lat);
					jQuery('#lng').val(lng);
				});
			}

			jQuery("input[name='business.Address1']").on("change",function(){makeMapChange()});
			jQuery("input[name='business.Address1']").on("blur",function(){makeMapChange()});
			jQuery("input[name='business.Address1']").on("mouseup",function(){makeMapChange()});
			jQuery("input[name='business.Address1']").on("keyup",function(){makeMapChange()});
			
			jQuery("input[name='business.City']").on("change",function(){makeMapChange()});
			jQuery("input[name='business.City']").on("blur",function(){makeMapChange()});
			jQuery("input[name='business.City']").on("mouseup",function(){makeMapChange()});
			jQuery("input[name='business.City']").on("keyup",function(){makeMapChange()});
			
			jQuery("input[name='business.Zip']").on("change",function(){makeMapChange()});
			jQuery("input[name='business.Zip']").on("blur",function(){makeMapChange()});
			jQuery("input[name='business.Zip']").on("mouseup",function(){makeMapChange()});
			jQuery("input[name='business.Zip']").on("keyup",function(){makeMapChange()});
			
			jQuery("select[name='business.state']").on("change",function(){makeMapChange()});
			jQuery("select[name='business.state']").on("blur",function(){makeMapChange()});
			jQuery("select[name='business.state']").on("mouseup",function(){makeMapChange()});
			jQuery("select[name='business.state']").on("keyup",function(){makeMapChange()});
			
			
			jQuery("select[name='business.country.CountryAbbr']").on("change",function(){makeMapChange()});
			jQuery("select[name='business.country.CountryAbbr']").on("blur",function(){makeMapChange()});
			jQuery("select[name='business.country.CountryAbbr']").on("mouseup",function(){makeMapChange()});
			jQuery("select[name='business.country.CountryAbbr']").on("keyup",function(){makeMapChange()});
			

			jQuery('#cbn_map').gmap('addMarker', jQuery.extend({ 
				'position': (typeof(lat)==='undefined' || lat=='')?pullman:new google.maps.LatLng(lat,lng),
				'icon':THEME_PATH+'/img/biz_map_icon.png'
			},{'draggable':true}),function(markerOptions, marker){
				markerLog[0]=marker;

			}).click(function() {

			}).dragend(function(e) {
				var placePos = this.getPosition();
				var lat = placePos.lat();
				var lng = placePos.lng();
				jQuery('#lat').val(lat);
				jQuery('#lng').val(lng);
			});
			
			if( (jQuery('#lat').val() == "" || jQuery('#lng').val() == "") && jQuery("input[name='business.Zip']").val() !='' ){
				makeMapChange();
			}
			
			
			
		});
	}
		
		
		
		

		
		prep();
});

function disableFields(selected){
	if(selected){
		jQuery('#gradinfo').hide();
	}else{
		jQuery('#gradinfo').show();
	}
}


jQuery(document).ready(function(){
	
	
	if(jQuery('#cn-form').length>0){
		jQuery('#cn-metabox-metabox-address .cn-add').trigger('click');
		jQuery('[name="address[preferred]"]').attr('checked',true);
		jQuery('select[id^="cn-address"][name$="[type]"]').val('work');
		jQuery('input[id^="cn-address"][name$="[line_1]"]').attr('required','required');
		jQuery('input[id^="cn-address"][name$="[city]"]').attr('required','required');
		jQuery('input[id^="cn-address"][name$="[state]"]').attr('required','required');
		jQuery('input[id^="cn-address"][name$="[zipcode]"]').attr('required','required');
		jQuery('input[id^="cn-address"][name$="[country]"]').attr('required','required');
		
		
		jQuery('[name="contact_first_name"]').attr('required','required');
		jQuery('[name="contact_last_name"]').attr('required','required');
		jQuery('#cn-department').attr('required','required');
		
		jQuery('label[for="cn-department"]').text('Title');
		
		
		jQuery('#cn-metabox-categorydiv .cn-metabox-title').text('Category : (Choose all that apply)');
		
		
		
		jQuery('#cn-metabox-metabox-email .cn-add').trigger('click');
		jQuery('input[id^="cn-email"][name$="[address]"]').attr('required','required');
		jQuery('[name="email[preferred]"]').attr('checked',true);
		jQuery('select[id^="cn-email"][name$="[type]"]').val('work');
		
		jQuery('#cn-metabox-metabox-bio .cn-metabox-title').text('Business Description');
		
		
	}
	
	
	/*
	jQuery('#cbn-logo').delay(900).animate({
		width:"326px",
		left: "-113px"
	},1500,"jswing",function(){
		
	});*/
	jQuery('[href="#more"]').on('click',function(e){
		e.stopPropagation();
		e.preventDefault();
			
		if(jQuery(".expoArea.active").length){
			jQuery('#more').css("display","none");
			}
			
		jQuery('#more').toggle("showOrHide");
		if(jQuery('.expoArea').is(jQuery(".active"))){
			jQuery('a[href="#more"]').html(" Read More ....");
			jQuery('.expoArea').removeClass("active");
		}else{
			jQuery('a[href="#more"]').html(" &laquo;Less");
			jQuery('.expoArea').addClass("active");
		}
	});
	
	
	
	
( function( $ ) {
	$(document).ready( function($) {

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
		
		/* 
		* Geocode the address
		*/
		var uid = jQuery('a.geocode').data('uid');
		
		function startMap(){
			jQuery( '#map-' + uid ).goMap({ maptype: 'ROADMAP' });
			var address = new Object();
			address	= $.extend(
						{
							line_1:'Wilson Rd',
							city:'Pullman',
							state:'WA',
							zipcode:"99163"
						},
						address
					);
			$.goMap.createMarker({
				address: '\'' 
						+ address.line_1 + ', ' 
						+ address.city + ', ' 
						+ address.state + ', ' 
						+ address.zipcode + ', ' 
						+  '\'' ,
				 id: 'baseMarker',
				 draggable: true
			});

		}
		if($('[id*="#map"]').length){
			startMap();
		}
		
		
		

		function alterMap(uid){
			var lat;
			var lng;
			var map = $.goMap.map;

			var address = new Object();
			var line_1 = $('input[name=address\\[' + uid + '\\]\\[line_1\\]]').val();
			if(line_1!=""){address.line_1 = line_1;}
			
			
			var line_2 = $('input[name=address\\[' + uid + '\\]\\[line_2\\]]').val();
			if(line_2!=""){address.line_2 = line_2;}
			
			var line_3 = $('input[name=address\\[' + uid + '\\]\\[line_3\\]]').val();
			if(line_3!=""){address.line_3 = line_3;}
			
			var city = $('input[name=address\\[' + uid + '\\]\\[city\\]]').val();
			if(city!=""){address.city = city;}
			
			var state = $('input[name=address\\[' + uid + '\\]\\[state\\]]').val();
			if(state!=""){address.state = state;}
			
			var zipcode = $('input[name=address\\[' + uid + '\\]\\[zipcode\\]]').val();
			if(zipcode!=""){address.zipcode = zipcode;}
			
			var country = $('input[name=address\\[' + uid + '\\]\\[country\\]]').val();
			if(country!=""){address.country = country;}

			address	= $.extend(
						{
							line_1:'Wilson Rd',
							city:'Pullman',
							state:'WA',
							zipcode:"99163"
						},
						address
					);
			$.goMap.clearMarkers();
			$.goMap.createMarker({
				address: '\'' 
						+ address.line_1 + ', ' 
						+ address.city + ', ' 
						+ address.state + ', ' 
						+ address.zipcode + ', ' 
						+  '\'' ,
				 id: 'baseMarker',
				 draggable: true
			});
			
			
			$.goMap.setMap({ 
				address: '\'' 
						+ address.line_1 + ', ' 
						+ address.city + ', ' 
						+ address.state + ', ' 
						+ address.zipcode + ', ' 
						+  '\'' ,
				zoom: 18
			});
			
			$.goMap.createListener( {type:'marker', marker:'baseMarker'} , 'idle', function(event) {
				var lat = event.latLng.lat();
				var lng = event.latLng.lng();
				$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val(lat);
				$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val(lng);
			});
			
			$.goMap.createListener( {type:'marker', marker:'baseMarker'} , 'dragend', function(event) {
				var lat = event.latLng.lat();
				var lng = event.latLng.lng();
				$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val(lat);
				$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val(lng);
			});
			
			google.maps.event.trigger(map, 'resize');
			
			// There has to be a better way than setting a delay. I know I have to use a callback b/c the geocode is an asyn request.
			setTimeout( function(){
					var latLng = map.getCenter();
					map.setCenter(latLng);
					var baseMarkerPosition = $( '#map-' + uid ).data('baseMarker').getPosition();
					$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val( baseMarkerPosition.lat() );
					$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val( baseMarkerPosition.lng() );		
			}, 1500);
		}
	
		
		jQuery('.widget.address input,.widget.address select').on('change',function(){
			alterMap(uid);
		});	
		jQuery('a.geocode.button').live('click', function() {
			$( '#map-' + uid ).fadeIn('slow' , function() {
				alterMap(uid);
			});
			return false;
		 });

	});
} )( jQuery );
	
	
});