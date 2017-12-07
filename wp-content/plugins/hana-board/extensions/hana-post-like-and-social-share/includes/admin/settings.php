<?php
/**
 * WordPress settings API class
 * 
 * @author HanaWordpress
 */
if (! defined('HANAWORDPRESS_MENU'))
	define('HANAWORDPRESS_MENU', 'hanawordpress-main');


class HanaLike_Settings {
	private $settings_api;
	private $options_page;
	private $page_title;
	private $page;
	private $version;
	private $settings_key;
	private $available_sns;
	private $plugin_desc;
	function __construct() {
		$this->page = "hana-post-like";
		$this->page_title = __( 'Hana Post Like and Social Share', HANA_LIKE_TEXT_DOMAIN );
		$this->settings_key = "hana_like";
		$this->version = HANA_LIKE_VERSION;
		$this->available_sns = array( 
			'facebook' => __('Facebook', HANA_LIKE_TEXT_DOMAIN),
			'twitter' => __('Twitter', HANA_LIKE_TEXT_DOMAIN),
			'google' => __('Google+', HANA_LIKE_TEXT_DOMAIN),
			'kakao_story' => __('Kakao Story', HANA_LIKE_TEXT_DOMAIN),
			'kakao_talk' => __('Kakao Talk', HANA_LIKE_TEXT_DOMAIN),
			'naver_line' => __('Line', HANA_LIKE_TEXT_DOMAIN),
			'naver_band' => __('Naver Band', HANA_LIKE_TEXT_DOMAIN),
			'naver_blog' => __('Naver Blog', HANA_LIKE_TEXT_DOMAIN)
		);
		$this->plugin_desc = __('Adds \'Like\' and \'Social shares\' features to posts, pages, hanaboard and custom post types.', HANA_LIKE_TEXT_DOMAIN);
		
		add_action( 'wp_loaded', array (
				&$this,
				'admin_init' 
		) );
		add_action( 'admin_menu', array (
				&$this,
				'admin_menu' 
		) );
	}
	function admin_init() {
		// initialize settings
		if (isset( $_POST ['hana_like_options_submit'] )) {
			if (! wp_verify_nonce( $_POST ['hana_like-settings-nonce'], 'hana_like-settings-form' )) {
				wp_die( __( 'Cheating?', HANA_BOARD_TEXT_DOMAIN ) );
			}
			add_action( 'wp_loaded', array (
					$this,
					'settings_submit' 
			) );
			$this->settings_submit();
		}
	}
	
	/**
	 * Register the admin menu
	 * 
	 * @since 0.1
	 */
	function admin_menu() {
		if (! empty( $GLOBALS ['admin_page_hooks'] [HANAWORDPRESS_MENU] )) {
            add_submenu_page( HANAWORDPRESS_MENU, __( 'Post Like & Social Share', HANA_LIKE_TEXT_DOMAIN ), __( 'Post Like & Social Share', HANA_LIKE_TEXT_DOMAIN ), 'manage_options', $this->page, array (
                &$this,
                'settings_page'
            ) );
        } else {
            add_submenu_page('tools.php', __('Post Like & Social Share', HANA_LIKE_TEXT_DOMAIN), __('Post Like & Social Share', HANA_LIKE_TEXT_DOMAIN), 'manage_options', $this->page, array(
                &$this,
                'settings_page'
            ));
        }
    }
	function admin_enqueue_scripts() {
		global $wp_scripts;
		wp_enqueue_style( 'hanaboard-admin', hanaboard_plugins_url( 'includes/admin/css/hanaboard_admin.css' ) );
		wp_enqueue_style( 'hanaboard_bootstrap_grid', hanaboard_plugins_url( 'css/bootstrap-grid12.css' ) );
		wp_register_style( 'jquery-ui-bootstrap', hanaboard_plugins_url( 'css/jquery-ui-1.10.3.custom.css' ) );
		wp_register_style( 'hana-like-admin', plugins_url( 'css/admin.css', __FILE__ ) );
		wp_enqueue_style( 'hana-like-admin' );
		wp_enqueue_style( 'jquery-ui-bootstrap' );
	}
	function settings_submit() {
		$settings = array (
				'post_types_like' => $_POST ['post_types_like'],
				'post_types_social_share' => $_POST ['post_types_social_share'],
				'skin_like' => $_POST ['skin_like'],
				'skin_social_share' => $_POST ['skin_social_share'],
				'like_items' => $_POST ['like_items'], 
				'sns_enabled' => $_POST ['sns_enabled'], 
				'api_key_kakao' => $_POST ['api_key_kakao'],
				'like_text' => $_POST['like_text'],
				'liked_text' => $_POST['liked_text'],
				'dislike_text' => $_POST['dislike_text'],
				'disliked_text' => $_POST['disliked_text'],
				'title_social_share' => $_POST['title_social_share'],
				'icon_style_social_share' => $_POST['icon_style_social_share']
		);
		$this->update_plugin_settings( $settings );
	}
	function update_plugin_settings($values = array()) {
		$settings = $values;
		update_option( $this->settings_key, $settings );
	}
	function get_plugin_settings($key = '', $default = '') {
		$settings = get_option( $this->settings_key );
		if (FALSE === $settings) {
		} elseif (! $key) {
			return $settings;
		} elseif (array_key_exists( $key, $settings )) {
			return $settings [$key];
		} else {
			return $default;
		}
		
		return false;
	}
	function get_available_post_types() {
		$default_post_types = array (
				'page',
				'post' 
		);
		$post_types = get_post_types( array (
				'has_archive' => true 
		) );
		$post_types = array_merge( $default_post_types, $post_types );
		return $post_types;
	}
	function settings_page() {
		global $options, $current;
		$title = $this->page_title;
		
		$messages = array ();
		
		if (isset( $_POST ) && isset( $_POST ['hana_like_options_submit'] )) {
			$messages [] = array (
					'type' => 'message',
					'message' => __( "Hana Post Like & Social Share settings saved.", HANA_LIKE_TEXT_DOMAIN ) 
			);
		}
		$hanaboard_post_name = __( 'Hana Board Post', HANA_LIKE_TEXT_DOMAIN );
		$hanaboard_post_name .= __('(You can enable or disable Post Like feature on Hana Board Settings.)', HANA_LIKE_TEXT_DOMAIN);
		$available_sns = $this->available_sns;
		$settings = $this->get_plugin_settings();
		$post_types = $this->get_available_post_types();
		$like_skins = array(
				'default' => 'Default'
		);
		$social_share_skins = array(
				'default' => 'Default'
		);
		$icon_style_social_share = array(
				'rectangle' => __('rectangle',HANA_LIKE_TEXT_DOMAIN),
				'rounded' => __('rounded',HANA_LIKE_TEXT_DOMAIN),
		);

		$current = explode( ',', $current ['post_types'] );
		
		include_once ("settings-page.php");
	}
}
$hana_like_settings = new HanaLike_Settings();

