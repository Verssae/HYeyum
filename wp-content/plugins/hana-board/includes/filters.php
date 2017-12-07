<?php
if (! defined( 'ABSPATH' ))
	exit();

add_action('init', 'hanaboard_start_session', 1);
function hanaboard_start_session() {
	if (!session_id()) {
		session_start();
	}
	if (! isset($_SESSION['hanaboard_guest_authorized_posts']))
		$_SESSION['hanaboard_guest_authorized_posts'] = array();
}
if (! function_exists( 'hanaboard_rel_feed_override' )) {
	if (is_hanaboard_page( 'view' ))
		remove_action( 'wp_head', 'feed_links', 2 );
	function hanaboard_rel_feed_override() {
	}
}
if (! function_exists( 'hanaboard_rel_shortlink_override' )) {
	if (is_hanaboard_page( 'view' ))
		remove_action( 'wp_head', 'rel_canonical' );
	add_action( 'wp_head', 'hanaboard_rel_canonical_override' );
	// A copy of rel_canonical but to allow an override on an article
	function hanaboard_rel_canonical_override() {
		if (! is_hanaboard_page( 'view' ))
			return;
		
		$post_id = get_query_var( 'article' );
		$link = hanaboard_get_the_permalink( $post_id, false );
		echo "<link rel='canonical' href='" . $link . "' />\n";
	}
}
if (! function_exists( 'hanaboard_rel_shortlink_override' )) {
	if (is_hanaboard_page( 'view' ))
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
	add_action( 'wp_head', 'hanaboard_rel_shortlink_override' );
	// A copy of rel_shortlink but to allow an override on an article
	function hanaboard_rel_shortlink_override() {
		if (! is_hanaboard_page( 'view' ))
			return;
		
		$post_id = get_query_var( 'article' );
		$link = hanaboard_get_the_permalink( $post_id, false );
		echo "<link rel='shortlink' href='" . $link . "' />\n";
	}
}
if (! function_exists( 'hanaboard_search_filter' )) {
	add_filter( 'pre_get_posts', 'hanaboard_search_filter' );
	function hanaboard_search_filter($query) {
		if (is_hanaboard_page())
			return $query;
		$post_type = isset($_GET ['type']) ? $_GET['type'] : HANA_BOARD_POST_TYPE;
		if (! $post_type) {
			$post_type = array (
					'post',
					HANA_BOARD_POST_TYPE 
			);
		}
		if ($query->is_search) {
			$query->set( 'post_type', $post_type );
			$query->set( 'post_status', array (
					'publish' 
			) );
		}
		;
		return $query;
	}
}

if (! function_exists( 'hanaboard_content_auto_link' )) {
	add_filter( 'the_content', 'hanaboard_content_auto_link', 1, 1 );
	function hanaboard_content_auto_link($content) {
		return preg_replace( '/<a /', '<a target="_blank" ', make_clickable( $content ) );
	}
}

if (! function_exists( 'hanaboard_wp_title' )) {
	add_filter( 'wp_title', 'hanaboard_wp_title', 99, 2 );
	
	/**
	 * Add Article Title to <title> for SEO
	 * 
	 * @param string $title        	
	 * @param string $sep        	
	 * @return string
	 */
	function hanaboard_wp_title($title, $sep) {
		if (is_hanaboard_page( 'view' )) {
			$post_id = get_query_var( 'article' );
			$post = get_post( $post_id );
			if ($sep == '' | $sep == ' ')
				$sep = ' - ';
			$title = $post->post_title . $sep . get_the_title();
			return $title;
		} else {
			return $title;
		}
	}
}
add_action( 'wp_head', 'hanaboard_add_open_graph_tags' );
function hanaboard_add_open_graph_tags() {
	$enabled_post_type = array (
			'post',
			'page',
			HANA_BOARD_POST_TYPE 
	);
	if (is_hanaboard_page( 'view' )) {
		// global $post;
		$post_id = get_query_var( 'article' );
	} else {
		$post_id = get_the_ID();
	}
	$post = get_post( $post_id );
	$title = wp_title( ' - ', false ) . get_bloginfo( 'name' );
	$url = get_permalink( $post_id );
	if (get_the_post_thumbnail( $post->ID, 'thumbnail' )) {
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
		$image_url = wp_get_attachment_image_src( $thumbnail_id, 'large', true );
		$image = $image_url [0];
	} else {
		$image = ''; // Change this to the URL of the logo you want beside your links shown on Facebook
	}
	// $description = get_bloginfo('description');
	$post_content = $post->post_content;
	$description = trim( wp_strip_all_tags( nl2br( strip_shortcodes( $post_content ) ), true ) );
	$description = strip_tags( $description );
	$description = str_replace( "\"", "'", $description );
	?>
<meta property="og:title" content="<?php echo $title; ?>" />
<meta property="og:type" content="post" />
<meta property="og:image" content="<?php echo $image; ?>" />
<meta property="og:url" content="<?php echo $url; ?>" />
<meta property="og:description" content="<?php echo $description; ?>" />
<meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>" />
<?php
}

