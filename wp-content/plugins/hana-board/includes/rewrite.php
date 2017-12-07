<?php
if (! function_exists( 'hanaboard_add_query_arg' )) {
	function hanaboard_add_query_arg() {
		$args = func_get_args();
		$uri = call_user_func_array( 'add_query_arg', $args );
		return $uri;
		
		if (! get_option( 'permalink_structure' )) {
		} else {
			if (is_array( $args [0] )) {
				if (count( $args ) < 2 || false === $args [1])
					$uri = add_query_arg( array () );
				else
					$uri = $args [1];
			} else {
				if (count( $args ) < 3 || false === $args [2])
					$uri = $_SERVER ['REQUEST_URI'];
				else
					$uri = $args [2];
			}
			
			if ($frag = strstr( $uri, '#' ))
				$uri = substr( $uri, 0, - strlen( $frag ) );
			else
				$frag = '';
			
			if (0 === stripos( $uri, 'http://' )) {
				$protocol = 'http://';
				$uri = substr( $uri, 7 );
			} else if (0 === stripos( $uri, 'https://' )) {
				$protocol = 'https://';
				$uri = substr( $uri, 8 );
			} else {
				$protocol = '';
			}
			
			if (strpos( $uri, '?' ) !== false) {
				list ( $base, $query ) = explode( '?', $uri, 2 );
				$base .= '?';
			} else if ($protocol || strpos( $uri, '=' ) === false) {
				$base = $uri . '?';
				$query = '';
			} else {
				$base = '';
				$query = $uri;
			}
			
			wp_parse_str( $query, $qs );
			if (is_array( $args [0] )) {
				$kayvees = $args [0];
				$qs = array_merge( $qs, $kayvees );
			} else {
				$qs [$args [0]] = $args [1];
			}
			
			foreach ( $qs as $k => $v ) {
				if ($v === false)
					unset( $qs [$k] );
				if (in_array( $k, $hanaboard_keys )) {
					$hanaboard_add_pretty_url [] = hanaboard_rewrite_formatter( $k, $v );
					unset( $qs [$k] );
				}
			}
			$hanaboard_add_pretty_url = join( "/", $hanaboard_add_pretty_url );
			$add_slash = '';
			if (strpos( $uri, '?' ) !== false) {
				if (strpos( $uri, '/?' ) === false)
					$add_slash = '/';
				$uri = str_replace( '?', $add_slash . $hanaboard_add_pretty_url . '/?', $uri );
				$uri = user_trailingslashit( $uri );
				$uri = add_query_arg( $qs, $protocol . $uri );
			} else {
				$uri = user_trailingslashit( $protocol . $uri );
				if (substr( $uri, - 1 ) != '/')
					$add_slash = '/';
				$uri = $uri . $add_slash . $hanaboard_add_pretty_url;
			}
			$uri = user_trailingslashit( urldecode( $uri ) );
		}
		return $uri;
	}
}

if (! function_exists( 'hanaboard_rewrite_formatter' )) {
	function hanaboard_rewrite_formatter($key, $val) {
		switch ($key) {
			case 'search-with' :
			case 'search-str' :
				$str = $key . '-' . $val;
				break;
			case HANA_BOARD_QUERY_VAR_MODE :
				if ($val == 'write_reply')
					$str = 'reply';
				else
					$str = $val;
				break;
			case 'post' :
			case HANA_BOARD_TAXONOMY :
			case HANA_BOARD_POST_TYPE . '-parent' :
				$str = $val;
				break;
		}
		return $str;
	}
}

add_filter( 'query_vars', 'hanaboard_add_query_vars' );
function hanaboard_add_query_vars($myVars) {
	$myVars [] = "article";
	$myVars [] = HANA_BOARD_TAXONOMY;
	$myVars [] = HANA_BOARD_POST_TYPE;
	$myVars [] = "post";
	$myVars [] = "mode";
	$myVars [] = HANA_BOARD_POST_TYPE . '-parent';
	$myVars [] = "search-with";
	$myVars [] = "search-str";
    $myVars [] = 'board_cat';
    $myVars [] = 'sub_cat';
	
	return $myVars;
}

