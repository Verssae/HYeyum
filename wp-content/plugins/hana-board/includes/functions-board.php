<?php
if (! defined( 'ABSPATH' ))
	exit();

if (! function_exists( 'hanaboard_get_shortcode' )) {
	function hanaboard_get_shortcode($term_slug) {
		return hanaboard_get_shortcode_header() . $term_slug . '"]';
	}
}
if (! function_exists( 'hanaboard_get_shortcode_header' )) {
	function hanaboard_get_shortcode_header() {
		return '[hanaboard board="';
	}
}
if (! function_exists( 'hanaboard_get_image_size' )) {
	function hanaboard_get_image_size($image_size) {
		if (in_array( $image_size, array (
				'post_gallery',
				'post_webgine' 
		) )) {
			$size = get_option( $image_size . '_image_size', array () );
			$size ['width'] = isset( $size ['width'] ) ? $size ['width'] : '300';
			$size ['height'] = isset( $size ['height'] ) ? $size ['height'] : '300';
			$size ['crop'] = isset( $size ['crop'] ) ? $size ['crop'] : 0;
		} else {
			$size = array (
					'width' => '300',
					'height' => '300',
					'crop' => 1 
			);
		}
		
		return apply_filters( 'hanaboard_get_image_size_' . $image_size, $size );
	}
}
if (! function_exists( 'hanaboard_is_board_admin' )) {
	function hanaboard_is_board_admin($term_id = null) {
		if (current_user_can( 'editor' ) || current_user_can( 'administrator' ))
			return true;
		else if (! is_user_logged_in())
			return false;
		else if ($term_id || hanaboard_get_current_term_id()) {
			if(is_null($term_id))
				$term_id = hanaboard_get_current_term_id();
			$board_admin = explode(',', hanaboard_get_option('board_admin_users', $term_id));
			$user_login = wp_get_current_user()->user_login;

			if(in_array($user_login,$board_admin))
				return true;
		}
		return false;
	}
}
if (! function_exists( 'hanaboard_current_user_can' )) {
	function hanaboard_current_user_can($capability, $args = array()) {
		if (current_user_can('manage_options'))
			return true;

		$hanaboard_post_type = apply_filters( 'hanaboard_post_type', HANA_BOARD_POST_TYPE );
		$hanaboard_taxonomy = apply_filters( 'hanaboard_taxonomy', HANA_BOARD_TAXONOMY );
		
		// If HanaBoard Capability
		$cap = $capability . '_' . $hanaboard_post_type;
		$hanaboard_caps = array (
				'edit',
				'delete',
				'read',
				'comment',
				'list',
				'write_reply',
				'notice',
				'write'
		);

		if (! in_array( $capability, $hanaboard_caps ))
			return current_user_can( $cap, $args );
		
		$post_id_required_cap = array (
				'edit',
				'delete',
				'read' 
		);
		global $post;
		if (get_query_var( 'article' )) {
			$post_id = $post->ID;
		}
		if (in_array( $capability, $post_id_required_cap )) {
			if (! is_object( $post ))
				return false;
		} else if ($capability == 'write_reply') {
			$term = hanaboard_get_current_term();
			if (is_object( $term ) && ! hanaboard_get_option( 'allow_replies' ))
				return false;
			
			$parent_post = hanaboard_get_parent_post_for_write_reply();
			if (is_object( $parent_post ))
				$post_id = $parent_post->ID;
		}
		
		$term_id = hanaboard_get_current_term_id();
		if(hanaboard_is_board_admin($term_id))
			return true;

		if ($capability == 'edit' || $capability == 'delete') {
			$post = get_post( $post_id );
			if (is_object( $post )) {
				if ($post->post_author == 0)
					return true; // need to enter password
				else if ($post->post_author == get_current_user_id())
					return true;
				else
					return current_user_can( 'delete_others_posts' );
			}
			return false;
		} else if ('write_reply' == $capability) {
			if (hanaboard_get_post_meta( $post_id, 'is_notice' ) == 'on')
				return false;
		} else if ('notice' == $capability) {
			if (is_hanaboard_page( 'write_reply' ))
				return false;
			else if (current_user_can( 'activate_plugins' ))
				return true;
			else
				return false;
		}

		if (in_array( $capability, array (
				'list',
				'write',
				'write_reply',
				'comment',
				'read' 
		) )) {
			if ($capability == 'write_reply')
				$cap = 'write_reply_' . $hanaboard_post_type;
			else if ($capability == 'write')
				$cap = 'publish_' . $hanaboard_post_type;
			else if ($capability == 'comment')
				$cap = 'moderate_comments_' . $hanaboard_post_type;

			$board_permission = hanaboard_get_option( $cap, $term_id );

			// 게시판 관리자는 앞에서 return true
			if ($board_permission == 'board_admin')
				return false;
			if (isset( $post_id ) && hanaboard_is_post_private( $post_id )) {
				if (current_user_can( 'delete_others_posts' ))
					return true;
				else if ((get_current_user_id() == $post->post_author) && $post->post_author > 0)
					return true;
				else
					return false;
			} else if ($board_permission != 'everyone' && ! is_user_logged_in())
				return false;
			else if ($board_permission == 'everyone')
				return true;
		}
		return current_user_can( $cap, $args );
	}
}