// get_option('comment_registration') filter backup and restore
if (! function_exists( 'hanaboard_wp_title' )) {
	add_filter( 'bloginfo', 'hanaboard_wp_title', 99, 2 );
	
	/**
	 * modify META tag description for SEO
	 * 
	 * @param unknown $info        	
	 * @param unknown $show        	
	 * @return string|unknown
	 */
	function hanaboard_bloginfo_description($info, $show) {
		if (is_hanaboard_page( 'view' )) {
			$post = hanaboard_get_the_post();
			$description = substr( wp_strip_all_tags( $post->post_content, true ), 0, 200 ) . $info;
			return $description;
		} else {
			return $info;
		}
	}
}
add_filter( 'hanaboard_format_add_form', 'hanaboard_format_add_form', 10, 2 );
if (! function_exists( 'hanaboard_format_add_form' )) {
	function hanaboard_format_add_form($html, $name, $label = null, $required = false) {
		if ($label)
			$col_width = '12';
		$html = '<div class="row form-group">';
		$html .= '<label for="' . $name . '" class="col-xs-3 control-label nopadding">';
		if ($label)
			$html .= $label;
		if ($required)
			$html .= '<span class="required"></span>';
		if ($label)
			$html .= '</label>';
		if ($label)
			$col_width = '9';
		
		$html .= '<div class="col-xs-' . $col_width . ' nopadding">';
		$html .= '</div></div>';
		return $html;
	}
}
add_action( 'hanaboard_before_template', 'hanaboard_before_template' );
if (! function_exists( 'hanaboard_before_template' )) {
	function hanaboard_before_template() {
		global $hanaboard_option;
		$hanaboard_option ['in_shortcode'] = true;
	}
}
add_action( 'hanaboard_after_template', 'hanaboard_after_template' );
if (! function_exists( 'hanaboard_after_template' )) {
	function hanaboard_after_template() {
		global $hanaboard_option;
		$hanaboard_option ['in_shortcode'] = false;
	}
}
if (! function_exists( 'hanaboard_is_in_shortcode' )) {
	function hanaboard_is_in_shortcode() {
		global $hanaboard_option;
		return $hanaboard_option ['in_shortcode'];
	}
}
add_action( 'hanaboard_before_template', 'hanaboard_backup_option' );
if (! function_exists( 'hanaboard_backup_option' )) {
	function hanaboard_backup_option() {
		if (is_hanaboard_page( 'view' )) {
			global $hanaboard_option;
			$hanaboard_option ['original_comment_registration'] = get_option( 'comment_registration' );
			$comment_registration = "1";
			if (hanaboard_get_option( 'moderate_comments_hanaboard-post' ) == 'everyone')
				$comment_registration = "";
			update_option( 'comment_registration', $comment_registration );
		}
	}
}
add_action( 'hanaboard_after_template', 'hanaboard_restore_option' );
if (! function_exists( 'hanaboard_restore_option' )) {
	function hanaboard_restore_option() {
		if (is_hanaboard_page( 'view' )) {
			global $hanaboard_option;
			update_option( 'comment_registration', $hanaboard_option ['original_comment_registration'] );
		}
	}
}

