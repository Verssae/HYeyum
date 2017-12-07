jQuery(document).ready(function(){
	jQuery("iframe").css("max-width","100%");
	var $iframe_content = jQuery("iframe").contents();
	$iframe_content.find("embed").css("max-width","100%");
	$iframe_content.find("object").css("max-width","100%");
});