if (! function_exists( 'hanaboard_get_last_post_no' )) {
	function hanaboard_get_last_post_no($term_id) {
		global $wpdb;
		$querystr = "
			SELECT MAX(CAST($wpdb->postmeta.meta_value AS SIGNED))
			FROM $wpdb->postmeta, $wpdb->term_relationships
			WHERE $wpdb->postmeta.post_id = $wpdb->term_relationships.object_id
			AND $wpdb->postmeta.meta_key LIKE 'hanaboard_post_no'
			AND $wpdb->term_relationships.term_taxonomy_id = ( SELECT $wpdb->term_taxonomy.term_taxonomy_id
			FROM $wpdb->term_taxonomy WHERE $wpdb->term_taxonomy.term_id =" . $term_id . ")";
		$no = $wpdb->get_var( $querystr );
		return intval( $no );
	}
}

if (! function_exists( 'hanaboard_get_option' )) {
	function hanaboard_get_option($key, $term_id = null) {
		$tax_meta_header = HANA_BOARD_TAX_META_HEADER;
		if (! $term_id) {
			$terms = hanaboard_get_current_term();
			if (is_object( $terms )) {
				$term_id = $terms->term_id;
			}
		} 
		//if (! is_object( $terms )) {
		if( ! $term_id ) {
			if (is_hanaboard_page()) {
				$post = get_post( get_query_var( 'page_id' ) );
				foreach ( shortcode_parse_atts( $post->post_content ) as $v ) {
					preg_match( '/board= *["\']?([^"\']*)/i', $v, $matches );
				}
				$term = get_term_by( 'slug', $matches [1], HANA_BOARD_TAXONOMY );
				if (is_object( $term )) {
					$term_id = $term->term_id;
				}
			}
		}
		$m = get_option( $tax_meta_header . $term_id );
		if (isset( $m [$key] ))
			return $m [$key];
		else
			return false;
	}
}

if (! function_exists( 'hanaboard_update_option' )) {
	function hanaboard_update_option($key, $value, $term_id) {
		$m = get_option( HANA_BOARD_TAX_META_HEADER . $term_id );
		$m [$key] = $value;
		update_option( HANA_BOARD_TAX_META_HEADER . $term_id, $m );
	}
}

if (! function_exists( 'hanaboard_get_option_default' )) {
	function hanaboard_get_option_default($key) {
		$m = get_option( HANA_BOARD_TAX_META_HEADER . 'default' );
		if (isset( $m [$key] )) {
			return $m [$key];
		} else {
			return null;
		}
	}
}

if (! function_exists( 'my_option_posts_per_page' )) {
	function my_option_posts_per_page($value) {
		global $option_posts_per_page;
		if (is_tax( HANA_BOARD_TAXONOMY )) {
			return 2;
		} else {
			return $option_posts_per_page;
		}
	}
}

