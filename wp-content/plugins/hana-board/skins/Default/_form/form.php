<form id="hanaboard-post-form" name="hanaboard_post_form" enctype="multipart/form-data" method="POST" class="form-horizonal">
	<?php wp_nonce_field('hanaboard-post-form', 'hanaboard-add-post-nonce')?>

	<input type="hidden" name="hanaboard_ajax_nonce" value="<?php echo wp_create_nonce('hanaboard_post_submit_nonce');?>" />
	<input type="hidden" name="action" value="hanaboard_submit_post" />
	<input type="hidden" name="hanaboard_post_type" value="<?php echo HANA_BOARD_POST_TYPE; ?>" />
	<input type="hidden" name="hanaboard_post_ID" value="<?php echo $post_id; ?>" />
	<input type="hidden" name="hanaboard_post_form_mode" value="<?php echo get_query_var( HANA_BOARD_QUERY_VAR_MODE ); ?>" />
	<input type="hidden" name="hanaboard_post_parent" value="<?php echo $post_parent_id; ?>" />

	<?php do_action('hanaboard_add_post_form_top', HANA_BOARD_POST_TYPE); ?>
	<div id="hanaboardErrors"></div>
	<?php if (!is_user_logged_in()) { ?>
	<fieldset class="formGuest">
		<div class="row form-group">
			<label for="new-post-guest-author" class="col-xs-3 control-label "> <?php _e('Name', HANA_BOARD_TEXT_DOMAIN); ?>
				<span class="required"></span>
			</label>
			<div class="col-xs-9 ">
				<input class="requiredField guest-author form-control" type="text" value="<?php echo hanaboard_get_the_post_meta('guest_author'); ?>" name="hanaboard_guest_author" id="new-post-guest-author" minlength="2" placeholder="<?php _e('Nick Name', HANA_BOARD_TEXT_DOMAIN);?>">
			</div>
		</div>
		<div class="row form-group">
			<label for="hanaboard-guest-password-input" class="col-xs-3 control-label "> <?php _e('Password', HANA_BOARD_TEXT_DOMAIN); ?>
				<span class="required"></span>
			</label>
			<div class="col-xs-9 ">
				<input class="requiredField guest-password form-control" type="password" value="" name="hanaboard_guest_password" id="hanaboard-guest-password-input" minlength="4" placeholder="<?php _e('Post Password', HANA_BOARD_TEXT_DOMAIN);?>" data-mode="<?php echo get_query_var('mode');?>" data-post-id="<?php the_ID();?>" />
				<div id="guest_password_alert_message"></div>
			</div>
		</div>
		<div class="row form-group">
			<label for="new-post-guest-email" class="col-xs-3 control-label "> <?php _e('Email', HANA_BOARD_TEXT_DOMAIN); ?>
				<span class="required"></span>
			</label>
			<div class="col-xs-9 ">
				<input class="email requiredField form-control" type="text" value="<?php echo hanaboard_get_the_post_meta('guest_email'); ?>" name="hanaboard_guest_email" id="new-post-guest-email" minlength="2" placeholder="<?php _e('Email', HANA_BOARD_TEXT_DOMAIN);?>">
			</div>
		</div>		
		<?php
		do_action( 'hanaboard_add_post_form_guest', HANA_BOARD_POST_TYPE );
		?>
	</fieldset>
	<?php } ?>

	<div class="row form-group">
		<label for="cat" class="col-xs-3 control-label "> <?php _e('Category', HANA_BOARD_TEXT_DOMAIN); ?>
			<span class="required"></span>
		</label>
		<div class="col-xs-9 ">
			<?php if (hanaboard_is_show('cat_selectable')) { ?>
				<?php echo hanaboard_dropdown_category_list(); ?>
			<?php } else { ?>
			<input type="hidden" name="cat" value="<?php echo $term_id; ?>" />
			<span class="category_name"><?php echo $term_name; ?>
				<?php } ?>
				<?php //todo: cat onchange ?>
				<?php $sub_categories = hanaboard_get_subcategory_map(); ?>
				<?php if (sizeof($sub_categories)) { ?>
					<?php $selected_sub_category = get_query_var('sub_cat'); ?>
					<?php echo hanaboard_get_subcategory_selectbox(hanaboard_get_current_term_id(), $selected_sub_category); ?>
				<?php } ?>
		</div>
	</div>
	<div class="row">
		<?php do_action('hanaboard_add_post_form_category', HANA_BOARD_POST_TYPE); ?>
	</div>
	<div class="row form-group">
		<label for="title" class="col-xs-3 control-label "> <?php _e('Title', HANA_BOARD_TEXT_DOMAIN); ?>
			<span class="required"></span>
		</label>
		<div class="col-xs-9 ">
			<input class="form-control requiredField" placeholder="<?php _e('Enter title here', HANA_BOARD_TEXT_DOMAIN);?>" type="text" value="<?php echo $post_title;?>" name="post_title" id="title" minlength="2" />
		</div>
	</div>
	<div class="row">
		<div class="col-xs-9 col-xs-offset-3">
			<?php if (hanaboard_get_option('allow_notice') && hanaboard_current_user_can('notice')) { ?>
			<label class="checkbox-inline">
				<input type="checkbox" name="hanaboard_is_notice" <?php echo hanaboard_is_post_notice() ? 'checked' : ''; ?> /> <?php _e('Notice', HANA_BOARD_TEXT_DOMAIN); ?>
			</label>
			<?php } ?>

			<?php if (hanaboard_get_option('allow_secret_post')) { ?>
			<label class="checkbox-inline">
				<input type="checkbox" name="hanaboard_is_secret" <?php echo hanaboard_is_post_private() ? 'checked' : ''; ?> /> <?php _e('Secret', HANA_BOARD_TEXT_DOMAIN); ?>
			</label>
			<?php } ?>
			<?php do_action('hanaboard_add_notice_checkbox');?>
		</div>
	</div>

	<?php do_action('hanaboard_add_post_form_after_title', HANA_BOARD_POST_TYPE); ?>

	<?php if (hanaboard_get_option('allow_attachment') || hanaboard_get_option('allow_upload_media')) { ?>
	<div class="row insert-media-buttons">
		<div class="col-xs-9 col-xs-offset-3 ">
			<div class="row">
				<button type="button" id="hanaboard-attachment-upload-pickfiles" class="btn btn-secondary hanaboard-button">
					<i class="fa fa-picture-o"></i>
					<?php 
						if (hanaboard_get_option('allow_attachment'))
							_e('Image / File', HANA_BOARD_TEXT_DOMAIN);
						else
							_e('Image', HANA_BOARD_TEXT_DOMAIN);
					?>
				</button>
				<button type="button" id="hanaboard-insert-video-button" class="btn btn-secondary hanaboard-button">
					<i class="fa fa-youtube-play"></i>
					<?php _e('Video', HANA_BOARD_TEXT_DOMAIN); ?>
				</button>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="row">
		<div class="col-xs-9 col-xs-offset-3 ">
			<div id="hanaboard-attachment-upload-container">
				<div id="hanaboard-attachment-upload-filelist">
					<ul class="hanaboard-attachment-list">			
						<?php do_action('hanaboard_add_post_form_attachments', $post_id); ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 ">
			<?php if (hanaboard_get_option('editor_type') == 'rich') { ?>
			<div id="hanaboard-richtext">
				<?php wp_editor($post_content, 'post_content', $editor_args); ?>
			</div>
			<?php } else { ?>
			<textarea name="post_content" class="form-control post_content" id="post_content" cols="80" rows="12"><?php echo esc_textarea($post_content); ?></textarea>
			<?php } ?>
		</div>
	</div>

	<?php
	if (! defined( 'ABSPATH' ))
		exit();
	
	do_action( 'hanaboard_add_post_form_tags', HANA_BOARD_POST_TYPE );
	?>

	<?php if (hanaboard_get_option('allow_tags')) { ?>
	<div class="row form-group">
		<label class="col-sm-3 "> <?php _e('Tags', HANA_BOARD_TEXT_DOMAIN); ?>
		</label>
		<div class="col-sm-9 ">
			<input type="text" size="50" name="tags" id="new-post-tags" class="form-control" value="<?php echo $tags; ?>" placeholder="<?php _e('Tags must be separated with comma.', HANA_BOARD_TEXT_DOMAIN);?>" />
		</div>
	</div>
	<?php } ?>

	<?php
	do_action( 'hanaboard_add_post_form_bottom', HANA_BOARD_POST_TYPE );
	?>

	<div class="row text-center">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-primary hanaboard-button" name="hanaboard_new_post_submit" id="hanaboard_new_post_submit">
				<i class="fa fa-check"></i>
				<?php _e('Submit', HANA_BOARD_TEXT_DOMAIN); ?>
			</button>
			<button type="button" class="btn btn-secondary hanaboard-button" onclick="if(confirm('<?php _e('Do you really want to cancel input?', HANA_BOARD_TEXT_DOMAIN); ?>')){location.href='<?php echo hanaboard_back_to_list_url();?>';}">
				<i class="fa fa-times"></i>
				<?php _e('Cancel', HANA_BOARD_TEXT_DOMAIN)?>
			</button>
		</div>
	</div>
	<div id="insert-video-dialog-form" title="<?php _e('Insert Video', HANA_BOARD_TEXT_DOMAIN);?>" style="display: none;">
		<p class="validateTips"><?php _e('Paste Video source code start with &lt;iframe ..',HANA_BOARD_TEXT_DOMAIN); ?></p>
		<fieldset>
			<textarea name="insert_video_html" id="insert_video_html" rows="5" cols="80" style="width: 100%;"></textarea>
			<input type="button" tabindex="-1" style="position: absolute; top: -1000px" />
		</fieldset>
	</div>
	<div id="loading_dialog"></div>
</form>
