/**
 * @author Steven A. Zahm
 */
jQuery(document).ready(function($){
	
	//http://www.designchemical.com/blog/index.php/jquery/add-default-text-to-form-fields-using-jquery/
	
	$('#cn-category').chosen();
	
	$('input#entry_type_0')
		.click(function(){
			/*
			 * Remove the 'required' class used by jQuery Validatation plugin to identify required input fields.
			 * Entry type, 'individual' does not require the 'organization' field to be entered.
			 */
			$('input[name=first_name], input[name=last_name]').addClass('required');
			$('input[name=organization]').removeClass('required error').addClass('invalid');
			
			
			$('#cn-name').slideDown();
			$('#cn-contact-name').slideUp();
			$('.celebrate').slideDown();
			$('.celebrate-disabled').slideUp();
		});
	
	
	$('input#entry_type_1')
		.click(function(){
			/*
			 * Add the 'required' class used by jQuery Validatation plugin to identify required input fields.
			 * Entry type, 'organization' requires the 'organization' field to be entered.
			 */
			$('input[name=organization]').addClass('required');
			$('input[name=first_name], input[name=last_name]').removeClass('required error').addClass('invalid');
			
			
			$('#cn-name').slideUp();
			$('#cn-contact-name').slideDown();
			$('.celebrate').slideUp();
			$('.celebrate-disabled').slideDown();
		});
	
	
	$(function() {
		var $entryType;
		
		if ( $('input[name=entry_type]').length == 1 ) {
			$entryType = ($('input[name=entry_type]').val());
		} else if ( $('input[name^=entry_type]').length > 1  ){
			$entryType = ( $('input[name^=entry_type]:checked').val() );
		}
		
		switch ($entryType)
		{
			case 'individual':
				$('input[name=first_name], input[name=last_name]').addClass('required');
				$('input[name=organization]').removeClass('required');
				$('#cn-contact-name').slideUp();
				$('.celebrate-disabled').slideUp();
				break;
			
			case 'organization':
				$('input[name=organization]').addClass('required');
				$('input[name=first_name], input[name=last_name]').removeClass('required');
				$('#cn-name').slideUp();
				$('.celebrate').slideUp();
				$('.celebrate-disabled').slideDown();
				break;
		}
	
	});
	
	$('a.cn-add.cn-button').click(function() {
		var $this = $(this);
		var type = $this.attr('data-type');
		var container = '#' + $this.attr('data-container');
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
		
		return false;
	});
	
	$('a.cn-remove.cn-button').live('click', function() {
		var $this = $(this);
		var token = $this.attr('data-token');
		var type = $this.attr('data-type');
		var id = '#' + type + '-row-' + token;
		//alert(id);
		$(id).slideUp().remove('fast');
		return false;
	});
	
	/*
	 * Bind the click event of the submit button to to have tingMCE update the textarea contents
	 * so the content is properly saved.
	 */
	//$('#mySubmitButton').click(function() {
	$('input[type="submit"]#cn-form-submit-new').click(function() {
		//$('input[type="submit"]#cn-form-submit-new').tinyMCE.triggerSave();
		tinyMCE.triggerSave();
	});
	
	/*
	 * Empty the jQuery Validation plugin error message strings
	 */
    //jQuery.validator.messages.required = "";
	
	/*
	 * Validate and submit the form
	 */
	$('form#cn-form').validate({
		submitHandler: function(form){
			// This should make sure the tinyMCE content is submitted
			//tinyMCE.get('cn-form-bio').save();
			//tinyMCE.get('cn-form-notes').save();
			
			// Serialize the form data
			//var formData = $('#cn-form').serialize();
			
			// Post the form data
			$(form).prop('method', 'POST').ajaxSubmit({
				type: 'POST',
				iframe: true,
				enctype: "multipart/form-data",
				url: cn_form.ajaxurl,
				cache: false,
				success: function(response){
					// Clear the form after user submission
					//$(form).clearForm();
					//tinyMCE.get('cn-form-bio').setContent('');
					//tinyMCE.get('cn-form-notes').setContent('');
					
					// Show the success message
					$('#cn-form-ajax-response').html('<div id="cn-form-message"></div>');
					$('#cn-form-message').html('<h3>' + cn_form.strSubmitted + '</h3>').hide().append('<p>' + cn_form.strSubmittedMsg + '</p>').fadeIn(1500);
				},
				beforeSend: function() {
					// Scroll to form head
					$('html,body').animate({
						scrollTop: $('html,body').offset().top
					}, 'slow', function() {
							// Set the processing message
							$('#cn-form-ajax-response').html('<h3>' + cn_form.strSubmitting + '</h3>').hide().fadeIn(500);
							
							// Hide the form
							$('#cn-form').fadeTo('slow', 0).slideUp(1500);
						}
					);
				},
			});
			
			// Override the defualt form submit action
			return false;
		},
		/*errorContainer: '#cn-form-ajax-response',
		errorLabelContainer: '#cn-form-ajax-response ul',
		wrapper: 'li',*/
		/*focusCleanup: true,*/
		// Override generation of error label
		errorPlacement: function(error, element) {},
		debug: cn_form.debug
		//ignore: '.invalid'
	});
});