/**
 * Get lists of users from database
 * 
 * @return array
 */
if (! function_exists( 'hanaboard_list_users' )) {
	function hanaboard_list_users() {
		global $wpdb;
		$users = $wpdb->get_results( "SELECT ID, user_login from $wpdb->users" );
		$list = array ();
		if ($users) {
			foreach ( $users as $user ) {
				$list [$user->ID] = $user->user_login;
			}
		}
		return $list;
	}
}

/**
 * Retrieve or display list of posts as a dropdown (select list).
 * 
 * @return string HTML content, if not displaying.
 */
if (! function_exists( 'hanaboard_get_pages' )) {
	function hanaboard_get_pages() {
		global $wpdb;
		$arr = array ();
		$pages = get_pages();
		if ($pages) {
			foreach ( $pages as $page ) {
				$arr [$page->ID] = $page->post_title;
			}
		}
		
		return $arr;
	}
}

/**
 * Search users with string in display_name or nicename
 * 
 * @param string $str
 *        	search string
 * @param bool $strict
 *        	add % to string if false. e.g. `display_name` LIKE '%$str%'
 * @return array $user_id user id(key)
 */
if (! function_exists( 'hanaboard_search_author' )) {
	function hanaboard_search_author($str, $strict = false) {
		global $wpdb;
		$search_fields = array (
				'display_name',
				'user_nicename' 
		);
		
		$querystr = "SELECT `ID` FROM " . $wpdb->users . " WHERE ";
		$ext = '';
		if (! $strict)
			$ext = '%';
		$q_part = array ();
		foreach ( $search_fields as $v ) {
			$q_part [] = "`${v}` LIKE '" . $ext . esc_sql( $str ) . $ext . "' ";
		}
		$querystr .= implode( ' OR ', $q_part ) . ';';
		
		$users = $wpdb->get_col( $querystr, 0 );
		return $users;
	}
}

if (! function_exists( 'hanaboard_archive_list_query_args' )) {
	function hanaboard_archive_list_query_args($args = array()) {
		$paged = hanaboard_get_paged();
		if( get_query_var('board_cat')) {
			$term = get_term_by('id', get_query_var('board_cat'), HANA_BOARD_TAXONOMY);
			$term_slug = $term->slug;
		} else {
			$term_slug = hanaboard_get_current_term_slug();
		}
		$loop_args = array (
				'post_type' => HANA_BOARD_POST_TYPE,
				'taxonomy' => HANA_BOARD_TAXONOMY,
				'post_parent' => 0,
				'term' => $term_slug,
				'post_status' => array (
						'publish',
						'private' 
				),
				'posts_per_page' => hanaboard_get_option( 'posts_per_page' ),
				'paged' => $paged 
		);
		if (get_query_var( 'search-with' ) == "author") {
			$loop_args ['author__in'] = implode( ',', hanaboard_search_author( get_query_var( 'search-str' ) ) );
		} else if (get_query_var( 'search-with' ) == "title_content") {
			$loop_args ['s'] = get_query_var( 'search-str' );
		} else if (get_query_var( 'search-with' ) == "title") {
			//todo: 제목만 검색
			global $wpdb;
			$posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_type LIKE %s  ", '%'.get_query_var( 'search-str' ).'%', HANA_BOARD_POST_TYPE ));
			if ( $posts )
				$loop_args ['post__in'] = $posts;
			else
				$loop_args['post__in'] = array(-1); // 검색결과 없을때 검색하지 않은 결과가 보이는걸 방지함

		} else if (get_query_var( 'search-with' ) == "content") {
			//todo: 내용만 검색(meta_query 활용)
			$loop_args ['s'] = get_query_var( 'search-str' );
		}
		if(get_query_var('sub_cat')) {
			$loop_args['meta_query'] = array (
				array (
					'key' => HANA_BOARD_TAXONOMY . '_sub_category',
					'compare' => 'LIKE',
					'value' => get_query_var('sub_cat')
				)
			);
		}
		$loop_args = array_merge( $loop_args, $args );
		return $loop_args;
	}
}