add_action( 'init', 'hanaboard_register_post_types' );
function hanaboard_register_post_types() {
	$args = array (
			'labels' => array (
					'name' => __( 'Board Posts', HANA_BOARD_TEXT_DOMAIN ),
					'singular_name' => __( 'Board Posts', HANA_BOARD_TEXT_DOMAIN ),
					'add_new' => __( 'Add New', HANA_BOARD_TEXT_DOMAIN ),
					'add_new_item' => __( 'Add New Post', HANA_BOARD_TEXT_DOMAIN ),
					'edit' => __( 'Edit', HANA_BOARD_TEXT_DOMAIN ),
					'edit_item' => __( 'Edit Post', HANA_BOARD_TEXT_DOMAIN ),
					'new_item' => __( 'New Post', HANA_BOARD_TEXT_DOMAIN ),
					'view' => __( 'View Post', HANA_BOARD_TEXT_DOMAIN ),
					'view_item' => __( 'View Post', HANA_BOARD_TEXT_DOMAIN ),
					'search_items' => __( 'Search Posts', HANA_BOARD_TEXT_DOMAIN ),
					'not_found' => __( 'No Posts found', HANA_BOARD_TEXT_DOMAIN ),
					'not_found_in_trash' => __( 'No Posts found in Trash', HANA_BOARD_TEXT_DOMAIN ),
					'parent' => __( 'Parent Post', HANA_BOARD_TEXT_DOMAIN ) 
			),
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'has_archive' => true,
			'rewrite' => array (
					'with_front' => false 
			),
			'rewrite' => false,
			'show_in_menu' => false,
			'show_ui' => false,
			'query_var' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array (
					'thumbnail',
					'title',
					'taxonomy',
					'author',
					'date',
					'comment',
					'editor',
					'exerpt' 
			),
			'taxonomies' => array (
					HANA_BOARD_TAXONOMY 
			),
			'capability_type' => 'post',
			'capabilities' => array (
					'edit_post' => 'edit_post',
					'read_post' => 'read_post',
					'list' => 'list_' . HANA_BOARD_POST_TYPE,
					'read' => 'read_' . HANA_BOARD_POST_TYPE,
					'publish_posts' => 'publish_' . HANA_BOARD_POST_TYPE,
					'write_reply' => 'write_reply_' . HANA_BOARD_POST_TYPE,
					'edit_posts' => 'edit_' . HANA_BOARD_POST_TYPE,
					'delete_posts' => 'delete_' . HANA_BOARD_POST_TYPE,
					'moderate_comments' => 'comments_' . HANA_BOARD_POST_TYPE,
					'edit_others_posts' => 'edit_others_' . HANA_BOARD_POST_TYPE,
					'delete_others_posts' => 'delete_others_' . HANA_BOARD_POST_TYPE,
					'read_private_posts' => 'read_private_' . HANA_BOARD_POST_TYPE,
					'edit_private_posts' => 'edit_private_' . HANA_BOARD_POST_TYPE,
					'delete_private_posts' => 'delete_private_' . HANA_BOARD_POST_TYPE 
			) 
	);
	register_post_type( HANA_BOARD_POST_TYPE, $args );
	$args = array (
			'hierarchical' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_ui' => false,
			'menu_icon' => '\f119',
			'label' => __( 'Board Category', HANA_BOARD_TEXT_DOMAIN ),
			'query_var' => HANA_BOARD_TAXONOMY,
			'rewrite' => array (
					'slug' => 'board',
					'with_front' => false,
					'hierarchical' => false 
			),
			'singular_label' => __( 'Board Category', HANA_BOARD_TEXT_DOMAIN ) 
	);
	register_taxonomy( HANA_BOARD_TAXONOMY, Array (
			HANA_BOARD_POST_TYPE 
	), $args );
}
// permalink structure////
// add_filter( 'init', 'hanaboard_page_rewrite_rules' );
function hanaboard_page_rewrite_rules() {
	$new_rewrite_rules = array (
			'(.+?)/([0-9]+)/?$' => 'index.php?pagename=$matches[1]&article=$matches[2]',
			'(.+?)/([0-9]+)/page/([0-9]+)/?$' => 'index.php?pagename=$matches[1]&article=$matches[2]&paged=$matches[3]',
			'(.+?)/([0-9]+)/edit/?$' => 'index.php?pagename=$matches[1]&article=$matches[2]&mode=edit',
			'(.+?)/([0-9]+)/edit/page/([0-9]+)/?$' => 'index.php?pagename=$matches[1]&article=$matches[2]&mode=edit&paged=$matches[3]',
			'(.+?)/write/?$' => 'index.php?pagename=$matches[1]&article=$matches[2]&fname=$matches[3]&lname=$matches[4]' 
	);
	// '(.+?)/([0-9]+)/write_reply/([^/]+)/?$' =>
	// 'index.php?pagename=$matches[1]&hanaboard-post-parent=$matches[2]&mode=write_reply'
	
	foreach ( $new_rewrite_rules as $k => $v ) {
		add_rewrite_rule( $k, $v, 'top' );
	}
}
/*
add_filter( 'post_link', 'hanaboard_custom_post_type_link', 10, 2 );
add_filter( 'post_type_link', 'hanaboard_custom_post_type_link', 10, 2 );
function hanaboard_custom_post_type_link($permalink, $post) {
	if ($post->post_type == HANA_BOARD_POST_TYPE)
		return hanaboard_get_the_permalink( $post->ID );
}
*/