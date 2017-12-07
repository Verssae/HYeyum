jQuery(document).ready(function($) {
	jQuery('.hana-like-action').on('click', function() {
		var $this = jQuery(this);
		var post_id = $this.data('postId');
		var user_id = $this.data('userId');
		var type_add = $this.data('type');
		var post_data = {
		    action : 'hana_like_ajax_action',
		    nonce : hana_like.nonce,
		    item_id : post_id,
		    user_id : user_id,
		    item_type : type_add
		};

		$.ajax({
		    type : "POST",
		    url : hana_like.ajaxurl,
		    data : post_data,
		    success : function(response) {
			    if (response.error == 'success') {
				    if (type_add == 'dis') {
					    $this.removeClass('hana-like-dislike-link');
					    $this.addClass('hana-like-disliked-link');
					    jQuery(".dislike-count").text((jQuery(".dislike-count").text() * 1) + 1);

				    } else {
					    $this.removeClass('hana-like-like-link');
					    $this.addClass('hana-like-liked-link');
					    jQuery(".like-count").text((jQuery(".like-count").text() * 1) + 1);
				    }
			    } else if (response.error == 'login_required') {
				    if (confirm(hana_like.message.no_permission)) {
					    location.href = hana_like.login_link;
				    }
			    } else if (response.error == 'already_liked') {
				    alert(hana_like.message.already_liked);
			    } else if (response.error == 'already_disliked') {
				    alert(hana_like.message.already_disliked);
			    } else if (response.error == 'your_post') {
				    alert(hana_like.message.your_post);
			    } else {
				    alert(hana_like.message.error);
			    }
		    },
		    dataType : 'json'
		});

	});
	if (typeof hana_like.api_key.kakao != 'undefined' && 'kakao_talk' in hana_like.enabled_social_share || 'kakaostory' in hana_like.enabled_social_share) {
		// KAKAO TALK
		Kakao.init(hana_like.api_key.kakao);
		if ('kakao_talk' in hana_like.enabled_social_share && $(window).width() < 768) {
			var kakao_talk_link_selector = '.hana-like-social-share-button-kakao_talk';

			Kakao.Link.createTalkLinkButton({
			    container : kakao_talk_link_selector,
			    image : {
			        src : $(kakao_talk_link_selector).data('imageSrc'),
			        width : $(kakao_talk_link_selector).data('imageWidth'),
			        height : $(kakao_talk_link_selector).data('imageHeight')
			    },
			    webButton : {
			        text : $(kakao_talk_link_selector).data('title'),
			        url : hana_like.home_url
			    },
			    appButton : {
				    text : $(kakao_talk_link_selector).data('title')
			    }
			});
		}
		if ('kakao_story' in hana_like.enabled_social_share) {
			var kakao_story_link_selector = '.hana-like-social-share-button-kakao_story';
			$(kakao_story_link_selector).on('click', function(e) {
				Kakao.Story.share({
				    url : $(this).data('url'),
				    text : $(this).data('excerpt')
				});
			});
		}
	}
	if ('naver_line' in hana_like.enabled_social_share) {
		var naver_line_link_selector = '.hana-like-social-share-button-naver_line';
		//var naver_line_url = "http://line.naver.jp/R/msg/text/?" + $(naver_line_link_selector).data('title') + '%0D%0A' + $(naver_line_link_selector).data('url') + '"';
		$(naver_line_link_selector).on('click', function(e) {
			var url = 'http://line.me/R/msg/text/?'+$(this).data('title')+'%0D%0A'+$(this).data('url');
			document.location.href = url;
		});
	}
	if ('naver_band' in hana_like.enabled_social_share) {
		var naver_band_link_selector = '.hana-like-social-share-button-naver_band';
		$(naver_band_link_selector).on('click', function(e) {
			if (hana_like.is_mobile) {
				location.href = 'bandapp://create/post?text=' + $(this).data('title') + '&route=' + $(this).data('url');
			} else {
				window.open('http://band.us/plugin/share?body=' + $(this).data('title') + '&route=' + $(this).data('url'), "share_band", "width=410, height=540, resizable=no");
			}
		});
		// document.location.href=\'http://line.naver.jp/R/msg/text/?' . $eTitle
		// . '%0D%0A' . $eLink . '\'';
	}
	if ('naver_blog' in hana_like.enabled_social_share) {
		var naver_blog_link_selector = '.hana-like-social-share-button-naver_blog';
		$(naver_blog_link_selector).on('click', function(e) {
			SendSNS('naver_blog', $(this).data('title'), $(this).data('url'), $(this).data('imageSrc'));
		});
	}
	if ('facebook' in hana_like.enabled_social_share) {
		var facebook_link_selector = '.hana-like-social-share-button-facebook';
		$(facebook_link_selector).on('click', function(e) {
			SendSNS('facebook', $(this).data('title'), $(this).data('url'), $(this).data('imageSrc'));
		});
	}
	if ('twitter' in hana_like.enabled_social_share) {
		var  twitter_link_selector = '.hana-like-social-share-button-twitter';
		$(twitter_link_selector).on('click', function(e) {
			SendSNS('twitter', $(this).data('title'), $(this).data('url'), $(this).data('imageSrc'));
		});
	}
	if ('google' in hana_like.enabled_social_share) {
		var google_link_selector = '.hana-like-social-share-button-google';
		$(google_link_selector).on('click', function(e) {
			SendSNS('google', $(this).data('title'), $(this).data('url'), $(this).data('imageSrc'));
		});	}
});

