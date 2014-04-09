( function( $ ) {
	$(document).ready( function($) {
	
		/**
		 * doc here;
		 * @todo  lookup jdoc spec and add doc per spec.
		 */
		var CN_Form = {
	
			init : function() {
	
				// Show/hide form fields based on the entry type.
				var type;
	
				if ( $('input[name=entry_type]').length == 1 ) {
	
					type = ( $('input[name=entry_type]').val() );
	
				} else if ( $('input[name=entry_type]').length > 1) {
	
					type = ( $('input[name=entry_type]:checked').val() );
				}
	
				switch ( type ) {
	
					case 'individual':
	
						this.show( type );
						break;
	
					case 'organization':
	
						this.show( type );
						break;
				}
	
				// Show the `individual` entry fields when the individual entry type radio is clicked.
				$( 'body' ).on( 'click', 'input[name=entry_type][value=individual]', function() {
	
					CN_Form.show( 'individual' );
				});
	
				// Show the `organization` entry fields when the organization entry type radio is clicked.
				$( 'body' ).on( 'click', 'input[name=entry_type][value=organization]', function() {
	
					CN_Form.show( 'organization' );
				});
	
				// Add repeatable entry data meta type.
				$( 'body' ).on( 'click', 'a.cn-add.cn-button', function( e ) {
	
					CN_Form.add( $( this ) );
	
					e.preventDefault();
				});
	
				// Remove repeatable entry data meta type.
				$( 'body' ).on( 'click', 'a.cn-remove.cn-button', function( e ) {
	
					CN_Form.remove( $( this ) );
	
					e.preventDefault();
				});
	
			},
			show : function( type ) {
	
				switch( type ) {
	
					case 'individual':
	
						/*
						 * Remove the 'required' class used by jQuery Validatation plugin to identify required input fields.
						 * Entry type, 'individual' does not require the 'organization' field to be entered.
						 */
						$('input[name=first_name], input[name=last_name]').addClass('required');
						$('input[name=organization]').removeClass('required error').addClass('invalid');
	
	
						$('#cn-metabox-section-name').slideDown();
						$('#cn-metabox-section-title').slideDown();
						$('#cn-metabox-section-organization').slideDown();
						$('#cn-metabox-section-department').slideDown();
						$('#cn-metabox-section-contact').slideUp();
	
						break;
	
					case 'organization':
	
						/*
						 * Add the 'required' class used by jQuery Validatation plugin to identify required input fields.
						 * Entry type, 'organization' requires the 'organization' field to be entered.
						 */
						$('input[name=organization]').addClass('required');
						$('input[name=first_name], input[name=last_name]').removeClass('required error').addClass('invalid');
	
	
						$('#cn-metabox-section-name').slideUp();
						$('#cn-metabox-section-title').slideUp();
						$('#cn-metabox-section-organization').slideDown();
						$('#cn-metabox-section-department').slideDown();
						$('#cn-metabox-section-contact').slideDown();
	
						break;
				}
	
			},
			add : function( button ) {
	
				var type = button.attr('data-type');
				var container = '#' + button.attr('data-container');
				var id = '#' + type + '-template';
				//console.log(id);
	
				var template = $(id).text();
				//console.log(template);
	
				var d = new Date();
				var token = Math.floor( Math.random() * d.getTime() );
	
				template = template.replace(
					new RegExp('::FIELD::', 'gi'),
					token
				);
				//console.log(template);
				//console.log(container);
	
				$(container).append( '<div class="widget ' + type + '" id="' + type + '-row-' + token + '" style="display: none;">' + template + '</div>' );
				$('#' + type + '-row-' + token).slideDown();
	
			},
			remove : function( button ) {
	
				var token = button.attr('data-token');
				var type = button.attr('data-type');
				var id = '#' + type + '-row-' + token;
				//alert(id);
				$(id).slideUp().remove('fast');
	
			},
			ajaxBeforeSend : function( data ) {
	
				if ( $.fn.tinyMCE ) {
	
					tinyMCE.triggerSave();
				}
			},
			ajaxSuccess : function( response, status ) {
	
				// console.log( 'CNF_Response:: ' + response );
				// alert( 'Success Occurred!' );
	
				switch ( response ) {
	
					case -3:
	
						$('#cn-form-ajax-response').html('<span id="cn-form-message"><p>' + cn_form.strTokenErrMsg + '</p></span>').fadeIn(1500);
						break
	
					case -2:
	
						$('#cn-form-ajax-response').html('<span id="cn-form-message"><p>' + cn_form.strErrMsg + '</p></span>').fadeIn(1500);
						break
	
					case -1:
	
						$('#cn-form-ajax-response').html('<span id="cn-form-message"><p>' + cn_form.strNonceErrMsg + '</p></span>').fadeIn(1500);
						break
	
					case 0:
	
						$('#cn-form-ajax-response').html('<span id="cn-form-message"><p>' + cn_form.strAJAXErrMsg + '</p></span>').fadeIn(1500);
						break
	
					case 1:
	
						// Show the success message. Response code == success, unmoderated.
						$('#cn-form-ajax-response').html('<div id="cn-form-message"></div>');
						$('#cn-form-message').html('<h3>' + cn_form.strSubmitted + '</h3>').hide().append('<p>' + cn_form.strSubmittedMsg + '</p>').fadeIn(1500);
						break
	
					case 2:
	
						// Show the success message. Response code == success, moderated.
						$('#cn-form-ajax-response').html('<div id="cn-form-message"></div>');
						$('#cn-form-message').html('<h3>' + cn_form.strSubmitted + '</h3>').hide().append('<p>' + cn_form.strSubmittedMsg + '</p>').fadeIn(1500);
						break
	
					case 3:
	
						$('#cn-form-ajax-response').html('<span id="cn-form-message"><p>' + cn_form.strUpdatedMsg + '</p></span>');
	
						location.reload(true);
						break
	
				};
	
			},
			ajaxError : function ( XMLHttpRequest, status, error ) {
	
				alert( cn_form.strAJAXSubmitErrMsg );
			},
			scrollTop : function () {
	
				/**
				 * @link http://stackoverflow.com/a/21583714
				 */
	
				// if missing doctype (quirks mode) then will always use 'body'
				if ( document.compatMode !== 'CSS1Compat' ) return 'body';
	
				// if there's a doctype (and your page should)
				// most browsers will support the scrollTop property on EITHER html OR body
				// we'll have to do a quick test to detect which one...
	
				var html = document.documentElement;
				var body = document.body;
	
				// get our starting position.
				// pageYOffset works for all browsers except IE8 and below
				var startingY = window.pageYOffset || body.scrollTop || html.scrollTop;
	
				// scroll the window down by 1px (scrollTo works in all browsers)
				var newY = startingY + 1;
				window.scrollTo(0, newY);
	
				// And check which property changed
				// FF and IE use only html. Safari uses only body.
				// Chrome has values for both, but says
				// body.scrollTop is deprecated when in Strict mode.,
				// so let's check for html first.
				var element = ( html.scrollTop === newY ) ? 'html' : 'body';
	
				// now reset back to the starting position
				window.scrollTo(0, startingY);
	
				return element;
			}
		};
	
		CN_Form.init();
	
	
		/*
		 * Init Chosen to enhance select drop downs.
		 */
		$('.cn-enhanced-select').chosen();
	
		/*
		 * Validate and submit the form.
		 */
		$('form#cn-form').validate({
			submitHandler: function(form) {
	
				// Serialize the form data
				// var formData = $(form).serialize();
	
				var top = CN_Form.scrollTop();
	
				// Scroll to form head
				$(top).animate( { scrollTop: $('body').offset().top }, 'slow', function() {
	
					// Set the processing message
					$('#cn-form-ajax-response').html('<h3>' + cn_form.strSubmitting + '</h3>').hide().fadeIn(500);
	
					// Hide the form
					$( form ).fadeTo( 'slow', 0 ).slideUp( 1500, function() {
	
						// Post the form data
						$( form ).ajaxSubmit({
							async: false,
							type:       'POST',
							iframe:     true,
							enctype:    'multipart/form-data',
							url:        cn_form.ajaxurl,
							dataType:   'json',
							cache:      false,
							beforeSend: CN_Form.ajaxBeforeSend,
							success:    CN_Form.ajaxSuccess,
							error:      CN_Form.ajaxError
						});
	
					});
	
				});
			},
			// errorContainer: '#cn-form-ajax-response',
			// errorLabelContainer: '#cn-form-ajax-response ul',
			// wrapper: 'li',
			// focusCleanup: true,
			// Override generation of error label
			errorPlacement: function( error, element ) {},
			debug: cn_form.demo,
			ignore: '.ed_button, ul.chosen-choices li.search-field input, textarea[id$="template"]'
		});

	});
} )( jQuery );