if (! function_exists( 'hanaboard_archive_list_notice_query_args' )) {
	function hanaboard_archive_list_notice_query_args() {
		// $term = get_term_by( 'slug', get_query_var(HANA_BOARD_TAXONOMY),
		// HANA_BOARD_TAXONOMY );
		$term = hanaboard_get_current_term();
		$termchildren = get_term_children( $term->term_id, HANA_BOARD_TAXONOMY );
		$args = array (
				'post_type' => HANA_BOARD_POST_TYPE,
				'taxonomy' => HANA_BOARD_TAXONOMY,
				'term' => get_query_var( HANA_BOARD_TAXONOMY ),
				'post_status' => array (
						'publish' 
				),
				'posts_per_page' => hanaboard_get_option( 'posts_per_page' ),
				'post_parent' => 0,
				'tax_query' => array (
						'relation' => 'AND',
						array (
								'taxonomy' => HANA_BOARD_TAXONOMY,
								'field' => 'slug',
								'terms' => $term->slug,
								'operator' => 'IN' 
						),
						array (
								'taxonomy' => HANA_BOARD_TAXONOMY,
								'field' => 'id',
								'terms' => $termchildren,
								'operator' => 'NOT IN' 
						) 
				),
				'meta_query' => array (
						array (
								'key' => HANA_BOARD_TAXONOMY . '_is_notice',
								'compare' => 'LIKE',
								'value' => 'on' 
						) 
				),
				'meta_key' => HANA_BOARD_TAXONOMY . '_is_notice' 
		);
		return $args;
	}
}


/**
 * Format the post status for user dashboard
 * 
 * @param string $status        	
 * @since 0.1
 * @author HanaWordpress
 */
if (! function_exists( 'hanaboard_show_post_status' )) {
	function hanaboard_show_post_status($status) {
		if ($status == 'publish') {
			$title = __( 'Live', HANA_BOARD_TEXT_DOMAIN );
			$fontcolor = '#33CC33';
		} else if ($status == 'draft') {
			$title = __( 'Offline', HANA_BOARD_TEXT_DOMAIN );
			$fontcolor = '#bbbbbb';
		} else if ($status == 'pending') {
			$title = __( 'Awaiting Approval', HANA_BOARD_TEXT_DOMAIN );
			$fontcolor = '#C00202';
		} else if ($status == 'future') {
			$title = __( 'Scheduled', HANA_BOARD_TEXT_DOMAIN );
			$fontcolor = '#bbbbbb';
		}
		echo '<span style="color:' . $fontcolor . ';">' . $title . '</span>';
	}
}

/**
 * Format error message
 * 
 * @param array $error_msg        	
 * @return string
 */
if (! function_exists( 'hanaboard_format_error_msg' )) {
	function hanaboard_format_error_msg($type, $typemsg, $msg) {
		$msg_string = '';
		$html = '<div class="alert alert-' . $type . '"><strong>' . $typemsg . '</strong> ' . $msg . '</div>';
		return $html;
	}
}
if (! function_exists( 'hanaboard_display_errors' )) {
	function hanaboard_display_errors($errors) {
		$html = '';
		foreach ( $errors as $error ) {
			$typemsg = __( 'Error', HANA_BOARD_TEXT_DOMAIN );
			$html .= hanaboard_format_error_msg( $error ['type'], $typemsg, $error ['message'] );
		}
		echo $html;
	}
}

