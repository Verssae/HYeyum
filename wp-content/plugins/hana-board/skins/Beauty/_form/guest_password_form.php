<div id="hanaboard-guest-password-wrapper" class="hanaboard-guest-password-wrapper" title="<?php _e('Post Password', HANA_BOARD_TEXT_DOMAIN);?>" style="margin:0 auto; width:300px; padding: 30px 10px; text-align:center;">
	<form id="hanaboard-guest_password-form" name="hanaboard_guest_password_form" method="POST" class="wp-core-ui">
		<p class="top_large_icon"><i class="fa fa-lock"></i></p>
		<p class="description validateTips text-center">
				<?php _e('This post is password protected.', HANA_BOARD_TEXT_DOMAIN); ?><br/><?php _e('Enter post password.', HANA_BOARD_TEXT_DOMAIN); ?>
			</p>
		<div class="text-center">
			<input type="hidden" name="action" value="hanaboard_view_private_guest_post" />
			<input type="hidden" name="hanaboard_view_private_post_nonce" value="<?php echo wp_create_nonce('hanaboard_delete_post_nonce');?>" />
			<input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>" />
			<input class="requiredField guest-password" type="password" value="" name="hanaboard_guest_password" id="hanaboard-guest-password-input" placeholder="<?php _e('Post Password', HANA_BOARD_TEXT_DOMAIN);?>" data-mode="<?php echo get_query_var('mode');?>" data-post-id="<?php the_ID();?>" />
			<div id="guest_password_alert_message"></div>
		</div>
		<div class="text-center " style="margin-top: 20px;">
			<button type="submit" class="btn btn-primary hanaboard-button">
				<i class="fa fa-check"></i> <?php _e('Submit',HANA_BOARD_TEXT_DOMAIN); ?></button>
			<button type="button" class="btn btn-secondary hanaboard-button" onclick="location.href='<?php echo hanaboard_back_to_list_url();?>';">
				<i class="fa fa-times"></i>
					<?php _e('Cancel', HANA_BOARD_TEXT_DOMAIN)?>
				</button>
		</div>
	</form>
</div>