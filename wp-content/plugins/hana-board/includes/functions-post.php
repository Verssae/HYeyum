<?php
if (! function_exists ( 'hanaboard_get_total_posts' )) {
	function hanaboard_get_total_posts() {
		global $wpdb;
		$term_id = hanaboard_get_current_term_id ();
		// use mysql query for optimization
        $subCategoryQuery = '';
        $subcat = '';
        if(isset($_GET['sub_cat'])) {
            $subcat = $_GET['sub_cat'];
            $subCategoryQuery = " AND object_id IN (select post_id from $wpdb->postmeta where `meta_key` LIKE 'hanaboard_sub_category' AND `meta_value` like '" . $subcat . "')";
        }
        $subQuery = "SELECT object_id from $wpdb->term_relationships WHERE $wpdb->term_relationships.`term_taxonomy_id`=" . $term_id . $subCategoryQuery;
		$query = "SELECT COUNT(*) FROM $wpdb->posts WHERE $wpdb->posts.`ID` in ($subQuery) AND $wpdb->posts.`post_status` in ('publish', 'private') " . hanaboard_get_search_sql ();
		return $wpdb->get_var ( $query );
	}
}

function hanaboard_get_search_sql() {
	global $wpdb;
	
	if (get_query_var ( 'search-with' ) == 'author')
		$query_search = " AND $wpdb->posts.`post_author` IN ( " . implode ( ',', hanaboard_search_author ( get_query_var ( 'search-str' ) ) ) . " ) ";
	elseif (get_query_var ( 'search-with' ) == 'title_content')
		$query_search = " AND ( $wpdb->posts.`post_title` LIKE '%" . get_query_var ( 'search-str' ) . "%' OR $wpdb->posts.`post_content` LIKE '%" . get_query_var ( 'search-str' ) . "%'  )";
	else
		$query_search = '';
	return $query_search;
}
if (! function_exists ( 'hanaboard_get_paged' )) {
	function hanaboard_get_paged($post_id = null) {
		global $wpdb;
		if (is_hanaboard_page ( 'view' )) {
			if (get_query_var ( 'paged' ))
				return max ( 1, get_query_var ( 'paged' ) );
			$post_id = preg_replace ( "/[^0-9]/", "", get_query_var ( 'article' ) );
			$term_id = hanaboard_get_current_term_id ();
			// use mysql query for optimization
			$current_position = $wpdb->get_var ( "SELECT COUNT(*) FROM $wpdb->posts WHERE $wpdb->posts.`ID` in (SELECT object_id from $wpdb->term_relationships WHERE $wpdb->term_relationships.`term_taxonomy_id`=$term_id) AND $wpdb->posts.`post_status` in ('publish', 'private') AND $wpdb->posts.`ID` >= $post_id " . hanaboard_get_search_sql () . " order by $wpdb->posts.`ID`" );
			$posts_per_page = hanaboard_get_option ( 'posts_per_page' ) > 0 ? hanaboard_get_option ( 'posts_per_page' ) : '15';
			if ($current_position > 0)
				$paged = ceil ( $current_position / $posts_per_page );
			return max ( 1, $paged );
		} else {
			return max ( 1, get_query_var ( 'paged' ) );
		}
	}
}

if (! function_exists ( 'hanaboard_get_post_no' )) {
	function hanaboard_get_post_no($post_id = null) {
		global $hanaboard_current_post_no;
		if (! $post_id)
			$post_id = get_the_ID ();
		if (get_query_var ( 'article' ) == $post_id)
			return '<span class="hanaboard_current_no"><i class="fa fa-angle-double-right"></i></span>';
		else if (hanaboard_is_post_notice ( $post_id ))
			return '<span class="no_notice">' . __ ( 'Notice', HANA_BOARD_TEXT_DOMAIN ) . '</span>';
		else
			return $hanaboard_current_post_no;
	}
}

if (! function_exists ( 'hanaboard_get_the_post_meta' )) {
	function hanaboard_get_the_post_meta($key = null) {
		$post_id = get_the_ID ();
		if (isset ( $post_id ) && $key) {
			$val = hanaboard_get_post_meta ( $post_id, $key, true );
			return $val;
		}
		return null;
	}
}

if (! function_exists ( 'hanaboard_get_post_meta' )) {
	function hanaboard_get_post_meta($post_id, $key = null, $single = true) {
		if ($key) {
			$val = get_post_meta ( $post_id, HANA_BOARD_POST_META_HEADER . $key, $single );
			return $val;
		}
		return null;
	}
}