// add_filter('the_content', 'my_formatter', 99);
if (! function_exists( 'hanaboard_comment_redirect' )) {
	// add_action( 'comment_post_redirect', 'hanaboard_comment_redirect' );
	// Redirect
	// to thank
	// you post
	// after
	// comment
	function hanaboard_comment_redirect() {
		return get_post_permalink( get_the_ID() );
	}
}

/**
 * If the user isn't logged in, redirect
 * to the login page
 * 
 * @since 0.1
 * @author HanaWordpress
 */
if (! function_exists( 'hanaboard_auth_redirect_login' )) {
	function hanaboard_auth_redirect_login() {
		$user = wp_get_current_user();
		
		if ($user->ID == 0) {
			nocache_headers();
			echo "<script> location.href='" . get_option( 'siteurl' ) . '/wp-login.php?redirect_to=' . urlencode( $_SERVER ['REQUEST_URI'] ) . "';</script>";
			exit();
		}
	}
}

/**
 * Add Image upload button to TinyMCE.
 * Currently disabled.
 */
add_action( 'init', 'hana_mce_buttons' );
function hana_mce_buttons() {
	if (is_hanaboard_page( 'form' )) {
		// add_filter('mce_external_plugins', 'hanaboard_tinymce_add_buttons');
		add_filter( 'mce_buttons', 'hanaboard_tinymce_register_buttons' );
	}
}
function hanaboard_tinymce_add_buttons($plugin_array) {
	$plugin_array ['wptuts'] = hanaboard_plugins_url( 'js/mce_plugin.js' );
	return $plugin_array;
}
function hanaboard_tinymce_register_buttons($buttons) {
	$buttons = array_diff( $buttons, array (
			"wp_more",
			"spellchecker" 
	) );
	
	array_unshift( $buttons, 'hanaboard_upload_image', 'hanaboard_upload_video' ); // dropcap',
	                                                                             // 'recentposts
	
	return $buttons;
}

/**
 * Remove the mdedia upload tabs from subscribers
 * 
 * @package Hana Board
 * @author HanaWordpress
 */
if (! function_exists( 'hanaboard_unset_media_tab' )) {
	add_filter( 'media_upload_tabs', 'hanaboard_unset_media_tab' );
	function hanaboard_unset_media_tab($list) {
		if (! current_user_can( 'edit_posts' )) {
			// unset( $list['library'] );
			// unset( $list['gallery'] );
		}
		
		return $list;
	}
}

if (! function_exists( 'hanaboard_the_content_filter' )) {
	add_filter( 'the_content', 'hanaboard_the_content_filter', 10, 1 );
	function hanaboard_the_content_filter($content) {
		if (get_post_type() == HANA_BOARD_POST_TYPE) {
			if (is_hanaboard_page( 'write_reply' )) {
				$html = '<br/><br/><br/><br/><div style="margin-top:30px;">';
				$html .= __( '&lt;Original content&gt;', HANA_BOARD_TEXT_DOMAIN ) . '';
				$html .= '<div style="margin-left:20px;">';
				$html .= $content;
				$html .= '</div></div>';
				return $html;
			} else {
				if (is_hanaboard_page( 'write' ))
					return '';
				else
					return $content;
			}
		} else
			return $content;
	}
}

if (! function_exists( 'hanaboard_the_title_filter' )) {
	// add_filter( 'the_title', 'hanaboard_the_title_filter', 99 );
	function hanaboard_the_title_filter($title) {
		if (is_hanaboard_page( 'write_reply' )) {
			$post_parent = hanaboard_get_parent_post_for_write_reply();
			
			$new_title = __( 'Re : ', HANA_BOARD_TEXT_DOMAIN ) . $post_parent->post_title;
			return $new_title;
		} else if (is_hanaboard_page( 'write' )) {
			return '';
		} else {
			return $title;
		}
	}
}

