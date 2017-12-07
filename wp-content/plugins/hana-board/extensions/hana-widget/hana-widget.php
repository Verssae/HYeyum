<?php
/*
 * Plugin Name: Hana Widget
 * Plugin URI: http://hanawordpress.com/hana-widget
 * Description: Display any post type posts, and comments with display option. Outputs the post thumbnail, title and date per listing
 * Author: HanaWordpress
 * Version: 0.2
 * Author URI: http://hanawordpress.com
 */

/**
 * *************************
 * constants
 * **************************
 */
if (! defined( 'HANA_WIDGET_TEXT_DOMAIN' )) {
	define( 'HANA_WIDGET_TEXT_DOMAIN', 'hana-widget' );
}
if (! defined( 'HANA_WIDGET_VERSION' )) {
	define( 'HANA_WIDGET_VERSION', '0.2' );
}
if (! defined( 'HANA_WIDGET_BASE_DIR' )) {
    if (defined( 'HANA_BOARD_VERSION')) {
        define('HANA_WIDGET_BASE_DIR', hanaboard_plugins_dir() . 'extensions/hana-widget/');
        define('HANA_WIDGET_SKIN_DIR', hanaboard_plugins_dir() . 'widget_skins/');
    }
    else {
        define('HANA_WIDGET_BASE_DIR', dirname(__FILE__) . 'widget_skins/');
        define('HANA_WIDGET_SKIN_DIR', dirname(__FILE__) . 'widget_skins/');
    }
}
if (! defined( 'HANA_WIDGET_BASE_URL' )) {
    if (defined('HANA_BOARD_VERSION')) {
        define('HANA_WIDGET_BASE_URL', hanaboard_plugins_url() . '/widget_skins');
        define('HANA_WIDGET_SKIN_URL', hanaboard_plugins_url() . '/widget_skins');
    } else {
        define('HANA_WIDGET_BASE_URL', plugin_dir_url(__FILE__));
        define('HANA_WIDGET_SKIN_URL', plugin_dir_url(__FILE__) . '/layouts');
    }
}
add_action( 'wp_enqueue_scripts', 'hana_widget_enqueue' );
if (! function_exists( 'hana_widget_enqueue' )) {
	function hana_widget_enqueue() {
		wp_enqueue_style( 'fontawesome-45', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );
		wp_enqueue_style( 'bootdtrap-grid12', plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap-grid12.css', array (), HANA_WIDGET_VERSION );
		$types = hana_widget_types();
		foreach ( $types as $type ) {
			foreach ( hana_widget_skin_list( $type ) as $skin ) {
				
				$filename = HANA_WIDGET_SKIN_URL . '/' . $type . '/' . $skin . '/css/widget.css';
				wp_enqueue_style( 'hana-widget-' . $type . 'skin-' . sanitize_key( $skin ), $filename . '?v=' . HANA_BOARD_VERSION, array (), HANA_WIDGET_VERSION );
			}
		}
	}
}
if (! function_exists( 'hana_widget_skin_list' )) {
	function hana_widget_skin_list($type = 'post') {
		if (! in_array( $type, hana_widget_types() ))
			return array ();
		$default_skins = array (
				'default' => 'default' 
		);
		$skins_dir = glob( HANA_WIDGET_SKIN_DIR . '/' . $type . '/*', GLOB_ONLYDIR | GLOB_ERR );
		$skins = array ();
		foreach ( $skins_dir as $dir ) {
		    $current_dir = explode( '/', $dir );
			$skin_name = end( $current_dir );
			$skins [$skin_name] = $skin_name;
		}
		if (sizeof( $skins ) > 0)
			return $skins;
		else
			return $default_skins;
	}
}
if (! function_exists( 'hana_widget_types' )) {
	function hana_widget_types() {
		return array (
				'post',
				'comment' 
		);
	}
}

/**
 * *************************
 * includes
 * **************************
 */
if (is_admin()) {
	include (HANA_WIDGET_BASE_DIR . '/includes/admin/settings.php');
}
if (! class_exists( 'Hana_Post_Widget' )) {
	include (HANA_WIDGET_BASE_DIR . '/includes/hana-post-widget.php');
}

if (! class_exists( 'Hana_Comment_Widget' )) {
	include (HANA_WIDGET_BASE_DIR . '/includes/hana-comment-widget.php');
}

/**
 * *************************
 * language files
 * **************************
 */
if (! function_exists('hana_widget_load_text_domain')) {
    function hana_widget_load_text_domain() {
        load_plugin_textdomain(HANA_WIDGET_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    add_action('init', 'hana_widget_load_text_domain');
}