if (! function_exists ( 'hanaboard_get_the_author' )) {
	// add_filter('the_author',)
	function hanaboard_the_author() {
		echo hanaboard_get_the_author ( $post_id );
	}
	function hanaboard_get_the_author($post_id = null, $filter = true) {
		if (! $post_id)
			$post_id = get_the_ID ();
		$post = get_post ( $post_id );
		if ($post->post_author)
			$author_name = get_the_author_meta ( 'display_name', $post->post_author );
		else {
			$guest = hanaboard_get_post_meta ( $post_id, "guest_author" );
			$author_name = '<span class="the_author_guest">' . $guest . '</span>';
		}
		
		if ($filter)
			return apply_filters ( 'the_author', $author_name );
		else
			return $author_name;
	}
	function hanaboard_get_avatar_url() {
		$get_avatar = get_avatar ( get_the_author_meta ( 'ID' ), 512 );
		preg_match ( '/src="(.*?)"/i', $get_avatar, $matches );
		return $matches [1];
	}
	function hanaboard_get_the_author_ID($post_id = null) {
		if (! $post_id) {
			$post_id = get_the_ID ();
			$post = get_post ( $post_id );
		} else {
			$post = hanaboard_get_the_post ();
		}
		return $post->post_author;
	}
	function hanaboard_get_the_author_url($post_id = null) {
		if (! $post_id) {
			$post_id = get_the_ID ();
			$post = get_post ( $post_id );
		} else {
			$post = hanaboard_get_the_post ();
		}
		$username = get_the_author_meta ( 'username', $post->post_author );
		return get_author_posts_url ( $post->post_author, $username );
	}
	function hanaboard_get_the_author_link($post_id = null) {
		global $authordata;
		if (! $post_id)
			$post_id = get_the_ID ();
		$post = get_post ( $post_id );
		if ($post->post_author) {
			$author_rank = '';
			if (function_exists ( 'mycred_get_users_rank' )) {
				$author_rank = mycred_get_users_rank ( $post->post_author, 'logo' );
			}
			$author_name = get_the_author_meta ( 'display_name', $post->post_author );
			$username = get_the_author_meta ( 'username', $post->post_author );
			$link = sprintf ( '<a href="%1$s" title="%2$s" rel="author">%3$s %4$s</a>', get_author_posts_url ( $post->post_author, $username ), esc_attr ( sprintf ( __ ( 'Posts by %s' ), $author_name ) ), $author_rank, $author_name );
			$link = apply_filters ( 'the_author', $link );
		} else {
			$guest = hanaboard_get_post_meta ( $post_id, "guest_author" );
			$link = '<span class="the_author_guest">' . $guest . '</span>';
		}
		return $link;
	}
}
if (! function_exists ( 'hanaboard_the_excerpt' )) {
	function hanaboard_the_excerpt() {
		echo hanaboard_get_the_excerpt();
	}
	function hanaboard_get_the_excerpt() {
        if(wp_is_mobile())
            $len = hanaboard_get_option('list_excerpt_length_mobile');
        else
            $len = hanaboard_get_option('list_excerpt_length');

        if($len == 0)
            return '';

		$post = get_post ();
		$excerpt = wp_trim_words ( str_replace ( '&nbsp;', ' ', wp_strip_all_tags ( $post->post_content ) ), $len, '...' );
		return $excerpt;
	}
}
if (! function_exists ( 'hanaboard_the_permalink' )) {
	function hanaboard_the_permalink($post_id = null, $include_paged = true) {
		echo hanaboard_get_the_permalink ( $post_id, $include_paged );
	}
}
if (! function_exists ( 'hanaboard_get_the_permalink' )) {
	function hanaboard_get_the_permalink($post_id = null, $include_query_vars = true) {
		if (! $post_id)
			$post_id = get_the_ID ();
		$post = get_post ( $post_id );

        $terms = wp_get_post_terms($post_id, HANA_BOARD_TAXONOMY);
        if (is_array($terms))
            $term = $terms [0];
//todo: 예외처리
        $term_id = $term->term_id;

        // post의 카테고리가 현재 카테고리의 자식일 경우 : 보기페이지의 목록에 현재 보고 있는 목록이 뜨도록 함
        if(hanaboard_get_current_term_id()) {
            $children = get_term_children(hanaboard_get_current_term_id(), HANA_BOARD_TAXONOMY);
            if ($children) {
                if (in_array($term_id, $children)) {
                    $term_id = hanaboard_get_current_term_id();
                }
            }
        }
		$args = array (
				'article' => $post->ID 
		);

		$connected_page = apply_filters ( 'hanaboard_connected_page', hanaboard_get_connected_page ( $term_id ) );
		$permalink_structure = get_option ( 'permalink_structure' );
		if (empty ( $permalink_structure )) {
			$args ['page_id'] = $connected_page;
		}
		if ($include_query_vars) {
			if (get_query_var ( 'search-with' ))
				$args ['search-with'] = get_query_var ( 'search-with' );
			if (get_query_var ( 'search-str' ))
				$args ['search-str'] = get_query_var ( 'search-str' );
			if (get_query_var ( 'paged' ))
				$args ['paged'] = get_query_var ( 'paged' );
            if (get_query_var('board_cat'))
                $args['board_cat'] = get_query_var('board_cat');
            if (get_query_var('sub_cat'))
                $args['sub_cat'] = get_query_var('sub_cat');
		}
		return add_query_arg ( $args, get_permalink ( $connected_page ) );
	}
}
if (! function_exists ( 'hanaboard_get_connected_page' )) {
	function hanaboard_get_connected_page($term_id = null) {
		return apply_filters ( 'hanaboard_connected_page', hanaboard_get_option ( 'connect_page', $term_id ) );
	}
}
if (! function_exists ( 'hanaboard_get_current_term_link' )) {
	function hanaboard_get_current_term_link() {
		return remove_query_arg ( 'article' );
	}
}
if (! function_exists ( 'hanaboard_get_the_term_link' )) {
	function hanaboard_get_the_term_link($term_id = null) {
		if (! $term_id)
			$term_id = hanaboard_get_current_term ()->term_id;
		$connected_page = hanaboard_get_connected_page ( $term_id );
		return get_page_link ( apply_filters ( 'hanaboard_connected_page', $connected_page ) );
	}
}
if (! function_exists ( 'hanaboard_get_term_link' )) {
	function hanaboard_get_term_link($format = '%s') {
		$term_list = wp_get_post_terms ( get_the_ID (), HANA_BOARD_TAXONOMY );
		$link_format = '';
		foreach ( $term_list as $term ) {
			// get_term_link( $term, HANA_BOARD_TAXONOMY );
			$name = sprintf ( $format, trim ( $term->name ) );
			$link_format .= sprintf ( '<a href="%s" class="category_link">%s</a>', hanaboard_get_the_term_link ( $term->term_id ), $name );
		}
		return $link_format;
	}
}
if (! function_exists ( 'hanaboard_get_post_subboard' )) {
    function hanaboard_get_post_subboard($post_id = null) {
        if (!$post_id)
            $post_id = get_the_ID();

        $post = get_post($post_id);
        $current_term = hanaboard_get_current_term();
        if ($post->post_category != $current_term->term_id) {
            $post_term = wp_get_post_terms( $post_id, HANA_BOARD_TAXONOMY);
            if(! is_wp_error($post_term)) {
                return $post_term[0]->name;
            }
            else
                return null;
        } else
            return null;
    }
}
if (! function_exists ( 'hanaboard_has_post_subboard' )) {
    function hanaboard_has_post_subboard($term_id = null) {
        if(! $term_id)
            $term_id = hanaboard_get_current_term_id();
        $children = get_term_children( $term_id, HANA_BOARD_TAXONOMY);
        return (bool)$children;
    }
}
if (! function_exists ( 'hanaboard_get_the_tags' )) {
	function hanaboard_get_the_tags($post_id = null) {
		if (! $post_id)
			$post_id = get_the_ID ();
		
		$tags = array ();
		
		if (get_the_tags ( $post_id )) {
			foreach ( get_the_tags ( $post_id ) as $tag ) {
				$tags [] = apply_filters ( 'hanaboard_format_tags', $tag->name );
			}
		}
		$args ['tags'] = implode ( $tags, ',' );
	}
}
function hanaboard_get_the_post() {
	$post_id = get_the_ID ();
	return get_post ( $post_id );
}
if (! function_exists ( 'hanaboard_get_the_title' )) {
	function hanaboard_get_the_title() {
		return hanaboard_get_the_post ()->post_title;
	}
	function hanaboard_the_title($before = '', $after = '', $echo = true) {
		$title = hanaboard_get_the_title ();
		if (strlen ( $title ) == 0)
			return;
		
		$title = $before . $title . $after;
		
		if ($echo)
			echo $title;
		else
			return $title;
	}
}

