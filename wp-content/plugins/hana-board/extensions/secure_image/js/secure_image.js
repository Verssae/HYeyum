jQuery(document).ready(function($) {
	function hanaboard_secure_form_alert_html(type,typemsg,msg){
		var html = '<div class="alert alert-'+type+'"><strong>'+typemsg+'!</strong>'+msg+'</div>';
		return html;
	}		
	if (typeof jQuery('#hanaboard_secure_image_input') != 'undefined') {
		jQuery("#refresh_captcha").on('click', function() {
			jQuery('#siimage').attr('src', hanaboard_secure_image.refresh_captcha_link + Math.random());
			jQuery('#hanaboard_secure_image_input').val('').focus();
			jQuery('#hanaboard_captcha_message').html('');
		});

		jQuery('#hanaboard_secure_image_input').change(function() {
			var message = '';
			jQuery.ajax({
			type : 'post',
			url : ajaxurl,
			data : {
			action : hanaboard_secure_image.action,
			nonce : hanaboard_secure_image.nonce,
			ct_captcha : jQuery("#hanaboard_secure_image_input").val()
			},
			dataType : 'json'
			}).done(function(data) {
				if (data.error == 0) {
					message = hanaboard_secure_form_alert_html('success', hanaboard.messages.success, hanaboard_secure_image.messages.success_captcha);
					jQuery("#hanaboard_captcha_message").html(message);
					jQuery("#hanaboard_secure_image_input").removeClass('invalid');
					setTimeout("jQuery('#hanaboard_captcha_message .alert').fadeOut()", 5000);
				} else {
					jQuery('#siimage').prop('src', hanaboard_secure_image.path + 'securimage_show.php?sid=' + Math.random());
					jQuery('#hanaboard_secure_image_input').val('');
					message = hanaboard_secure_form_alert_html('danger', hanaboard.messages.error, hanaboard_secure_image.messages.wrong_captcha);
					jQuery("#hanaboard_captcha_message").html(message);
					jQuery("#hanaboard_secure_image_input").addClass('invalid');
					jQuery("#hanaboard_secure_image_input").val('').focus();
					
					
				}
			});

			return false;
		});
	}
});
