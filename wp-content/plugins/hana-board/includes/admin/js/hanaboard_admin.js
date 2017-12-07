jQuery(document).ready(function() {
if( typeof jQuery("#hanaboard-admin-tabs") != 'undefined' ) {
	jQuery("#hanaboard-admin-tabs").tabs();
}
if( typeof jQuery("#include_cats") != 'undefined' ) {
	if ( jQuery( "#include_cats" ).length ) {
		jQuery(".deleteBoardLink").click( function(event) {
			if ( ! confirm( hanaboard.messages.confirmDeleteBoard ) ) {
				event.preventDefault();
				return false;
			}
		});
	}
	jQuery("#include-cats-modal").dialog({
		'dialogClass'   : 'wp-dialog',
		'modal'         : true,
		'autoOpen'      : false,
		'closeOnEscape' : true,
		'buttons'       : {
			"Save": function() {
				var selected_cats = [];
				jQuery('input[name="tax_input[hanaboard][]"]').each(function () {
					if (this.checked) {
						selected_cats.push( jQuery(this).val() );

					}
				});
				jQuery('input[name="term_meta[include_cats]"]').val( selected_cats.join( ',' ) );

				jQuery(this).dialog('close');
			}
		}
	});

	jQuery("#include_cats").click( function(event) {
		event.preventDefault();
		var selected_cats = jQuery('input[name="term_meta[include_cats]"]').val().split( ',' );
		jQuery('input[name="tax_input[hanaboard][]"]').each(function () {
			if ( jQuery.inArray( jQuery(this).val(), selected_cats ) !== -1 ) {
				jQuery(this).attr('checked','checked');
			}
		});
		jQuery.each( selected_cats, function ( index, value ) {

		});
		jQuery("#include-cats-modal").dialog('open');
	});
}

}); // jQuery.Ready