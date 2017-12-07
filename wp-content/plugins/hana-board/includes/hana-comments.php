<?php
add_action ( 'wp_enqueue_scripts', 'hana_comments_enqueue', 999 );
function hana_comments_enqueue() {
	// if ( is_singular() AND comments_open() AND (get_option('thread_comments')
	// == 1)) {
	if (get_query_var ( 'article' )) {
		wp_enqueue_script ( 'comment-reply' );
		wp_enqueue_script ( 'hana_comment', hanaboard_plugins_url ( 'js/hana_comments.js' ), array (
				'jquery',
				'comment-reply' 
		), '0.0.2', true );
		wp_localize_script ( 'hana_comment', 'hana_comment', array (
				'nonce' => wp_create_nonce ( 'hana_delete_comment' ),
				'msg' => array (
						'confirm' => __ ( 'Are you sure to delete this comment?', HANA_BOARD_TEXT_DOMAIN ),
						'error_have_children' => __ ( 'You cannot delete comment which have children comments.', HANA_BOARD_TEXT_DOMAIN ),
						'error_permission' => __ ( 'No Permission', HANA_BOARD_TEXT_DOMAIN ) 
				),
				'ajaxurl' => admin_url ( 'admin-ajax.php' ) . '?action=hana_delete_comment&nonce=' . wp_create_nonce ( 'hana_delete_comment' ) 
		) );
	}
}

function my_wpdiscuz_shortcode() {
	if(file_exists(ABSPATH . 'wp-content/plugins/wpdiscuz/templates/comment/comment-form.php')){
		include_once ABSPATH . 'wp-content/plugins/wpdiscuz/templates/comment/comment-form.php';
	}
}

add_shortcode( 'wpdiscuz_comments', 'my_wpdiscuz_shortcode' );

if (! class_exists('WpdiscuzCore')) {
	//add_filter('comments_template', 'hana_comments_template', 10);
	function hana_comments_template($comment_template)
	{
		global $post;
		// global $comment_form_flag;
		// if ($comment_form_flag)
		// return false;
		if (get_query_var('article')) { // assuming there is a post type called
			// business
			if (hanaboard_get_option('allow_comments')) {
				$comment_form_flag = true;
				return dirname(__FILE__) . '/comments.php';
			}
		}
		return $comment_template;
	}
}
// add_filter( 'comments_open', 'hana_comments_close', 10, 2 );
function hana_comments_close($open, $post_id) {
	$post = get_post ( $post_id );
	
	if (HANA_BOARD_POST_TYPE == $post->post_type)
		$open = false;
	
	return $open;
}
/**
 * *********** COMMENT LAYOUT ********************
 */
if (! function_exists ( 'hana_custom_comments' )) {
	
	/**
	 * Display customized comments
	 *
	 * @param object $comment        	
	 * @param array $args        	
	 * @param integer $depth        	
	 */
	function hana_custom_comments($comment, $args, $depth) {
		$GLOBALS ['comment'] = $comment;
		$GLOBALS ['comment_depth'] = $depth;
		$comment_author_id = get_comment ( $comment_id )->user_id;
		
		?>
<li id="comment-<?php comment_ID() ?>"
	<?php comment_class('clearfix') ?>>
	<div class="comment-wrap clearfix">
		<div class="comment-avatar hana-rounded">
                <?php
		
		if (function_exists ( 'get_avatar' )) {
			echo get_avatar ( $comment, '100' );
		}
		?>
                <?php if ($comment->comment_author_email == get_the_author_meta('email')) { ?>
                    <span class="tooltip"><?php _e("Author", "hana-board"); ?><span
				class="arrow"></span> </span>
                <?php } ?>
            </div>
		<div class="comment-content">
			<div class="comment-meta">
				<span class="comment-author">
					<?php
		if ($comment_author_id > 0) {
			$author_name = get_the_author_meta ( 'display_name', $comment_author_id );
			if (function_exists ( 'mycred_get_users_rank' ))
				$author_name = mycred_get_users_rank ( $comment_author_id, 'logo' ) . ' ' . $author_name;
			$author_link = '<a href="' . get_author_posts_url ( $comment_author_id ) . '">' . $author_name . '</a>';
			$author_link = apply_filters ( 'the_author', $author_link );
			echo $author_link;
		} else {
			echo '<span class="guest_author">' . $comment->comment_author . '</span>';
		}
		?>
				</span> <span class="comment-date">
					<?php echo human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( "ago", HANA_BOARD_TEXT_DOMAIN ); ?>
				</span>
			</div>
			<div class="comment-body">
 
                <?php
		if ($comment->comment_approved == '0') {
			_e ( "<span class='unapproved'>Your comment is awaiting moderation.</span>\n", HANA_BOARD_TEXT_DOMAIN );
		} else {
			comment_text ();
		}
		?>
                </div>
			<div class="comment-meta-actions">
                    <?php
		edit_comment_link ( __ ( 'Edit', HANA_BOARD_TEXT_DOMAIN ), '<span class="edit-link">', '</span><span class="meta-sep">&nbsp;&nbsp;&nbsp;</span>' );
		
		hana_delete_comment_link ( __ ( 'Delete', HANA_BOARD_TEXT_DOMAIN ), '<span class="delete-link">', '</span><span class="meta-sep">&nbsp;&nbsp;&nbsp;</span>' );
		?>

                    <?php
		
		if ($args ['type'] == 'all' || get_comment_type () == 'comment') :
			comment_reply_link ( array_merge ( $args, array (
					'reply_text' => __ ( 'Reply', HANA_BOARD_TEXT_DOMAIN ),
					'login_text' => __ ( 'Log in to reply.', HANA_BOARD_TEXT_DOMAIN ),
					'depth' => $depth,
					'before' => '<span class="comment-reply">',
					'after' => '</span>' 
			) ) );
		
		
		endif;
		?>
                </div>
		</div>
	</div>
    <?php
	}
} // end hana_custom_comments

