<?php
if (! function_exists ( 'get_wp_user_avatar' )) {
	function get_wp_user_avatar() {
		return get_avatar ( get_the_author_meta ( 'ID' ) );
	}
}
?>
<div class="hana_widget widget-beauty-posts <?php echo $class_with_shortcode; ?>">
<?php 
	if( $title ) { 
		if ( $title_link )
			echo '<a href="' . $title_link . '">';
	
		echo '<h3 class="hana-widget-header">';
		echo $title;
		echo '</h3>';

		if ( $title_link )
			echo '</a>';		
	} 
?>