function hanaboard_display_errors_before_load($errors, $show_back_to_home = true) {
	global $hanaboard_skip_template;
	$hanaboard_skip_template = false;
	$html = '';
	foreach ( $errors as $error ) {
		$typemsg = __( 'Error', HANA_BOARD_TEXT_DOMAIN );
		$html .= hanaboard_format_error_msg( $error ['type'], $typemsg, $error ['message'] );
	}
	echo $html;
	if (! current_user_can( 'edit_others_posts' )) {
		$hanaboard_skip_template = true;
		if ($show_back_to_home)
			hanaboard_show_back_to_home_button();
	}
}
function hanaboard_show_back_to_home_button() {
	?>
<div class="row text-center" style="margin-top: 40px; margin-bottom: 40px;">
	<div class="well col-md-4 col-md-offset-4">
		<a href="<?php home_url();?>" class="ui-button-secondary ui-btn-block ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
			<span class="ui-button-text"><?php _e('Back to Home',HANA_BOARD_TEXT_DOMAIN);?></span>
		</a>
	</div>
</div>
<?php
}
if (! function_exists( 'hanaboard_clean_tags' )) {
	// for the price field to make only numbers, periods, and commas
	function hanaboard_clean_tags($string) {
		$string = preg_replace( '/\s*,\s*/', ',', rtrim( trim( $string ), ' ,' ) );
		return $string;
	}
}
function hanaboard_wp_die($msg = '') {
	if (! $msg)
		$msg = __( 'You have no permissions!', HANA_BOARD_TEXT_DOMAIN );
	echo "<script>alert('$msg');</script>";
	echo "<script>history.back();</script>";
	exit();
}
/**
 * Validates any integer variable and sanitize
 * 
 * @param int $int        	
 * @return intger
 */
if (! function_exists( 'hanaboard_is_valid_int' )) {
	function hanaboard_is_valid_int($int) {
		$int = isset( $int ) ? intval( $int ) : 0;
		return $int;
	}
}

/**
 * Notify the admin for new post
 * 
 * @param object $userdata        	
 * @param int $post_id        	
 */
if (! function_exists( 'hanaboard_notify_post_mail' )) {
	if(hanaboard_get_option('email_on_new_post')) {
		add_action('hanaboard_add_post_after_insert', 'hanaboard_notify_post_mail', 10, 1);
	}

	function hanaboard_notify_post_mail($post_id) {
		$blogname = get_bloginfo( 'name' );
		$to = get_bloginfo( 'admin_email' );
		$permalink = get_post_permalink( $post_id );
		$post = get_post($post_id);
		$term = wp_get_post_terms($post_id);
		$headers = sprintf( "From: %s <%s>\r\n", $blogname, $to );
		$subject = sprintf( __( '[%s] New post submission', HANA_BOARD_TEXT_DOMAIN), $blogname );
		
		$msg = sprintf( __( 'A new post has been submitted on %s', HANA_BOARD_TEXT_DOMAIN), $blogname ) . "\r\n\r\n";
		$user = get_user_by('id', $post->post_author);
		if(is_object($user)) {
			$msg .= sprintf(__('Author : %s', HANA_BOARD_TEXT_DOMAIN), $user->display_name) . "\r\n";
			$msg .= sprintf(__('Author Email : %s', HANA_BOARD_TEXT_DOMAIN), $user->user_email) . "\r\n";
		}
		$msg .= sprintf( __( 'Title : %s', HANA_BOARD_TEXT_DOMAIN), get_the_title( $post_id ) ) . "\r\n";
		$msg .= sprintf( __( 'Permalink : %s', HANA_BOARD_TEXT_DOMAIN), $permalink ) . "\r\n";
		$msg .= __( 'Content : ' , HANA_BOARD_TEXT_DOMAIN) . "\r\n" . $post->post_content .  "\r\n\r\n";
		$msg .= sprintf( __( 'Edit Link : %s', HANA_BOARD_TEXT_DOMAIN), admin_url( 'post.php?action=edit&post=' . $post_id ) ) . "\r\n";
		
		// plugin api
		$to = apply_filters( 'hanaboard_notify_to', $to );
		$subject = apply_filters( 'hanaboard_notify_subject', $subject );
		$msg = apply_filters( 'hanaboard_notify_message', $msg, $post_id );
		
		wp_mail( $to, $subject, $msg, $headers );
	}
}

/**
 * Get the registered post types
 * 
 * @return array
 */
if (! function_exists( 'hanaboard_get_post_types' )) {
	function hanaboard_get_post_types() {
		$post_types = get_post_types();
		
		foreach ( $post_types as $key => $val ) {
			if ($val == 'attachment' || $val == 'revision' || $val == 'nav_menu_item') {
				unset( $post_types [$key] );
			}
		}
		return $post_types;
	}
}