if (! function_exists ( 'hanaboard_get_readcount' )) {
	function hanaboard_get_readcount() {
		$views_key = 'readcount'; // The views post meta key
		$readcount = hanaboard_get_post_meta ( get_the_ID (), $views_key, true );
		if (! $readcount)
			$readcount = "0";
		return apply_filters ( 'hanaboard_readcount', $readcount );
	}
}

if (! function_exists ( 'hanaboard_update_readcount' )) {
	function hanaboard_update_readcount() {
		$user_ip = $_SERVER ['REMOTE_ADDR'];
		$post_id = get_the_ID ();
		// Readcount won't be updated when the writer's IP and access IP are
		// same.
		// if ( hanaboard_get_post_meta( $post_id, "writer_ip", true) == $user_ip
		// ) return false;
		
		// Readcount won't be updated when the writer's user id and access user
		// id are same.
		// if ( hanaboard_get_the_author() > 0 && hanaboard_get_the_author() ==
		// get_current_user_id() ) return false;
		
		$default_readcount_update_interval = 120;
		// The user's IP address
		
		$view_meta_key = 'readcount'; // The views post meta key
		$ip_meta_key = 'read_ip'; // The IP Address post meta key
		                          
		// The current post views count
		$readcount = hanaboard_get_post_meta ( $post_id, $view_meta_key, true );
		if (! $readcount)
			$readcount = 0;
			
			// Array of IP addresses that have already visited the post.
		$ip_meta = hanaboard_get_post_meta ( $post_id, $ip_meta_key, true );
		
		/*
		 * The following checks if the user's IP already exists
		 */
		
		$time_limit = hanaboard_get_option ( 'readcount_update_interval' );
		
		if (! is_array ( $ip_meta ) || ! isset ( $ip_meta [$user_ip] )) {
			$ip_meta = array (
					$user_ip => time () 
			);
			$readcount ++;
		}
		foreach ( $ip_meta as $ip => $ip_time ) {
			if (time () - $ip_time > $time_limit) {
				if ($ip != $user_ip) {
					// unset other's ip address if time limit exceed for reduce
					// resource.
					// unset( $ip_meta[$ip] );
				} else {
					$ip_meta [$ip] = time ();
					$readcount ++;
				}
			}
		}
		// Mark time of the ip address
		if (! $ip_meta [$user_ip])
			$ip_meta [$user_ip] = time ();
			// Update and encode the $ip array
		$serialized_ip_meta = serialize ( $ip_meta );
		// Update the post's metadata
		hanaboard_update_post_meta ( $post_id, $view_meta_key, $readcount ); // Update
		                                                                    // the
		                                                                    // count
		hanaboard_update_post_meta ( $post_id, $ip_meta_key, $ip_meta ); // Update
		                                                                // the user IP
		                                                                // JSON obect
		
		return true;
	}
}

