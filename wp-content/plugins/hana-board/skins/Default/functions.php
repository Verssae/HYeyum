<?php
$enqueue_styles = array(
		'hanaboard_bootstrap_grid' => hanaboard_plugins_url( 'css/bootstrap-grid12.css' ),
		'hana_comments' => hanaboard_plugins_url( 'css/comments.css' ),
		'hanaboard_skin_style' => plugin_dir_url( __FILE__ ) . 'css/common.css',
		'hanaboard_skin_style_list' => plugin_dir_url( __FILE__ ) . '/css/list.css',
		'lightbox2' => hanaboard_plugins_url( 'extensions/lightbox2/css/lightbox.css' ),
);

if(hanaboard_get_option('css_head')) {
	foreach ($enqueue_styles as $key => $filename ){
		?>
		<link rel="stylesheet" id="<?php echo $key; ?>-css" href="<?php echo $filename; ?>?v=<?php echo HANA_BOARD_VERSION; ?>&ver=4.6.6" type="text/css" media="all">
		<?php
	}
} else {
    foreach ($enqueue_styles as $key => $filename ){
        wp_enqueue_style($key, $filename . '?v=' . HANA_BOARD_VERSION );
    }
}

wp_enqueue_script( 'hanaboard_skin_script', plugin_dir_url( __FILE__ ) . '/js/common.js', array (
		'jquery' 
) );
wp_enqueue_script( 'lightbox2', hanaboard_plugins_url( 'extensions/lightbox2/js/lightbox.js' ), array (
		'jquery' 
), true );

?>