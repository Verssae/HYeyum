<?php
if (! function_exists( 'hana_like_ajax_process_like' )) {
	add_action( 'wp_ajax_hana_like_ajax_action', 'hana_like_ajax_process_like' );
	add_action( 'wp_ajax_nopriv_hana_like_ajax_action', 'hana_like_ajax_process_like' );
	// processes the ajax request
	function hana_like_ajax_process_like() {
		$post = get_post( $_POST ['item_id'] );
		$res = array ();
		if (isset( $_POST ['item_id'] ) && wp_verify_nonce( $_POST ['nonce'], 'hana-like-nonce' )) {
			if (! is_user_logged_in())
				$res ['error'] = 'login_required';
			else if (hana_like_user_has_liked_post( $_POST ['user_id'], $_POST ['item_id'], '' ))
				$res ['error'] = 'already_liked';
			else if (hana_like_user_has_liked_post( $_POST ['user_id'], $_POST ['item_id'], 'dis' ))
				$res ['error'] = 'already_disliked';
			else if (hana_like_mark_post_as_liked( $_POST ['item_id'], $_POST ['user_id'], $_POST ['item_type'] ))
				$res ['error'] = 'success';
			else if (get_current_user_id() == $post->post_author)
				$res ['error'] = 'your_post';
			else
				$res ['error'] = 'failed';
		} else {
			$res ['error'] = 404;
		}
		echo json_encode( $res );
		die();
	}
}

// check whether a user has liked an item
function hana_like_user_has_liked_post($user_id, $post_id, $type_add = '') {
	
	// get all item IDs the user has liked
	$liked = get_user_option( 'hana_like_user_' . $type_add . 'likes', $user_id );
	if (is_array( $liked ) && in_array( $post_id, $liked )) {
		return true; // user has liked post
	}
	return false; // user has not liked post
}

// adds the liked ID to the users meta so they can't like it again
function hana_like_store_liked_id_for_user($user_id, $post_id, $type_add = '') {
	$liked = get_user_option( 'hana_like_user_' . $type_add . 'likes', $user_id );
	if (is_array( $liked )) {
		$liked [] = $post_id;
	} else {
		$liked = array (
				$post_id 
		);
	}
	update_user_option( $user_id, 'hana_like_user_' . $type_add . 'likes', $liked );
}

// increments a like count
function hana_like_mark_post_as_liked($post_id, $user_id, $type_add = '') {
	
	// retrieve the like count for $post_id
	$like_count = get_post_meta( $post_id, 'hana_' . $type_add . 'likes', true );
	if ($like_count)
		$like_count = $like_count + 1;
	else
		$like_count = 1;
	
	if (update_post_meta( $post_id, 'hana_' . $type_add . 'likes', $like_count )) {
		// store this post as liked for $user_id
		hana_like_store_liked_id_for_user( $user_id, $post_id, $type_add );
		return true;
	}
	return false;
}

// returns a like count for a post
if (! function_exists( 'hana_like_get_like_count' )) {
	function hana_like_get_like_count($post_id = null, $type_add = '') {
		if (! $post_id)
			$post_id = get_the_ID();
		$like_count = get_post_meta( $post_id, 'hana_' . $type_add . 'likes', true );
		if ($like_count)
			return $like_count;
		return 0;
	}
}
if (! function_exists( 'hana_like_get_dislike_count' )) {
	function hana_like_get_dislike_count($post_id = null) {
		return hana_like_get_like_count( $post_id, 'dis' );
	}
}