if (! function_exists ( 'hanaboard_update_post_meta' )) {
	function hanaboard_update_post_meta($post_id, $key, $vals, $prev_value = null) {
		update_post_meta ( $post_id, HANA_BOARD_POST_META_HEADER . $key, $vals, $prev_value ); // $vals
			                                                                                      // can
			                                                                                      // be
			                                                                                      // either
			                                                                                      // string
			                                                                                      // or
			                                                                                      // array
	}
}

if (! function_exists ( 'hanaboard_show_reply_icon' )) {
	function hanaboard_show_reply_icon($depth = 0, $each_indent_width = 15) {
		if (! $depth)
			return false;
		$indent_width = $each_indent_width * $depth;
		$html = '<span class="hanaboard_reply_indent" style="width: ' . $indent_width . 'px; display:inline-block; text-align:right;">';
		$html .= '<i class="fa fa-angle-right"></i>';
		$html .= '</span>';
		echo $html;
	}
}

if (! function_exists ( 'hanaboard_substr' )) {
	function hanaboard_substr($str, $length = 0, $after = '...') {
		$add_after = '';
		if ($length == 0)
			return $str;
		
		if (mb_strlen ( $str ) > $length) {
			$add_after = $after;
		}
		return mb_substr ( $str, 0, $length, 'UTF-8' ) . $add_after;
	}
}

