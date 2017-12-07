jQuery(document).ready(function($) {
	jQuery('#la-saved').hide();
    jQuery('#la-loader').hide();
	jQuery('#fronteditor').on('click',' .saveoptions',function(event) {
	event.preventDefault();
	jQuery('#la-saved').hide();
    jQuery('#la-loader').show();
	 var roles = [];
	  $('input[name="roles"]:checked').each(function() {
		  roles.push(this.value);
		}); 
		
	var data = {
			action: 'la_save_front_editor',
			position:jQuery('#fronteditor').find('.btnposition').val(),
			btnText:jQuery('#fronteditor').find('.btntext').val(),
			disBtntext:jQuery('#fronteditor').find('.disbtntext').val(),
			role: roles
			
		}
		// console.log(data); 
		jQuery.post(laAjax.url, data, function(resp) {
			jQuery('#la-saved').show();
            jQuery('#la-loader').hide();
            jQuery('#la-saved').delay(2000).fadeOut();
            window.location.reload(true);
		});
	});

	if ( $("input[name=roles]:not('input[name=search]:eq(0)')").is(":checked")) {   
    $("input[name=roles]:eq(0)").prop('disabled',true);
}
});