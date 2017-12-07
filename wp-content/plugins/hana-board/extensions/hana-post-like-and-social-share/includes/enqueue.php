<?php
function hana_like_enqueue() {
	// if (is_user_logged_in()) {
	$hana_like_base_url = HANA_LIKE_BASE_URL;
	wp_enqueue_script( 'kakao-sdk', '//developers.kakao.com/sdk/js/kakao.min.js' );
	wp_enqueue_script( 'hana_like', $hana_like_base_url . 'includes/js/hana-like.js', array (
			'jquery' 
	), HANA_LIKE_VERSION );
	wp_localize_script( 'hana_like', 'hana_like', array (
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'hana-like-nonce' ),
			'login_link' => wp_login_url( get_permalink() ),
			'api_key' => array (
					'kakao' => hana_like_get_option( 'api_key_kakao' ) 
			),
			'home_url' => get_home_url(),
			'enabled_social_share' => hana_like_get_enabled_social_share(),
			'message' => array (
					'blog_info_name' => get_bloginfo( 'name' ),
					'blog_info_description' => get_bloginfo( 'description' ),
					'already_liked' => __( 'You have already liked this item.', HANA_LIKE_TEXT_DOMAIN ),
					'already_disliked' => __( 'You have already disliked this item.', HANA_LIKE_TEXT_DOMAIN ),
					'your_post' => __( 'You cannot like your post.', HANA_LIKE_TEXT_DOMAIN ),
					'no_permission' => __( 'User log in required.', HANA_LIKE_TEXT_DOMAIN ),
					'error' => __( 'Sorry, there was a problem processing your request.', HANA_LIKE_TEXT_DOMAIN ) 
			) 
	) );
	
	wp_enqueue_style( 'hana_like_style', hana_like_skin_path( 'like' ) . 'css/hana-like.css', array(), HANA_LIKE_VERSION );
	wp_enqueue_style( 'hana_like_social_share', hana_like_skin_path( 'social_share' ) . 'css/hana-social-share.css', array(), HANA_LIKE_VERSION );
	wp_enqueue_style( 'fontawesome-45', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );
}
add_action( 'wp_enqueue_scripts', 'hana_like_enqueue' );