if (! function_exists ( 'hana_delete_comment_link' )) {
	
	/**
	 * Display edit comment link with formatting.
	 *
	 * @since 0.1
	 * @global object $comment
	 * @param string $text
	 *        	Optional. Anchor text.
	 * @param string $before
	 *        	Optional. Display before edit link.
	 * @param string $after
	 *        	Optional. Display after edit link.
	 */
	function hana_delete_comment_link($text = null, $before = '', $after = '') {
		global $comment;
		if (get_current_user_id () == 0 || (( int ) $comment->user_id) != get_current_user_id () && ! (current_user_can ( 'editor' ) || current_user_can ( 'administrator' ))) {
			return;
		}
		$parent = get_comment ( $comment->ID );
		
		if (null === $text) {
			$text = __ ( 'Delete' );
		}
		
		$link = '<a class="hana-comment-delete-link" data-comment-id="' . $comment->comment_ID . '" href="#">' . $text . '</a>';
		/**
		 * Filter the comment edit link anchor tag.
		 *
		 * @since 0.1
		 * @param string $link
		 *        	Anchor tag for the edit link.
		 * @param int $comment_id
		 *        	Comment ID.
		 * @param string $text
		 *        	Anchor text.
		 */
		echo $before . apply_filters ( 'hana_delete_comment_link', $link, $comment->comment_ID, $text ) . $after;
	}
}
if (! function_exists ( 'hana_ajax_delete_comment' )) {
	add_action ( 'wp_ajax_hana_delete_comment', 'hana_ajax_delete_comment' );
	add_action ( 'wp_ajax_nopriv_hana_delete_comment', 'hana_ajax_delete_comment' );
	function hana_ajax_delete_comment() {
		global $wpdb;
		// check_ajax_referer( 'hana_delete_comment', 'nonce' );
		$res = array ();
		
		$comment_id = $_POST ['comment_id'];
		$comment = get_comment ( $comment_id );
		$comment_author_id = $comment->user_id;
		$res ['comment_author_id'] = $comment_author_id;
		$res ['current_user_id'] = get_current_user_id ();
		
		$children = $wpdb->get_col ( $wpdb->prepare ( "SELECT comment_ID FROM $wpdb->comments WHERE comment_parent = %d", $comment_id ) );
		
		if (get_current_user_id () == 0 || (( int ) $comment->user_id) != get_current_user_id () && ! (current_user_can ( 'editor' ) || current_user_can ( 'administrator' ))) {
			$res ['error'] = 1;
		} elseif (! empty ( $children )) {
			$res ['error'] = 2;
		} else {
			wp_delete_comment ( $comment_id );
			$res ['error'] = 0;
			$res ['comment_id'] = $comment_id;
		}
		echo json_encode ( $res );
		exit ();
	}
}
if (! function_exists ( 'hana_comments_form' )) {
	
	/**
	 * Outputs a complete commenting form for use within a template.
	 * Most strings and form fields may be controlled through the $args array
	 * passed
	 * into the function, while you may also choose to use the
	 * comment_form_default_fields
	 * filter to modify the array of default fields if you'd just like to add a
	 * new
	 * one or remove a single field. All fields are also individually passed
	 * through
	 * a filter of the form comment_form_field_$name where $name is the key used
	 * in the array of fields.
	 *
	 * @param array $args
	 *        	Options for strings, fields etc in the form
	 * @param mixed $post_id
	 *        	Post ID to generate the form for, uses the current post if
	 *        	null
	 * @return void
	 */
	function hana_comments_form($args = array(), $post_id = null) {
		global $id;
		
		$user = wp_get_current_user ();
		$user_identity = $user->exists () ? $user->display_name : '';
		
		if (null === $post_id) {
			$post_id = $id;
		} else {
			$id = $post_id;
		}
		
		if (comments_open ( $post_id )) {
			echo '<div id="respond-wrap">';
			
			$commenter = wp_get_current_commenter ();
			$req = get_option ( 'require_name_email' );
			$aria_req = ($req ? " aria-required='true'" : '');
			$fields = array (
					'author' => '<div class="row"><p class="comment-form-author col-sm-4"><label for="author">' . __ ( 'Name', HANA_BOARD_TEXT_DOMAIN ) . '</label> ' . ($req ? '<span class="required">*</span>' : '') . '<input id="author" name="author" type="text" class="form-control" value="' . esc_attr ( $commenter ['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
					'email' => '<p class="comment-form-email col-sm-4"><label for="email">' . __ ( 'Email', HANA_BOARD_TEXT_DOMAIN ) . '</label> ' . ($req ? '<span class="required">*</span>' : '') . '<input id="email" name="email" type="text" class="form-control" value="' . esc_attr ( $commenter ['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
					'url' => '<p class="comment-form-url col-sm-4"><label for="url">' . __ ( 'Website', HANA_BOARD_TEXT_DOMAIN ) . '</label><input id="url" name="url" type="text" class="form-control" value="' . esc_attr ( $commenter ['comment_author_url'] ) . '" size="30" /></p></div>',
					'redirect_to' => '<input type="hidden" name="redirect_to" value="' . get_permalink () . '"/>' 
			);
			
			if (function_exists ( 'bp_is_active' )) {
				$profile_link = bp_get_loggedin_user_link ();
			} else {
				$profile_link = admin_url ( 'profile.php' );
			}
			$comments_args = array (
					'fields' => apply_filters ( 'comment_form_default_fields', $fields ),
					'title_reply' => __ ( 'Leave a reply', HANA_BOARD_TEXT_DOMAIN ),
					'title_reply_to' => __ ( 'Leave a reply to %s', HANA_BOARD_TEXT_DOMAIN ),
					'cancel_reply_link' => __ ( 'Click here to cancel the reply', HANA_BOARD_TEXT_DOMAIN ),
					'label_submit' => __ ( 'Post comment', HANA_BOARD_TEXT_DOMAIN ),
					'comment_field' => '<p class="comment-form-comment"><label for="comment">' . __ ( 'Comment', HANA_BOARD_TEXT_DOMAIN ) . '</label><textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
					/*
					'logged_in_as' => wp_kses('<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', HANA_BOARD_TEXT_DOMAIN ), esc_url($profile_link), $user_identity, esc_url(wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) )) ) . '</p>', array(  'a' => array( 'href' => array(), 'title'=>array() ))),
					'must_log_in' => wp_kses('<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', HANA_BOARD_TEXT_DOMAIN ), esc_url(wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )) ) . '</p>', array(  'a' => array( 'href' => array(), 'title'=>array() ), 'p' => array('class'=>array())))
					*/ 
			);
			comment_form ( $comments_args, $post_id );
			echo '</div>';
		}
	}
}

if (! function_exists ( 'hana_comments_preprocess' )) {
	function hana_comments_preprocess($commentdata) {
		// some code
		return wp_kses_data ( $commentdata );
	}
	add_filter ( 'preprocess_comment', 'hana_comments_preprocess' );
}