var g_bInitKakao = false;

function SendSNS(sns, title, url, image) {
	var o;
	var _url = encodeURIComponent(url);
	var _title = encodeURIComponent(title);
	var _br = encodeURIComponent('\r\n');

	switch (sns) {
		case 'facebook':
			o = {
			    method : 'popup',
			    height : 600,
			    width : 600,
			    url : 'http://www.facebook.com/sharer/sharer.php?u=' + _url
			};
			break;

		case 'twitter':
			o = {
			    method : 'popup',
			    height : 600,
			    width : 600,
			    url : 'http://twitter.com/intent/tweet?text=' + _title + '&url=' + _url
			};
			break;

		case 'google':
			o = {
			    method : 'popup',
			    height : 600,
			    width : 600,
			    url : 'https://plus.google.com/share?url={' + _url + '}'
			};
			break;

		case 'naverband':
			o = {
			    method : 'web2app',
			    param : 'create/post?text=' + _title + _br + _url,
			    a_store : 'itms-apps://itunes.apple.com/app/id542613198?mt=8',
			    g_store : 'market://details?id=com.nhn.android.band',
			    a_proto : 'bandapp://',
			    g_proto : 'scheme=bandapp;package=com.nhn.android.band'
			};
			break;

		case 'naverblog':
			o = {
			    method : 'popup',
			    height : 600,
			    width : 600,
			    url : 'http://blog.naver.com/openapi/share?url=' + _url + '&title=' + _title
			};
			break;

		default:
			return false;
	}

	switch (o.method) {
		case 'popup':
			if (o.height > 0 && o.width > 0) {
				window.open(o.url, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=' + o.height + ',width=' + o.width);
			} else {
				window.open(o.url);
			}

			break;

		case 'web2app':
			if (navigator.userAgent.match(/android/i)) {
				setTimeout(function() {
					location.href = 'intent://' + o.param + '#Intent;' + o.g_proto + ';end'
				}, 100);
			} else if (navigator.userAgent.match(/(iphone)|(ipod)|(ipad)/i)) {
				setTimeout(function() {
					location.href = o.a_store;
				}, 200);
				setTimeout(function() {
					location.href = o.a_proto + o.param
				}, 100);
			} else {
				alert('Only mobile');
			}
			break;
	}
}
