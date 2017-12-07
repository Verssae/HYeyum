<?php
if (! defined ( 'ABSPATH' ))
	exit ();

/**
 * Get other templates (e.g.
 * product attributes) passing attributes and including the file.
 *
 * @access public
 * @param string $template_name        	
 * @param array $args
 *        	(default: array())
 * @param string $template_path
 *        	(default: '')
 * @param string $default_path
 *        	(default: '')
 * @return void
 */
if (! function_exists ( 'hanaboard_get_template' )) {
	function hanaboard_get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
		if ($args && is_array ( $args )) {
			extract ( $args );
		}
		
		$located = hanaboard_locate_template ( $template_name, $template_path, $default_path );
		
		if (! file_exists ( $located )) {
			_doing_it_wrong ( __FUNCTION__, sprintf ( '<code>%s</code> - <code>%s</code> does not exist.', $template_name, $located ), '2.1' );
			return;
		}
		
		// Allow 3rd party plugin filter template file from their plugin
		$located = apply_filters ( 'hanaboard_get_template', $located, $template_name, $args, $template_path, $default_path );
		
		do_action ( 'hanaboard_before_template_part', $template_name, $template_path, $located, $args );
		include ($located);
		do_action ( 'hanaboard_after_template_part', $template_name, $template_path, $located, $args );
	}
}

/**
 * Locate a template and return the path for inclusion.
 * This is the load order:
 * yourtheme / $template_path / $template_name
 * yourtheme / $template_name
 * $default_path / $template_name
 *
 * @access public
 * @param string $template_name        	
 * @param string $template_path
 *        	(default: '')
 * @param string $default_path
 *        	(default: '')
 * @return string
 */
if (! function_exists ( 'hanaboard_locate_template' )) {
	function hanaboard_locate_template($template_name, $template_path = '', $default_path = '') {
		if (! $template_path) {
			// $template_path = WC()->template_path();
			$template_path = hanaboard_plugin_dir () . 'templates/';
		}
		if (! $default_path) {
			// $default_path = HANA_BOARD()->plugin_path() . '/templates/';
			$default_path = $template_path;
		}
		
		// Look within passed path within the theme - this is priority
		$template = locate_template ( array (
				trailingslashit ( $template_path ) . $template_name,
				$template_name 
		) );
		// Get default template
		if (! $template) {
			$template = $default_path . $template_name;
		}
		
		// Return what we found
		return apply_filters ( 'hanaboard_locate_template', $template, $template_name, $template_path );
	}
}

/**
 * Get template part
 *
 * @access public
 * @param mixed $slug        	
 * @param string $name
 *        	(default: '')
 * @return void
 */
function hanaboard_get_template_part($slug, $name = '') {
	$template = '';
	
	// Look in yourtheme/slug-name.php
	if ($name) {
		$template = locate_template ( array (
				"{$slug}-{$name}.php",
				hanaboard_plugins_dir () . "templates/{$slug}-{$name}.php" 
		) );
	}
	
	// Get default slug-name.php
	if (! $template && $name && file_exists ( hanaboard_plugins_dir () . "templates/ {
		$slug}- {
		$name}.php" )) {
		$template = hanaboard_plugins_dir () . "templates/{$slug}-{$name}.php";
	}
	
	// If template file doesn't exist, look in yourtheme/slug.php and
	// yourtheme/woocommerce/slug.php
	if (! $template) {
		$template = locate_template ( array (
				"{$slug}.php",
				hanaboard_plugins_dir () . "templates/{$slug}.php" 
		) );
	}
	
	// Allow 3rd party plugin filter template file from their plugin
	if (! $template) {
		$template = apply_filters ( 'hanaboard_get_template_part', $template, $slug, $name );
	}
	
	if ($template) {
		load_template ( $template, false );
	}
}

if (! function_exists ( 'hanaboard_skin_option' )) {
	function hanaboard_skin_option() {
		$args = array ();
		include_once (hanaboard_get_current_skin_dir () . 'functions.php');
	}
}

/* Archives list */
if (! function_exists ( 'hanaboard_archive_list_header' )) {
	function hanaboard_archive_list_header() {
		$args = array ();
		hanaboard_get_template ( '_list/loop-start.php', $args, hanaboard_get_current_skin_dir () );
	}
}