if (! function_exists ( 'hanaboard_is_show' )) {
	function hanaboard_is_show($key, $default = false) {
		$option = hanaboard_get_option ( 'show_' . $key );
		$current_term = hanaboard_get_current_term ();
		switch ($key) {
			case 'post_no' :
				if ($option)
					return true;
				else
					return false;
				break;
			case 'category' :
				$term_list = wp_get_post_terms ( get_the_ID (), HANA_BOARD_TAXONOMY );
				foreach ( $term_list as $term ) {
					if ($current_term->slug == $term->slug) {
						return ( bool ) false;
					}
				}
				return $default;
				break;
			case 'cat_selectable' :
				if (get_query_var ( 'mode' ) == 'write_reply') {
					return ( bool ) false;
				}
				$option = hanaboard_get_option ( 'cat_selectable' );
				if ($option)
					return true;
				else
					return false;
				break;
			case 'date' :
			case 'list_on_view' :
			case 'thumbnail' :
			case 'like' :
			case 'dislike' :
			case 'author' :
			case 'readcount' :
			case 'featured_image' :
			case 'number_comments' :
				return $option;
				break;
		}
	}
}

if (! function_exists ( 'hanaboard_is_post_notice' )) {
	function hanaboard_is_post_notice($post_id = null) {
		if (! isset ( $post_id )) {
			$post_id = get_the_ID ();
			if (! $post_id)
				return false;
		}
		$post = get_post ( $post_id );
		if (! is_object ( $post )) {
			return false;
		}
		if (hanaboard_get_post_meta ( $post_id, 'is_notice' ) == 'on')
			return true;
		else
			return false;
	}
}
if (! function_exists ( 'hanaboard_is_post_new_item' )) {
	function hanaboard_is_post_new_item($post_id = null) {
		if (! isset ( $post_id )) {
			$post_id = get_the_ID ();
			if (! $post_id)
				return false;
		}
		$post = get_post ( $post_id );
		if (! is_object ( $post )) {
			return false;
		}
		
		$terms = wp_get_post_terms ( $post_id, HANA_BOARD_TAXONOMY );
		if (is_array ( $terms )) {
			$term = $terms [0];
			$term_id = $term->term_id;
		} else {
			return false;
		}
		$duration = hanaboard_get_option ( 'new_item', $term_id ) ? hanaboard_get_option ( 'new_item', $term_id ) : 43200;
		if ($duration > current_time ( 'timestamp', 0 ) - get_the_time ( 'U', $post_id ))
			return true;
		else
			return false;
	}
}

if (! function_exists ( 'hanaboard_is_post_private' )) {
	function hanaboard_is_post_private($post_id = null) {
		if (! isset ( $post_id )) {
			$post_id = get_the_ID ();
		}
		if (is_hanaboard_page ( 'write' ))
			return hanaboard_get_option ( 'default_secret_post' );
		
		$post = get_post ( $post_id );
		if (! is_object ( $post )) {
			return false;
		}
		/*
		 * if ($post->post_author == 0) {
		 * // post_password?
		 * // get_post_meta($post_id, )
		 * if (hanaboard_get_post_meta( $post_id, 'guest_password' ) != '') {
		 * return true;
		 * } else {
		 * return false;
		 * }
		 * } else {
		 */
		if ($post->post_status == "private") {
			return true;
		} else {
			return false;
		}
		// }
	}
}

if (! function_exists ( 'hanaboard_get_parent_post_for_write_reply' )) {
	function hanaboard_get_parent_post_for_write_reply() {
		if (is_hanaboard_page ( 'write_reply' )) {
			return get_post ( get_query_var ( 'article' ) );
		}
		return null;
	}
}

// returns a like count for a post
if (! function_exists ( 'hana_like_get_like_count' )) {
	function hana_like_get_like_count($post_id = null, $type_add = '') {
		if (! $post_id)
			$post_id = get_the_ID ();
		$like_count = get_post_meta ( $post_id, 'hana_' . $type_add . 'likes', true );
		if ($like_count)
			return $like_count;
		return 0;
	}
}
if (! function_exists ( 'hana_like_get_dislike_count' )) {
	function hana_like_get_dislike_count($post_id = null) {
		return hana_like_get_like_count ( $post_id, 'dis' );
	}
}

if (! function_exists ( 'hanaboard_get_skin_custom_field' )) {
	function hanaboard_get_skin_custom_field($meta) {
		return hanaboard_get_post_meta ( get_the_ID (), 'custom_' . $meta, true );
	}
}

if (! function_exists ( 'hanaboard_back_to_list_url' )) {
	function hanaboard_back_to_list_url() {
		$keys = array (
				'article',
				'mode' 
		);
		return remove_query_arg ( $keys );
	}
}

if (! function_exists ( 'hanaboard_url_board_write' )) {
	function hanaboard_url_board_write($slug = null) {
		if (! $slug)
			$slug = hanaboard_get_current_term_slug ();
		return add_query_arg ( 'mode', 'write', hanaboard_get_the_term_link () );
	}
}
