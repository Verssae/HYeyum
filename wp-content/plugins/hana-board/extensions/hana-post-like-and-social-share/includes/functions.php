<?php

function hana_like_get_option($key = '', $default = null) {
	$settings = get_option( 'hana_like' );
	if (FALSE === $settings) {
		return null;
	} elseif (! $key) {
		return $settings;
	} elseif (array_key_exists( $key, $settings )) {
		return $settings [$key];
	} else {
		return $default;
	}

	return false;
}
function hanaLikeAddSecretKey($query){
	$query['secret'] = 'customersapikey';
	$query['installed_version'] = HANA_LIKE_VERSION;
	return $query;
}

function hana_like_get_condition($type) {
	$post = get_post();
	
	$condition = true;
	
	$from_option = ($type == 'dislike') ? 'like' : $type;
	$hana_like_post_type_settings = hana_like_get_option( 'post_types_' . $from_option );
	
	if ($type == 'dislike' && hana_like_get_option( 'like_items' ) == 'like_only')
		return false;
	$post_type = get_post_type( get_the_ID() ) ? get_post_type( get_the_ID() ) : '';
	if (is_array( $hana_like_post_type_settings ) && array_key_exists( $post_type, $hana_like_post_type_settings )) {
		if (get_post_type() == 'hanaboard-post') {
			if( ! get_query_var('article') || get_query_var('mode') )
				return false;
			if (function_exists( 'hanaboard_get_option' )) {
				$board_option = hanaboard_get_option( 'show_' . $type );
				$condition = $condition && ( bool ) $board_option;
				return apply_filters( 'hana_like_condition_filter', $condition );
			}
		} elseif (is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'hanaboard' )) {
			return false;
		} else {
			return apply_filters( 'hana_like_condition_filter', $condition );
		}
	}
	return false;
}
function hana_like_get_enabled_social_share() {
	$enabled_sns = hana_like_get_option( 'sns_enabled' );
	if (! is_array( $enabled_sns ))
		$enabled_sns = array ();
	if (! is_admin() && ! wp_is_mobile()) {
		$enabled_sns = array_diff_key( $enabled_sns, hana_like_get_social_share_mobile_only() );
	}
	return apply_filters( 'hana_like_get_enabled_social_share_filter', $enabled_sns );
}
function hana_like_get_social_share_mobile_only() {
	return array (
			'naver_line'=>'on',
			'kakao_talk'=>'on' 
	);
}
function hana_like_is_social_share_enabled($sns) {
	$enabled_sns = hana_like_get_enabled_social_share();
	foreach ( $enabled_sns as $enabled_sns => $enabled ) {
		if ($enabled_sns == $sns)
			return true;
	}
	return false;
}
if (! function_exists( 'hana_like_skin_path' )) {
	function hana_like_skin_path($type = 'social_share', $is_dir = false) {
		if ($type != 'like' && $type != 'social_share')
			return;
		
		if ($is_dir) {
			$path = HANA_LIKE_BASE_DIR . 'skins/';
		} else {
			$path = HANA_LIKE_BASE_URL . 'skins/';
		}
		$path .= $type . '/';
		
		$skin = hana_like_get_option( 'skin_' . $type );
		if (! $skin)
			$skin = 'default';
		$path .= $skin . '/';
		
		return $path;
	}
}
function hana_like_skin_icon($sns_item, $type = 'social_share') {
	return hana_like_skin_path( 'social_share' ) . 'images/' . $sns_item . '.png';
}

if (! function_exists( 'hana_like_skin_dir' )) {
	function hana_like_skin_dir($type = 'like') {
		return hana_like_skin_path( $type, true );
	}
}

if (wp_is_mobile()) {
}
function hana_like_get_social_shar_mobile_only() {
	return array (
			'kakaotalk' 
	);
}
function hana_like_get_the_excerpt($charlength = 200) {
	$post = get_post();
	$excerpt = trim( wp_strip_all_tags( $post->post_content, true ) );
	$charlength ++;
	
	if (mb_strlen( $excerpt ) > $charlength) {
		$subex = mb_substr( $excerpt, 0, $charlength - 5 );
		$exwords = explode( ' ', $subex );
		$excut = - (mb_strlen( $exwords [count( $exwords ) - 1] ));
		if ($excut < 0) {
			$excerpt = mb_substr( $subex, 0, $excut );
		} else {
			$excerpt = $subex;
		}
		$excerpt = ' ...';
	}
	return $excerpt;
}