if (! function_exists ( 'hanaboard_archive_list_loop' )) {
	function hanaboard_archive_list_loop() {
		$args = array ();
		$args ['ellipsis'] = (hanaboard_get_option ( 'title_ellipsis' )) ? 'ellipsis' : '';
		
		global $post;
		$hanaboard_posts = get_posts ( hanaboard_archive_list_query_args () );
		// Get Post No
		$big = 999999999;
		$query_arg = hanaboard_archive_list_query_args ( array (
				'posts_per_page' => $big 
		) );
		$query_arg ['paged'] = 1;
		$posts_for_count = get_posts ( $query_arg );
		$total_posts = count ( $posts_for_count );
		unset ( $posts_for_count );
		$paged = get_query_var ( 'paged' ) ? get_query_var ( 'paged' ) : 1;
		$post_no_from = $total_posts - (($paged - 1) * hanaboard_get_option ( 'posts_per_page' ));
		global $hanaboard_current_post_no;
		$hanaboard_current_post_no = $post_no_from;
		if (sizeof ( $hanaboard_posts ) > 0) {
			
			foreach ( $hanaboard_posts as $hanaboard_post ) {
				$post = clone $hanaboard_post;
				setup_postdata ( $post );
				$depth = 0;
				
				include hanaboard_get_current_skin_dir () . '_list/loop.php';
				
				hanaboard_archive_list_loop_child_recursive ( get_the_ID (), 0 );
				$hanaboard_current_post_no = $hanaboard_current_post_no - 1;
			}
		} else {
			hanaboard_get_template ( '_list/loop_no_post.php', $args, hanaboard_get_current_skin_dir () );
		}
	}
}

if (! function_exists ( 'hanaboard_archive_list_loop_child_recursive' )) {
	function hanaboard_archive_list_loop_child_recursive($post_id, $depth = 1) {
		$args = array ();
		$args ['ellipsis'] = (hanaboard_get_option ( 'title_ellipsis' )) ? 'ellipsis' : '';
		
		$child_post_args = array (
				'post_parent' => $post_id, // Only shows posts that are direct
				                           // children of the Machinery page. I want all
				                           // descendants.
				'post_status' => array (
						'publish',
						'private' 
				),
				'post_type' => HANA_BOARD_POST_TYPE,
				'taxonomy' => HANA_BOARD_TAXONOMY 
		);
		
		global $post;
		$hanaboard_posts = get_posts ( $child_post_args );
		
		if (sizeof ( $hanaboard_posts ) > 0) {
			$depth ++;
			foreach ( $hanaboard_posts as $hanaboard_post ) {
				$post = clone $hanaboard_post;
				setup_postdata ( $post );
				$args ['depth'] = $depth;
				hanaboard_get_template ( '_list/loop.php', $args, hanaboard_get_current_skin_dir () );
				hanaboard_archive_list_loop_child_recursive ( $post->ID, $depth );
			}
		}
	}
}

if (! function_exists ( 'hanaboard_archive_list_loop_notice' )) {
	function hanaboard_archive_list_loop_notice() {
		global $post;
		
		$args ['ellipsis'] = (hanaboard_get_option ( 'title_ellipsis' )) ? 'ellipsis' : '';
		
		$args = array ();
		if ((! get_query_var ( 'paged' ) || get_query_var ( 'paged' ) == 1) && ! get_query_var ( 'search-str' )) {
			
			$hanaboard_posts = get_posts ( hanaboard_archive_list_notice_query_args () );
			
			if (sizeof ( $hanaboard_posts ) > 0) {
				foreach ( $hanaboard_posts as $hanaboard_post ) {
					$post = clone $hanaboard_post;
					setup_postdata ( $post );
					hanaboard_get_template ( '_list/loop_notice.php', $args, hanaboard_get_current_skin_dir () );
				}
			}
		}
	}
}

if (! function_exists ( 'hanaboard_archive_list_footer' )) {
	function hanaboard_archive_list_footer() {
		$args = array ();
		hanaboard_get_template ( '_list/loop-end.php', $args, hanaboard_get_current_skin_dir () );
	}
}
add_filter ( 'paginate_links', 'hanaboard_pagenum_link_filter', 1 );
function hanaboard_pagenum_link_filter($result) {
	return remove_query_arg ( 'article', $result );
}
if (! function_exists ( 'hanaboard_archive_list_paging' )) {
	function hanaboard_archive_list_paging() {
		$total_posts = hanaboard_get_total_posts ();
		$big = 9999999999;
		$total = ceil ( $total_posts / hanaboard_get_option ( 'posts_per_page' ) );
		$pagination = paginate_links ( array (
				'base' => str_replace ( $big, '%#%', get_pagenum_link ( $big, false ) ),
				'format' => '&paged=%#%',
				// 'current' => max( 1, get_query_var( 'paged' ) ),
				'current' => max ( 1, hanaboard_get_paged () ),
				
				'total' => $total,
				'prev_next' => false,
				'num_pages' => 5,
				'prev_text' => __ ( '&laquo;', HANA_BOARD_TEXT_DOMAIN ),
				'next_text' => __ ( '&raquo;', HANA_BOARD_TEXT_DOMAIN ),
				'type' => 'array' 
		) );
		$args ['pagination'] = $pagination;
		hanaboard_get_template ( '_list/paging.php', $args, hanaboard_get_current_skin_dir () );
	}
}

if (! function_exists ( 'hanaboard_archive_list_buttons' )) {
	function hanaboard_archive_list_buttons() {
		$args = array ();
		hanaboard_get_template ( '_list/buttons.php', $args, hanaboard_get_current_skin_dir () );
	}
}

