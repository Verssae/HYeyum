<?php
/*
 * Plugin Name: Hana Post Like and Social Share
 * Plugin URI: http://hanawordpress.com/hana-like
 * Description: Adds 'Like' and 'Social shares' features to posts, pages, hanaboard and custom post types.
 * Text Domain: hana-like
 * Version: 1.0.0
 * Author: HanaWordpress
 * Author URI: http://webjang.in
 */
/**
 * *************************
 * constants
 * **************************
 */
if (! defined( 'HANA_LIKE_TEXT_DOMAIN' )) {
	define( 'HANA_LIKE_TEXT_DOMAIN', 'hana-like' );
}
if (! defined( 'HANA_LIKE_BASE_DIR' )) {
	define( 'HANA_LIKE_BASE_DIR', dirname( __FILE__ ) );
}
if (! defined( 'HANA_LIKE_BASE_URL' )) {
	define( 'HANA_LIKE_BASE_URL', plugin_dir_url( __FILE__ ) );
}
if (! defined( 'HANA_LIKE_VERSION' )) {
	define( 'HANA_LIKE_VERSION', '1.0.0' );
}
/**
 * *************************
 * language files
 * **************************
 */
if( ! function_exists('hana_like_load_text_domain')) {
    function hana_like_load_text_domain()
    {
        load_plugin_textdomain(HANA_LIKE_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    add_action('init', 'hana_like_load_text_domain');
}
/**
 * *************************
 * includes
 * **************************
 */
if (is_admin()) {
	include (HANA_LIKE_BASE_DIR . '/includes/admin/settings.php');

}
include (HANA_LIKE_BASE_DIR . '/includes/functions.php');
include (HANA_LIKE_BASE_DIR . '/includes/display-functions.php');
include (HANA_LIKE_BASE_DIR . '/includes/widgets.php');
include (HANA_LIKE_BASE_DIR . '/includes/like-functions.php');
include (HANA_LIKE_BASE_DIR . '/includes/enqueue.php');
