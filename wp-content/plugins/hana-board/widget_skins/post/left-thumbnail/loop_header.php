<div class="hana_widget widget-left-thumbnail <?php echo $class_with_shortcode; ?>">
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