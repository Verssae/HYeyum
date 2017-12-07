<?php
/*
 * Plugin Name: Hana Board
 * Plugin URI: http://hanawordpress.com/hana-board
 * Description: 하나보드 워드프레스 게시판은 뛰어난 확장성과 안정성을 가진 한국형 워드프레스 게시판 플러그인입니다. <a href="http://hanawordpress.com">하나워드프레스</a>에서 다양한 게시판 / 위젯 스킨과 확장 기능을 무료로 받으실 수 있습니다.
 * Text Domain: hanaboard
 * Author: 하나워드프레스
 * Version: 1.5.0
 * Author URI: http://hanawordpress.com
 */
/**
 * *************************
 * constants
 * **************************
 */
if (! defined ( 'HANA_BOARD_VERSION' ))
	define ( 'HANA_BOARD_VERSION', '1.5.0' );
if (! defined ( 'HANAWORDPRESS_HOME' ))
	define ( 'HANAWORDPRESS_HOME', 'http://hanawordpress.com/' );
if (! defined ( 'HANA_BOARD_TEXT_DOMAIN' ))
	define ( 'HANA_BOARD_TEXT_DOMAIN', 'hanaboard' );
if (! defined ( 'HANA_BOARD_TAXONOMY' ))
	define ( 'HANA_BOARD_TAXONOMY', 'hanaboard' );
if (! defined ( 'HANA_BOARD_POST_TYPE' ))
	define ( 'HANA_BOARD_POST_TYPE', 'hanaboard-post' );
if (! defined ( 'HANA_BOARD_POST_TYPE_FORM' ))
	define ( 'HANA_BOARD_POST_TYPE_FORM', 'hanaboard-post-form' );
if (! defined ( 'HANA_BOARD_QUERY_VAR_MODE' ))
	define ( 'HANA_BOARD_QUERY_VAR_MODE', 'mode' );
if (! defined ( 'HANA_BOARD_TAX_META_HEADER' ))
	define ( 'HANA_BOARD_TAX_META_HEADER', 'hanaboard_tax_meta_' );
if (! defined ( 'HANA_BOARD_POST_META_HEADER' ))
	define ( 'HANA_BOARD_POST_META_HEADER', 'hanaboard_' );
if (! defined ( 'HANA_BOARD_CONTENT_IMAGE_SIZE' ))
	define ( 'HANA_BOARD_CONTENT_IMAGE_SIZE', 'hanaboard_content' );

if (! defined ( 'HANA_BOARD_PLUGIN_URL' ))
	define ( 'HANA_BOARD_PLUGIN_URL', plugin_dir_url ( __FILE__ ) );

if (! defined ( 'HANA_BOARD_PLUGIN_DIR' ))
	define ( 'HANA_BOARD_PLUGIN_DIR', plugin_dir_path ( __FILE__ ) );

add_theme_support ( 'post-thumbnails' );
add_image_size ( HANA_BOARD_CONTENT_IMAGE_SIZE, 1000, 50000, false );
add_image_size ( 'hana_wide_thumb', 320, 200, true );
add_image_size ( 'hana_micro_thumb', 120, 75, true );
function hanaboard_plugins_url($path = '') {
	return HANA_BOARD_PLUGIN_URL . $path;
}
function hanaboard_plugins_dir() {
	return HANA_BOARD_PLUGIN_DIR;
}