/**
 * Find the string that starts with defined word
 * 
 * @param string $string        	
 * @param string $starts        	
 * @return boolean
 */
if (! function_exists( 'hanaboard_starts_with' )) {
	function hanaboard_starts_with($string, $starts) {
		$flag = strncmp( $string, $starts, strlen( $starts ) );
		
		if ($flag == 0) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Displays attachment information upon upload as featured image
 * 
 * @since 0.1
 * @param int $attach_id
 *        	attachment id
 * @return string
 */
if (! function_exists( 'hanaboard_feat_img_html' )) {
	function hanaboard_feat_img_html($attach_id) {
		$image = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
		$post = get_post( $attach_id );
		
		$html = sprintf( '<div class="hanaboard-item" id="attachment-%d">', $attach_id );
		$html .= sprintf( '<img src="%s" alt="%s" />', $image [0], esc_attr( $post->post_title ) );
		$html .= sprintf( '<a class="hanaboard-del-ft-image hanaboard-button" href="#" data-id="%d">%s</a> ', $attach_id, __( 'Remove Image', HANA_BOARD_TEXT_DOMAIN ) );
		$html .= sprintf( '<input type="hidden" name="hanaboard_featured_img" value="%d" />', $attach_id );
		$html .= '</div>';
		
		return $html;
	}
}

/**
 * Category checklist walker
 * 
 * @since 0.1
 */
class HanaBoard_Walker_Category_Checklist extends Walker {
	var $tree_type = 'category';
	var $db_fields = array (
			'parent' => 'parent',
			'id' => 'term_id' 
	);
	// TODO: decouple this
	function start_lvl(&$output, $depth = 0, $args = array()) {
		$indent = str_repeat( "    ", $depth );
		$output .= "$indent<ul class='children'>\n";
	}
	function end_lvl(&$output, $depth = 0, $args = array()) {
		$indent = str_repeat( "    ", $depth );
		$output .= "$indent</ul>\n";
	}
	function start_el(&$output, $category, $depth = 0, $args = array(), $object_id = 0) {
		$popular_cats = null;
		$selected_cats = null;
		extract( $args );
		if (empty( $taxonomy ))
			$taxonomy = 'category';
		
		if ($taxonomy == 'category')
			$name = 'category';
		else
			$name = 'tax_input[' . $taxonomy . ']';
		
		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args ['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
	}
	function end_el(&$output, $category, $depth = 0, $args = array()) {
		$output .= "</li>\n";
	}
}

/**
 * Displays checklist of a taxonomy
 * 
 * @since 0.1
 * @param int $post_id        	
 * @param array $selected_cats        	
 */

if (! function_exists( 'hanaboard_dropdown_category_list' )) {
	
	/**
	 *
	 * @param array $args        	
	 * @return string
	 */
	function hanaboard_dropdown_category_list($args = array()) {
		$default = array (
				'taxonomy' => HANA_BOARD_TAXONOMY,
				'orderby' => 'NAME',
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'hierarchical' => 1,
				'include' => hanaboard_get_option( 'include_cats' ),
				'selected' => hanaboard_get_current_term_id() 
		);
		$category_args = array_merge( $default, $args );
		return wp_dropdown_categories( $category_args );
	}
}

if (! function_exists( 'hanaboard_category_checklist' )) {
	
	/**
	 *
	 * @param number $post_id        	
	 * @param string $selected_cats        	
	 * @param string $tax        	
	 * @param string $exclude        	
	 */
	function hanaboard_category_checklist($post_id = 0, $selected_cats = false, $tax = HANA_BOARD_TAXONOMY, $exclude = false) {
		if (! class_exists( 'Walker_Category_Checklist' )) {
			// require_once ABSPATH . '/wp-admin/includes/template.php';
		}
		$walker = new HanaBoard_Walker_Category_Checklist();
		
		// exclude categories from checklist
		if ($exclude) {
			add_filter( 'list_terms_exclusions', 'hanaboard_category_checklist_exclusions' );
		}
		
		echo '<ul class="hanaboard-category-checklist">';
		wp_terms_checklist( $post_id, array (
				'taxonomy' => $tax,
				'descendants_and_self' => 0,
				'selected_cats' => $selected_cats,
				'popular_cats' => false,
				'walker' => $walker,
				'checked_ontop' => false 
		) );
		echo '</ul>';
	}
}

/**
 * Exclude categories from checklist
 * 
 * @param string $exclusions        	
 * @return string
 */
if (! function_exists( 'hanaboard_category_checklist_exclusions' )) {
	
	/**
	 *
	 * @param unknown $exclusions        	
	 * @return string
	 */
	function hanaboard_category_checklist_exclusions($exclusions) {
		
		// calling hanaboard_get_option generates a recursion fatal error
		// thats why exclue category values picked up manually
		$opt = get_option( 'hanaboard_frontend_posting' );
		if (isset( $opt ['exclude_cats'] ) && ! empty( $opt ['exclude_cats'] )) {
			$exclusions = " AND t.term_id NOT IN({$opt['exclude_cats']})";
		}
		
		return $exclusions;
	}
}
function hanaboard_is_author_guest($post_id = null) {
	if (! $post_id)
		$post_id = get_the_ID();
	$post = get_post( $post_id );
	if ($post->post_author > 0)
		echo 'false';
	else
		echo 'true';
}
add_action( 'hanaboard_after_template', 'offer_to_developer', 99, 1 );
function offer_to_developer() {
	$wp_kses_param = array (
			'a' => array (
					'href' => array (),
					'title' => array (),
					'target' => array () 
			) 
	);
	if (hanaboard_get_option( 'offer_to_developer' )) {
		echo '<p style="font-size: 11px; color: #cccccc !important;">';
		echo sprintf( wp_kses( __( 'Powered by <a href="%1$s" target=_blank title="Hana Board">Hana Board</a>.', HANA_BOARD_TAXONOMY ), $wp_kses_param ), HANAWORDPRESS_HOME );
		echo '</p>';
	}
}

add_action( 'wp_ajax_nopriv_hanaboard_list_admin_action', 'hanaboard_list_admin_action' );
add_action( 'wp_ajax_hanaboard_list_admin_action', 'hanaboard_list_admin_action' );
function hanaboard_list_admin_action() {
	check_ajax_referer( 'hanaboard_nonce', 'nonce' );
	$result = array ();
	if (! hanaboard_is_board_admin($_POST['term_id']))
		$result ['error'] = 1;
	else {
		$list_admin_action = $_POST ['list_admin_action'];
		$posts_id = $_POST ['posts_id'];
		$target = $_POST ['target'];
		$count = 0;
		foreach ( $posts_id as $post_id ) {
			if ($post_id > 0) {
				$my_post = array (
						'ID' => $post_id 
				);
				if ($list_admin_action == 'move') {
					wp_set_post_terms( $post_id, array($target), HANA_BOARD_TAXONOMY );
				} elseif ($list_admin_action == 'trash') {
					$my_post ['post_status'] = 'trash';
					$res_post_id = wp_update_post( $my_post );
					if ($res_post_id)
						$count ++;
				}
			}
		}
		$result ['error'] = 0;
		$result ['count'] = $count;
	}
	die( json_encode($result) );
}

add_action( 'wp_ajax_nopriv_hanaboard_get_sub_categories', 'hanaboard_get_sub_categories' );
add_action( 'wp_ajax_hanaboard_get_sub_categories', 'hanaboard_get_sub_categories' );
function hanaboard_get_sub_categories() {
	check_ajax_referer( 'hanaboard_nonce', 'nonce' );
	$term = get_term_by('id', $_POST['term_id'], HANA_BOARD_TAXONOMY);
	if (! is_object($term))
		echo '';
	else {
		echo hanaboard_get_subcategory_selectbox( $_POST['term_id']);
	}
	exit;
}


function hanaboard_is_display_write_button() {
	if(hanaboard_get_option('show_write_button_with_permission') == 'on') {
		if(hanaboard_current_user_can('write'))
			return true;
		else
			return false;
	}
	return true;
}

function hanaboard_get_post_subcategory($post_id=null) {
	if(! $post_id)
		$post_id = get_the_ID();

	return hanaboard_get_post_meta($post_id, 'sub_category');
}

function hanaboard_has_post_subcategory($post_id=null) {
	if(! $post_id)
		$post_id = get_the_ID();

	return hanaboard_get_post_meta($post_id, 'sub_category') != '';
}
function hanaboard_get_subcategory_map($term_id=null) {
	if(! $term_id)
		$term_id = hanaboard_get_current_term_id();

	$sub_category = hanaboard_get_option('sub_category', $term_id);

	if(empty($sub_category))
		return array();
	$sub_categories = explode(',', $sub_category);
	for($i=0; $i<sizeof($sub_categories); $i++) {
		$sub_categories[$i] = trim($sub_categories[$i]);
	}
	return $sub_categories;
}
function hanaboard_get_subcategory_selectbox($term_id=null, $selected=null) {
	$sub_categories = hanaboard_get_subcategory_map($term_id);
	$html = '<select name="sub_category" id="sub_category_selectbox">';
	foreach($sub_categories as $cat) {
		$selected_html = ($selected == $cat) ? 'selected' : '';
		$html .= '<option value="'.$cat.'" '.$selected_html . '>'.$cat.'</option>';
	}
	$html .= '</select>';
	return $html;
}

function hanaboard_get_filter_subcategory($term_id=null) {
	if(is_null($term_id))
		$term_id = hanaboard_get_current_term_id();

	$term = get_term_by('id', $term_id);

	$children = get_term_children( $term_id, HANA_BOARD_TAXONOMY);
	$html = '';
	if($children) { // get_terms will return false if tax does not exist or term wasn't found.
		// term has children
		$categories = hanaboard_get_subcategory_map($term_id);
		$html .= '<select name="board_category" id="board_category_selectbox" onChange="if (this.value) window.location.href=this.value">';
		$selected_html = empty($selected) ? 'selected' : '';
		$html .= '<option value="' . hanaboard_get_the_term_link($term_id) . '" ' . $selected_html . '>' . __('All', HANA_BOARD_TEXT_DOMAIN) . '</option>';
		foreach ($children as $child) {
			$child_term = get_term_by('id', $child, HANA_BOARD_TAXONOMY);
			$selected_cat = get_query_var('board_cat');
			$url = add_query_arg('board_cat', $child_term->term_id, hanaboard_get_the_term_link($term_id));
			$selected_html = ($selected_cat == $child_term->term_id) ? 'selected' : '';
			$html .= '<option value="' . $url . '" ' . $selected_html . '>' . $child_term->name . '</option>';
		}
		$html .= '</select>';
	}
	if($children) {
		if( get_query_var('board_cat'))
			$sub_categories = hanaboard_get_subcategory_map(get_query_var('board_cat'));
		else
			$sub_categories = null;
	} else {
		$sub_categories = hanaboard_get_subcategory_map($term_id);
	}

	$selected_sub = get_query_var('sub_cat');
	if( $sub_categories ) {
		$html .= '<select name="sub_category" id="sub_category_selectbox" onChange="if (this.value) window.location.href=this.value">';
		$selected_html_sub = empty($selected_sub) ? 'selected' : '';
		$html .= '<option value="' . hanaboard_get_the_term_link($term_id) . '" ' . $selected_html_sub . '>' . __('All', HANA_BOARD_TEXT_DOMAIN) . '</option>';
		foreach ($sub_categories as $cat) {
			if($children) {
				$url = add_query_arg('board_cat', get_query_var('board_cat'), hanaboard_get_the_term_link($term_id) );
				$url = add_query_arg('sub_cat', $cat, $url);
			} else {
				$url = add_query_arg('sub_cat', $cat, hanaboard_get_the_term_link($term_id));
			}
			$selected_html_sub = ($selected_sub == $cat) ? 'selected' : '';
			$html .= '<option value="' . $url . '" ' . $selected_html_sub . '>' . $cat . '</option>';
		}
		$html .= '</select>';
	}
	return $html;
}