if (! function_exists( 'hanaboard_the_title_trim' )) {
	add_filter( 'the_title', 'hanaboard_the_title_trim' );
	function hanaboard_the_title_trim($title) {
		$title = esc_Attr( $title );
		$findthese = array (
				'/' . __( 'Protected: ', HANA_BOARD_TEXT_DOMAIN ) . '/',
				'/' . __( 'Private: ', HANA_BOARD_TEXT_DOMAIN ) . '/' 
		);
		$replacewith = array (
				'', // What to replace "Protected:" with
				'' 
		); // What to replace "Private:" with
		
		$title = preg_replace( $findthese, $replacewith, $title );
		return $title;
	}
}

if (! function_exists( 'hanaboard_the_author_filter' )) {
	add_filter( 'the_author', 'hanaboard_the_author_filter', 10, 1 );
	function hanaboard_the_author_filter($name) {
		if ($name) {
			return '<span class="the_author">' . $name . '</span>';
		} else {
		}
	}
}

if (! function_exists( 'remove_img_attr' )) {
	add_filter( 'post_thumbnail_html', 'remove_img_attr' );
	function remove_img_attr($html) {
		return preg_replace( '/(width|height)="\d+"\s/', "", $html );
	}
}

if (! function_exists( 'hanaboard_post_type_link' )) {
	add_filter( 'post_type_link', 'hanaboard_post_type_link', 10, 2 );
	function hanaboard_post_type_link($url, $post) {
		if ($post->post_type == HANA_BOARD_POST_TYPE) {
			if (hanaboard_is_in_shortcode() && (is_hanaboard_page( 'list' ) || is_hanaboard_page( 'view' ))) 
				return hanaboard_get_the_permalink( $post->ID, true );
			else
				return hanaboard_get_the_permalink( $post->ID, false );
		} else
			return $url;
	}
}

/**
 * Adds notices on add post form if any
 * 
 * @param string $text        	
 * @return string
 */
if (! function_exists( 'hanaboard_addpost_notice' )) {
	function hanaboard_addpost_notice($text) {
		add_filter( 'hanaboard_addpost_notice', 'hanaboard_addpost_notice' );
		$user = wp_get_current_user();
		
		// if ( is_user_logged_in() ) {
		$lock = ($user->hanaboard_postlock == 'yes') ? 'yes' : 'no';
		
		if ($lock == 'yes') {
			return $user->hanaboard_lock_cause;
		}
		
		$force_pack = hanaboard_get_option( 'force_pack' );
		$post_count = (isset( $user->hanaboard_sub_pcount )) ? intval( $user->hanaboard_sub_pcount ) : 0;
		
		if ($force_pack == 'yes' && $post_count == 0) {
			return __( 'You must purchase a pack before posting', HANA_BOARD_TEXT_DOMAIN );
		}
		// }
		
		return $text;
	}
}

/**
 * Adds the filter to the add post form if the user can post or not
 * 
 * @param string $perm
 *        	permission type. "yes" or "no"
 * @return string permission type. "yes" or "no"
 */
if (! function_exists( 'hanaboard_can_post' )) {
	add_filter( 'hanaboard_can_post', 'hanaboard_can_post' );
	function hanaboard_can_post($perm) {
		$user = wp_get_current_user();
		
		// if ( is_user_logged_in() ) {
		$lock = ($user->hanaboard_postlock == 'yes') ? 'yes' : 'no';
		
		if ($lock == 'yes') {
			return 'no';
		}
		
		$force_pack = hanaboard_get_option( 'force_pack' );
		$post_count = (isset( $user->hanaboard_sub_pcount )) ? intval( $user->hanaboard_sub_pcount ) : 0;
		
		if ($force_pack == 'yes' && $post_count == 0) {
			return 'no';
		}
		// }
		
		return $perm;
	}
}

if (! function_exists( 'hanaboard_header_css' )) {
	add_action( 'wp_head', 'hanaboard_header_css' );
	function hanaboard_header_css() {
		$css = apply_filters('hanaboard_header_css', hanaboard_get_option( 'custom_css' ));
		echo "<style type=\"text/css\">$css</style>";
	}
	function hanaboard_link_color_filter($css) {

		$link_color = hanaboard_get_option('link_color');
		if(! empty($link_color)) {
			$css .= ".hanaboard-page-view a, .hanaboard-list a { color: $link_color !important; } ";
		}

		$link_hover_color = hanaboard_get_option('link_hover_color');
		if(! empty($link_hover_color)) {
			$css .= ".hanaboard-page-view a:hover, .hanaboard-list a:hover { color: $link_hover_color !important; }";
		}

		return $css;
	}
	add_filter('hanaboard_header_css', 'hanaboard_link_color_filter', 10, 1);
}