if (! class_exists ( 'HanaBoard' )) {
	
	/**
	 * Main HanaBoard Class @class HanaBoard
	 *
	 * @version 0.1
	 */
	final class HanaBoard {
		public $version = HANA_BOARD_VERSION;
		
		/**
		 *
		 * @var HanaBoard The single instance of the class
		 * @since 0.1
		 */
		protected static $_instance = null;
		public static function instance() {
			if (is_null ( self::$_instance )) {
				self::$_instance = new self ();
			}
			return self::$_instance;
		}
		public function __construct() {
			$this->includes ();
			$this->init_hooks ();
			
			do_action ( 'hanaboard_loaded' );
		}
		private function init_hooks() {
			register_activation_hook ( __FILE__, array (
					&$this,
					'install' 
			) );
			add_action ( 'after_setup_theme', array (
					$this,
					'setup_environment' 
			) );
			
			add_action ( 'admin_init', array (
					&$this,
					'block_admin_access' 
			) );
			add_action ( 'plugins_loaded', array (
					&$this,
					'load_plugin_textdomain' 
			) );
			add_action ( 'wp_enqueue_scripts', array (
					&$this,
					'enqueue_scripts' 
			) );
		}
		
		/**
		 * Create tables on plugin activation
		 *
		 * @global object $wpdb
		 */
		function install() {
			//todo: 하나위젯과 like 플러그인 설치시 비활성화하도록 할 것
			$this->add_roles_on_plugin_activation ();
		}
		function uninstall() {
		}
		public function setup_environment() {
			$this->add_thumbnail_support ();
			$this->add_image_sizes ();
			// $this->fix_server_vars();
		}
		
		/**
		 * Ensure post thumbnail support is turned on
		 */
		private function add_thumbnail_support() {
			if (! current_theme_supports ( 'post-thumbnails' )) {
				add_theme_support ( 'post-thumbnails' );
			}
			add_post_type_support ( 'hanaboard-post', 'thumbnail' );
		}
		
		/**
		 * Add Image sizes to WP
		 *
		 * @since 0.1
		 */
		private function add_image_sizes() {
		}
		
		/**
		 * What type of request is this? string $type ajax, frontend or admin
		 *
		 * @return bool
		 */
		private function is_request($type) {
			switch ($type) {
				case 'admin' :
					return is_admin ();
				case 'ajax' :
					return defined ( 'DOING_AJAX' );
				case 'cron' :
					return defined ( 'DOING_CRON' );
				case 'frontend' :
					return (! is_admin () || defined ( 'DOING_AJAX' )) && ! defined ( 'DOING_CRON' );
			}
		}
		
		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			if ($this->is_request ( 'admin' )) {
				include_once ('includes/admin/settings.php');
				include_once ('includes/admin/custom-fields.php');
				include_once ('includes/admin/settings-options.php');
				include_once ('includes/admin/functions.php');
				include_once ('includes/admin/dashboard-functions.php');
			}
			
			if ($this->is_request ( 'ajax' )) {
				$this->ajax_includes ();
			}
			
			if ($this->is_request ( 'frontend' )) {
				$this->frontend_includes ();
			}
			
			if ($this->is_request ( 'cron' ) && 'yes' === get_option ( 'hanaboard_allow_tracking', 'no' )) {
			}
			require_once 'includes/hanaboard-controller.php';
			require_once 'includes/functions-post.php';
			require_once 'includes/functions-board.php';
			require_once 'includes/functions-skin.php';
			require_once 'includes/rewrite.php';
			require_once 'includes/filters.php';
			require_once 'includes/template-functions.php';
			require_once 'includes/attachment.php';
			require_once 'includes/hana-comments.php';
			require_once 'extensions/upload_filename_fix/upload_filename_fix.php';
			require_once 'extensions/secure_image/secure_image.php';

			if (! class_exists('Hana_Post_Widget')) {
				require_once 'extensions/hana-widget/hana-widget.php';
			}
			if (! class_exists('hana_most_liked_widget')) {
				require_once 'extensions/hana-post-like-and-social-share/hana-like.php';
			}
		}
		
		/**
		 * Include required ajax files.
		 */
		public function ajax_includes() {
		}
		
		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {
		}
		
		/**
		 * Enqueues Styles and Scripts when the shortcodes are used only
		 *
		 * @uses has_shortcode()
		 * @since 0.1
		 */
		function enqueue_scripts() {
			if (is_hanaboard_page ()) {
				
				$path = plugins_url ( '', __FILE__ );
				
				// for multisite upload limit filter
				if (is_multisite ()) {
					require_once ABSPATH . '/wp-admin/includes/ms.php';
				}
				
				require_once ABSPATH . '/wp-admin/includes/template.php';
				
				wp_enqueue_script ( 'jquery' );
				wp_enqueue_script ( 'jquery-ui-core' );
				wp_enqueue_script ( 'jquery-ui-dialog' );
				// wp_enqueue_style( "wp-jquery-ui-dialog" );
				wp_enqueue_script ( 'post' );
				
				wp_enqueue_script ( 'hanaboard', $path . '/js/hana_board.js', array (), HANA_BOARD_VERSION, true );
				wp_enqueue_script ( 'jquery-validate', '/js/jquery.validate.min.js', array (
						'jquery' 
				), true );
				
				wp_register_style ( 'jquery-ui-bootstrap', hanaboard_plugins_url ( 'css/jquery-ui-1.10.3.custom.css' ) );
				wp_enqueue_style ( 'jquery-ui-bootstrap' );
				wp_enqueue_style ( 'fontawesome-45', 'https://opensource.keycdn.com/fontawesome/4.5.0/font-awesome.min.css' );

				add_action ( 'wp_head', array(&$this, 'hanaboard_wp_head_script'));
				
				wp_localize_script ( 'hanaboard', 'hanaboard', array (
						'ajaxurl' => admin_url ( 'admin-ajax.php' ),
						'page_now' => hanaboard_page_now (),
						'messages' => array (
								'success' => __ ( 'Success', HANA_BOARD_TEXT_DOMAIN ),
								'error' => __ ( 'Error', HANA_BOARD_TEXT_DOMAIN ),
								'cancel' => __ ( 'Cancel', HANA_BOARD_TEXT_DOMAIN ),
								'empty_title' => __ ( 'Empty title.', HANA_BOARD_TEXT_DOMAIN ),
								'empty_content' => __ ( 'Content is too short.', HANA_BOARD_TEXT_DOMAIN ),
								'new-post-guest-author' => __ ( 'Empty name.', HANA_BOARD_TEXT_DOMAIN ),
								'hanaboard-guest-password-input' => __ ( 'Empty password.', HANA_BOARD_TEXT_DOMAIN ),
								'empty_guest_password' => __ ( 'Empty post password.', HANA_BOARD_TEXT_DOMAIN ),
								'wrong_guest_password' => __ ( 'Wrong post password.', HANA_BOARD_TEXT_DOMAIN ),
								'confirm' => __ ( 'Are you sure?', HANA_BOARD_TEXT_DOMAIN ),
								'submit' => __ ( 'Submit', HANA_BOARD_TEXT_DOMAIN ),
								'delete' => __ ( 'Delete', HANA_BOARD_TEXT_DOMAIN ),
								'confirmDelete' => __ ( 'Are you sure to delete this post?', HANA_BOARD_TEXT_DOMAIN ),
								'loading' => __ ( 'Loading...', HANA_BOARD_TEXT_DOMAIN ),
								'upload_image' => __ ( 'Upload Images', HANA_BOARD_TEXT_DOMAIN ),
								'insert_video' => __ ( 'Insert Video', HANA_BOARD_TEXT_DOMAIN ),
								'list_admin_action_error' => __ ( 'Error while moving articles. Ask to Administrator.', HANA_BOARD_TEXT_DOMAIN ),
								'list_admin_action_success' => __ ( 'Articles moved successfully.', HANA_BOARD_TEXT_DOMAIN ),
								'insert_to_content' => __ ( 'Insert to content', HANA_BOARD_TEXT_DOMAIN ) 
						)
						,
						'nonce' => wp_create_nonce ( 'hanaboard_nonce' ) 
				) );
			}
		}
		function hanaboard_wp_head_script() {
			?>
			<!--[if lt IE 9]>
			<style type="text/css" src="<?php echo HANA_BOARD_PLUGIN_URL;?>/css/jquery.ui.1.10.3.ie.css"></style>
			<script src="<?php echo HANA_BOARD_PLUGIN_URL;?>/js/PIE_IE9.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
			<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>

			<![endif]-->
			<?php
		}
		function add_roles_on_plugin_activation() {
			$roles = array (
					'read_' . HANA_BOARD_POST_TYPE 
			);
		}
		
		/**
		 * Block user access to admin panel for specific roles
		 *
		 * @global string $pagenow
		 */
		function block_admin_access() {
			global $pagenow;
			
			// bail out if we are from WP Cli
			if (defined ( 'WP_CLI' )) {
				return;
			}
			
			$access_level = 'administrator';
			$valid_pages = array (
					'admin-ajax.php',
					'async-upload.php',
					'media-upload.php',
					'edit.php' 
			);
		}
		
		/**
		 * *************************
		 * language files
		 * **************************
		 */
		function load_plugin_textdomain() {
			$locale = apply_filters ( 'hanaboard_locale', get_locale () );
			$mofile = dirname ( __FILE__ ) . "/languages/hanaboard-$locale.mo";
			if (file_exists ( $mofile )) {
				load_plugin_textdomain ( 'hanaboard', false, dirname ( plugin_basename ( __FILE__ ) ) . '/languages' );
			}
		}
		
		/**
		 * The main logging function
		 *
		 * @uses error_log
		 * @param string $type
		 *        	type of the error. e.g: debug, error, info
		 * @param string $msg        	
		 */
		function log($type = '', $msg = '') {
			if (WP_DEBUG == true) {
				$msg = sprintf ( "[%s][%s] %s\n", date ( 'd.m.Y h:i:s' ), $type, $msg );
				error_log ( $msg, 3, dirname ( __FILE__ ) . '/log.txt' );
			}
		}
	}
}
function HANA_BOARD() {
	return HanaBoard::instance ();
}
function is_hanaboard_page($page_type = null) {
	global $post;
	
	$mode = isset ( $_GET [HANA_BOARD_QUERY_VAR_MODE] ) ? $_GET [HANA_BOARD_QUERY_VAR_MODE] : null;
	$article = isset ( $_GET ['article'] ) ? $_GET ['article'] : null;
	switch ($page_type) {
		case 'form' :
			return ($mode == 'edit' || $mode == 'write' || $mode == 'write_reply');
		case 'view' :
		case 'article' :
			return ($article && ! $mode);
			break;
		case 'edit' :
			return ($mode === 'edit');
			break;
		case 'write' :
		case 'publish' :
			return ($mode === 'write' && ! $article);
			break;
		case 'write_reply' :
			return ($mode === 'write_reply');
			break;
		case 'list' :
		case 'archive' :
			return (! $article && ! $mode);
			break;
		case null :
			return (is_a ( $post, 'WP_Post' ) && has_shortcode ( $post->post_content, 'hanaboard' ));
			break;
	}
}
function hanaboard_page_now() {
	$mode = isset ( $_GET [HANA_BOARD_QUERY_VAR_MODE] ) ? $_GET [HANA_BOARD_QUERY_VAR_MODE] : null;
	$article = isset ( $_GET ['article'] ) ? $_GET ['article'] : null;
	if ($mode == 'edit')
		return 'edit';
	elseif ($mode == 'write')
		return 'write';
	elseif ($mode == 'write')
		return 'write';
	elseif ($mode == 'write_reply')
		return 'write_reply';
	elseif ($article && ! $mode)
		return 'view';
	else
		return 'list';
}

$GLOBALS ['hanaboard'] = HANA_BOARD ();
