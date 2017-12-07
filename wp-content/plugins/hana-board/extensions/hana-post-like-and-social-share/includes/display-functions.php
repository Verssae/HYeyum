<?php
add_filter('the_content', 'hana_like_display_like_link', 50, 1);
// adds the Like link and count to post/page content automatically
function hana_like_display_like_link ($content) {
	$like_text = hana_like_get_option('like_text') ? hana_like_get_option('like_text') : null;
	$liked_text = hana_like_get_option('liked_text') ? hana_like_get_option('liked_text') : null;
	$dislike_text = hana_like_get_option('dislike_text') ? hana_like_get_option('dislike_text') : null;
	$disliked_text = hana_like_get_option('disliked_text') ? hana_like_get_option('disliked_text') : null;
	if (hana_like_get_condition('like'))
		$content .= hana_like_get_like_link($like_text, $liked_text, $dislike_text, $disliked_text);
	
	return $content;
}

add_filter('the_content', 'hana_like_display_social_share_link', 60, 1);
// adds the Social Share to post/page content automatically
function hana_like_display_social_share_link ($content) {
	if (hana_like_get_condition('social_share'))
		$content .= hana_like_get_social_share_link();
	
	return $content;
}

// outputs the like it link
function hana_like_get_like_link ($like_text = null, $liked_text = null, $dislike_text = null, $disliked_text = null) {
	global $post;
	
	$user_ID = get_current_user_id() ? get_current_user_id() : '0';
	$post_id = get_the_ID();
	// retrieve the total like count for this item
	$like_count = hana_like_get_like_count($post_id);
	$dislike_count = hana_like_get_dislike_count($post_id);
	$like_text = ($like_text) ? $like_text : __('Like', 'hana-like');
	$liked_text = ($liked_text) ? $liked_text : __('Liked', 'hana-like');

	$dislike_text = is_null($dislike_text) ? $dislike_text : __('Dislike', 'hana-like');
	$disliked_text = is_null($disliked_text) ? $disliked_text : __('Disliked', 'hana-like') ;
	
	// only show the Like It link if the user has NOT previously liked this item
	if (hana_like_user_has_liked_post($user_ID, $post_id) && $user_ID > 0) {
		$like_class = "hana-like-liked-link";
		$like_icon_class = "fa fa-thumbs-o-up";
	} else {
		$like_class = "hana-like-like-link";
		$like_icon_class = "fa fa-thumbs-o-up";
	}
	if (hana_like_user_has_liked_post($user_ID, $post_id, 'dis') && $user_ID > 0) {
		$dislike_class = "hana-like-disliked-link";
		$dislike_icon_class = "fa fa-thumbs-o-down";
	} else {
		$dislike_class = "hana-like-dislike-link";
		$dislike_icon_class = "fa fa-thumbs-o-down";
	}
	
	// our wrapper DIV
	$html = '<div class="hana-like-post-like-wrapper ">';
	// show a message to users who have already liked this item
	$html .= '<a name="#hana-like" href="javascript:;;" data-type="" class="hana-like-action ' . $like_class . '" data-post-id="' . esc_attr($post_id) . '" data-user-id="' . esc_attr($user_ID) . '"> <i class="' . $like_icon_class . '" id="like-icon"></i> <span class="like-count">' . $like_count . '</span><br /><span class="like_text">' . $like_text . '</span></a>';
	
	if (hana_like_get_condition('dislike'))
		$html .= '<a href="javascript:;;" data-type="dis" class="hana-like-action ' . $dislike_class . '" data-post-id="' . esc_attr($post_id) . '" data-user-id="' . esc_attr($user_ID) . '"> <i class="' . $dislike_icon_class . '" id="dislike-icon"></i> <span class="dislike-count">' . $dislike_count . '</span><br /><span class="dislike_text">' . $dislike_text . '</span></a>';
		
		// close our wrapper DIV
	$html .= '</div>';
	
	return $html;
}

function hana_like_get_social_share_link () {
	$enabled_sns = hana_like_get_enabled_social_share();
	$title = hana_like_get_option('title_social_share') != '' ? hana_like_get_option('title_social_share') : __('Social Share', HANA_LIKE_TEXT_DOMAIN);
	if (is_array($enabled_sns) && sizeof($enabled_sns) > 0) {
		$html = '<div class="hana-like-social-share-wrapper">';
		$html .= '<div class="hana-like-social-share-title">'.$title.'</div>';
		foreach ($enabled_sns as $sns => $enabled) {
			$html .= hana_like_get_sns_link($sns);
		}
		$html .= '</div>';
	}
	return $html;
}

function hana_like_get_sns_link ($sns_item) {
	if ($link == '' || $title == '') {
		$link = get_permalink();
		$title = get_the_title();
	}
	
	$title = strip_tags($title);
	$title = str_replace("\"", " ", $title);
	$title = str_replace("&#039;", "", $title);
	
	$eLink = urlencode($link);
	$eTitle = urlencode($title . " - " . $siteTitle);
	
	$siteTitle = get_bloginfo('name');
	$siteTitle = strip_tags($siteTitle);
	$siteTitle = str_replace("\"", " ", $siteTitle);
	$siteTitle = str_replace("&#039;", "", $siteTitle);
	$thumb = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'thumbnail_size');
	$thumb_url = $thumb[0];
	$data_array = array(
			'sns' => $sns_item,
			'post-id' => $post_id,
			'title' => $title,
			'site-title' => $siteTitle,
			'excerpt' => hana_like_get_the_excerpt(),
			'image-src' => $thumb_url,
			'image-width' => 600,
			'image-height' => 400,
			'url' => get_the_permalink()
	);
	$data_html = '';
	foreach ($data_array as $k => $v) {
		$data_html .= 'data-' . $k . '="' . $v . '" ';
	}
	$class_icon_style = hana_like_get_option('icon_style_social_share');
	$html = '<a href="javascript:;;" class="hana-like-social-share-button '.$class_icon_style.' hana-like-social-share-button-' . $sns_item . '" ' . $data_html . '><img src="' . hana_like_skin_icon($sns_item) . '" /></a>';
	
	return $html;
}