if (! function_exists( 'hanaboard_post_types_filter' )) {
	add_filter( 'hanaboard_post_types', 'hanaboard_post_types_filter', 10, 1 );
	function hanaboard_post_types_filter($post_types) {
		if (! is_array( $post_types ))
			$post_types = array (
					$post_types 
			);
		
		if (! in_array( HANA_BOARD_POST_TYPE, $post_types ))
			$post_types [] = HANA_BOARD_POST_TYPE;
		
		return $post_types;
	}
}

if (! function_exists( 'hanaboard_tag_archive_filter' )) {
	add_filter( 'pre_get_posts', 'hanaboard_tag_archive_filter' );
	function hanaboard_tag_archive_filter($query) {
		if (is_tag()) {
			$post_type = get_query_var( 'post_type' );
			if ($post_type)
				$post_type = $post_type;
			else
				$post_type = apply_filters( 'hanaboard_post_types', array (
						'post' 
				) );
			$query->set( 'post_type', $post_type );
			return $query;
		}
	}
}
if (! function_exists( 'hanaboard_display_like_links_on_filter' )) {
	add_filter( 'hanaboard_display_like_links_on', 'hanaboard_display_like_links_on_filter', 10, 1 );
	function hanaboard_display_like_links_on_filter($post_types) {
		$hanaboard_post_types = apply_filters( 'hanaboard_post_types', $post_types );
		;
		return array_merge( $post_types, $hanaboard_post_types );
	}
}

if (! function_exists( 'hanaboard_display_like_link_filter' )) {
	add_filter( 'hanaboard_display_like_link_filter', 'hanaboard_display_like_link_filter', 10, 1 );
	function hanaboard_display_like_link_filter($cond) {
		return $cond && is_hanaboard_page( 'view' );
	}
}

if (! function_exists( 'hanaboard_write_content_filter' )) {
	add_filter( 'hanaboard_write_content', 'hanaboard_write_content_filter', 10, 1 );
	function hanaboard_write_content_filter($content) {
		return str_replace( '<!--more-->', '', trim( $content ) );
	}
}

if (! function_exists( 'hanaboard_addlightboxrel' )) {
	add_filter( 'the_content', 'hanaboard_addlightboxrel' );
	function hanaboard_addlightboxrel($content) {
		if (get_post_type() == HANA_BOARD_POST_TYPE) {
			global $post;
			$pattern = "/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
			$replacement = '<a$1href=$2$3.$4$5 rel="lightbox" data-lightbox="hanaboard" title="' . $post->post_title . '"$6>';
			$content = preg_replace( $pattern, $replacement, $content );
		}
		return $content;
	}
}

// this function initializes the iframe elements
if (! function_exists( 'hanaboard_add_iframe' )) {
	// this function alters the way the WordPress editor filters your code
	add_filter( 'tiny_mce_before_init', 'hanaboard_add_iframe' );
	function hanaboard_add_iframe($initArray) {
		$initArray ['extended_valid_elements'] = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
		return $initArray;
	}
}

if (! function_exists( 'hanaboard_allow_post_tags' )) {
	add_filter( 'wp_kses_allowed_html', 'hanaboard_allow_post_tags', 1 );
	// allow script & iframe tag within posts
	function hanaboard_allow_post_tags($allowedposttags) {
		$allowedposttags ['script'] = array (
				'type' => true,
				'src' => true,
				'height' => true,
				'width' => true 
		);
		$allowedposttags ['iframe'] = array (
				'src' => true,
				'width' => true,
				'height' => true,
				'class' => true,
				'frameborder' => true,
				'webkitAllowFullScreen' => true,
				'mozallowfullscreen' => true,
				'allowFullScreen' => true 
		);
		return $allowedposttags;
	}
}
?>