if (! function_exists ( 'hanaboard_archive_list_search' )) {
	function hanaboard_archive_list_search() {
		$args = array ();
		hanaboard_get_template ( '_list/search.php', $args, hanaboard_get_current_skin_dir () );
	}
}

/* Single post view */
if (! function_exists ( 'hanaboard_single_post' )) {
	function hanaboard_single_post() {
		$args = array ();
		$term = hanaboard_get_current_term ();
		$args ['term_slug'] = $term->slug;
		$args ['term_name'] = $term->name;
		
		$query_arg = array (
				'mode' => 'write_reply',
				HANA_BOARD_POST_TYPE . '-parent' => get_the_ID () 
		);
		$args ['reply_link'] = hanaboard_add_query_arg ( $query_arg );
		
		hanaboard_update_readcount ();
		hanaboard_get_template ( '_view/view.php', $args, hanaboard_get_current_skin_dir () );
	}
}

if (! function_exists ( 'hanaboard_show_attachments_list' )) {
	function hanaboard_show_attachments_list($post_id) {
		if (hanaboard_get_option ( 'allow_attachment' )) {
			$attach = '';
			$attachments = hanaboard_get_attachments ( $post_id );
			if ($attachments) {
				$attach = '<ul class="hanaboard-attachments">';
				
				foreach ( $attachments as $file ) {
					// if the attachment is image, show the image. else show the
					// link
					if (hanaboard_is_file_image ( $file ['url'], $file ['mime'] )) {
						$thumb = wp_get_attachment_image_src ( $file ['id'] );
						$attach .= sprintf ( '<li><a href="%s"><img src="%s" alt="%s" /></a></li>', $file ['url'], $thumb [0], urldecode ( esc_attr ( $file ['title'] ) ) );
					} else {
						$attach .= sprintf ( '<li><a href="%s" title="%s">%s</a></li>', $file ['url'], urldecode ( esc_attr ( $file ['title'] ) ), urldecode ( $file ['title'] ) );
					}
				}
				$attach .= '</ul>';
			}
		}
		echo $attach;
	}
}

if (! function_exists ( 'hanaboard_single_post_buttons' )) {
	function hanaboard_single_post_buttons() {
		$args = array ();
		$query_arg = array (
				'mode' => 'write_reply',
				HANA_BOARD_POST_TYPE => null,
				HANA_BOARD_POST_TYPE . '-parent' => get_the_ID () 
		);
		$args ['reply_link'] = hanaboard_add_query_arg ( $query_arg );
		hanaboard_get_template ( '_view/buttons.php', $args, hanaboard_get_current_skin_dir () );
	}
}

/* Single Form */
if (! function_exists ( 'hanaboard_single_post_form' )) {
	function hanaboard_single_post_form() {
		$args = array ();
		$term = hanaboard_get_current_term ();
		$args ['term_slug'] = is_object ( $term ) ? $term->slug : '';
		$args ['term_id'] = is_object ( $term ) ? $term->term_id : '';
		$args ['term_name'] = is_object ( $term ) ? $term->name : '';
		
		$args ['post_parent_id'] = null;
		$args ['post_id'] = null;
		$args ['post_title'] = '';
		$args ['post_content'] = hanaboard_get_option ( 'default_content' );
		$args ['tags'] = '';
		$args = apply_filters ( 'hanaboard_form_values', $args );
		
		$args ['editor_args'] = array (
				'textarea_name' => 'post_content',
				'editor_class' => 'post_content',
				'textarea_rows' => 20,
				'dfw' => true,
				'drag_drop_upload' => true,
				'tabfocus_elements' => 'save-post',
				'quicktags' => true,
				'media_buttons' => true 
		);
		
		remove_filter ( 'the_content', 'wpautop' );
		remove_filter ( 'the_content', 'wptexturize' );
		
		if (is_hanaboard_page ( 'edit' )) {
			global $post;
			$post_id = get_query_var ( 'article' );
			$hanaboard_post = get_post ( $post_id );
			$post = clone $hanaboard_post;
			setup_postdata ( $post );
			$args ['post_id'] = $post->ID;
			$args ['post_title'] = $post->post_title;
			$args ['post_content'] = $post->post_content;
			$args ['post_parent_id'] = $post->post_parent;
			$args ['tags'] = hanaboard_get_the_tags ( $post->ID );
		} else if (is_hanaboard_page ( 'write_reply' )) {
			$post_parent = hanaboard_get_parent_post_for_write_reply ();
			$args ['post_parent_id'] = $post_parent->ID;
			
			$post = get_post ( $post_parent->ID );
			$args ['post_title'] = hanaboard_the_title_filter ( $post->post_title );
			// $args['post_content'] = hanaboard_the_content_filter($post->post_content);
		} else {
			$args ['post_id'] = 0;
			$args ['post_title'] = '';
		}
		hanaboard_get_template ( '_form/form.php', $args, hanaboard_get_current_skin_dir () );
	}
}
