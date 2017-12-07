<?php
wp_enqueue_style( 'hanaboard_bootstrap_grid', HANA_BOARD_PLUGIN_URL . '/css/bootstrap-grid12.css' );
wp_enqueue_style( 'hana_comments', HANA_BOARD_PLUGIN_URL . '/css/comments.css' );
wp_enqueue_style( 'hanaboard_skin_style', plugin_dir_url( __FILE__ ) . 'css/common.css' );
wp_enqueue_style( 'hanaboard_skin_style_list', plugin_dir_url( __FILE__ ) . '/css/list.css' );
wp_enqueue_script( 'hanaboard_skin_script', plugin_dir_url( __FILE__ ) . '/js/common.js', array (
		'jquery' 
) );

wp_enqueue_style( 'fuelux-3.6.3-style', 'http://www.fuelcdn.com/fuelux/3.6.3/css/fuelux.min.css' );
wp_enqueue_script( 'fuelux-3.6.3', 'http://www.fuelcdn.com/fuelux/3.6.3/js/fuelux.min.js', array (
		'jquery,bootstrap-script' 
), true );

wp_enqueue_style( 'lightbox2', HANA_BOARD_PLUGIN_URL . '/extensions/lightbox2/css/lightbox.css' );
wp_enqueue_script( 'lightbox2', HANA_BOARD_PLUGIN_URL . '/extensions/lightbox2/js/lightbox.js', array (
		'jquery' 
), true );
?>