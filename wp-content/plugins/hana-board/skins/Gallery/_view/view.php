<div id="hanaboard-post-<?php the_ID(); ?>" class="hanaboard-page-view">
	<div class="hanaboard-entry-header">
		<h3 class="hanaboard-entry-title">
			<?php if( hanaboard_is_show('sub_category')) hanaboard_get_term_link('[%s] '); ?>
			<?php the_title(); ?>
		</h3>
		<div class="entry-meta">
			<div class="row">
				<div class="col-xs-6 article-author ellipsis">
					<?php echo hanaboard_get_the_author_link(); ?>
				</div>
				<div class="col-xs-6 text-right article-date">
					<i class="fa fa-clock-o"></i> <?php the_date('Y-m-d'); ?> <?php the_time('H:i'); ?>
				</div>
			</div>
		</div>
		<div class="entry-meta">
			<div class="row">
				<div class="col-xs-6 article-category">
					<i class="fa fa-tags"></i> <?php echo hanaboard_get_term_link(); ?>
					<?php if(hanaboard_get_post_subcategory()) { ?>
						&gt; <span class="loop-subcategory"><?php echo hanaboard_get_post_subcategory() ?></span>
					<?php } ?>
				</div>
				<div class="col-xs-6 board-article-meta text-right">
					<?php if (hanaboard_get_option('show_readcount')) { ?>
						<span>
						<i class="fa fa-search"></i> <?php echo hanaboard_get_readcount(); ?>
					</span>
					<?php } ?>
					<?php if (hanaboard_is_show('like')) { ?>
						<span>
						<i class="fa fa-thumbs-o-up"></i> <?php echo hana_like_get_like_count(); ?>
					</span>
					<?php } ?>
					<?php if (hanaboard_is_show('dislike')) { ?>
						<span>
						<i class="fa fa-thumbs-o-down"></i> <?php echo hana_like_get_dislike_count(); ?>
					</span>
					<?php } ?>
					<span>
						<i class="fa fa-comments-o"></i> <?php echo comments_number(0, 1, '%'); ?>
					</span>
				</div>
			</div>
		</div>

		<?php if (hanaboard_get_option('allow_attachment') && has_post_attachment()) { ?>
			<div class="entry-meta">
				<div class="hanaboard-attachments">
					<label for="hanaboard-attachments-list"><?php _e(' Attachments ', HANA_BOARD_TEXT_DOMAIN); ?></label>
					<ul>
						<?php foreach (hanaboard_get_attachments() as $file) { ?>
							<li>
								<a href="<?php echo $file->url;?>" title="<?php echo esc_attr( $file->title );?>">
							<span class="attachment-filename">
							<?php echo $file->title; ?>
						</span>
									<span class="attachment-filesize">
							(<?php echo $file->filesize; ?>)
						</span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		<?php } ?>

		<?php do_action('hanaboard_view_custom_field_before_content'); ?>
	</div>
	<div class="hanaboard-article-content">
		<?php the_content(); ?>
	</div>
	<?php do_action('hanaboard_view_custom_field_after_content'); ?>
	<div class="entry-meta">
		<div class="hanaboard-tags">
			<?php echo hanaboard_get_the_tags(); ?>
		</div>
		<?php if (hanaboard_get_option('show_author_profile')) { ?>
			<div id="author-info" class="clearfix">
				<div class="author-img">
					<a class="userpro-tip-fade lightview" href="<?php echo hanaboard_get_avatar_url();?>" data-lightview-caption="">
						<?php echo get_avatar(get_the_author_meta('ID'), '96'); ?>
					</a>
				</div>
				<div class="author-desc">
					<h4>
						<?php printf(esc_attr__('About %s', HANA_BOARD_TEXT_DOMAIN), hanaboard_get_the_author()); ?>
					</h4>
					<p><?php echo wp_kses(get_the_author_meta('description'), null); ?></p>
					<div class="profile-links clearfix">
						<ul class="social-links">
							<li>
								<a class="author-icon" href="<?php echo get_author_posts_url(get_the_author_meta('ID')) ?>">
									<i class="fa fa-user"></i>
								</a>
							</li>

							<?php if (get_the_author_meta('twitter') != '') { ?>
								<li>
									<a class="author-icon" target="_blank" href="<?php echo esc_url( get_the_author_meta( 'twitter' ) ); ?>">
										<i class="fa fa-twitter"></i>
									</a>
								</li>
							<?php } ?>

							<?php if (get_the_author_meta('facebook') != '') { ?>
								<li>
									<a class="author-icon" target="_blank" href="<?php echo esc_url( get_the_author_meta( 'facebook' ) ); ?>">
										<i class="fa fa-facebook"></i>
									</a>
								</li>
							<?php } ?>

							<?php if (get_the_author_meta('linkedin') != '') { ?>
								<li>
									<a class="author-icon" target="_blank" href="<?php echo esc_url( get_the_author_meta( 'linkedin' ) ); ?>">
										<i class="fa fa-linkedin"></i>
									</a>
								</li>
							<?php } ?>

							<?php if (get_the_author_meta('googleplus') != '') { ?>
								<li>
									<a class="author-icon" target="_blank" href="<?php echo esc_url( get_the_author_meta( 'googleplus' ) ); ?>">
										<i class="fa fa-google-plus"></i>
									</a>
								</li>
							<?php } ?>

							<?php if (get_the_author_meta('pinterest') != '') { ?>
								<li>
									<a class="author-icon" target="_blank" href="<?php echo esc_url( get_the_author_meta( 'pinterest' ) ); ?>">
										<i class="fa fa-pinterest-square"></i>
									</a>
								</li>
							<?php } ?>

						</ul>
					</div>
				</div>
			</div>
			<!-- end author profile -->
		<?php } ?>
	</div>
	<div class="hanaboard-button-bottom">
		<button class="hanaboard-button btn-primary btn button hanaboard-button" onclick="location.href='<?php echo hanaboard_back_to_list_url();?>';">
			<i class="fa fa-list"></i> <?php _e('List', HANA_BOARD_TEXT_DOMAIN)?>
		</button>
		<?php if (hanaboard_current_user_can('write_reply', get_the_ID())) { ?>
			<button class="hanaboard-button btn-secondary btn button hanaboard-button" onclick="location.href='<?php echo $reply_link;?>';">
				<i class="fa fa-reply"></i> <?php _e('Reply', HANA_BOARD_TEXT_DOMAIN)?>
			</button>
		<?php } ?>
		<?php if (hanaboard_current_user_can('edit', get_the_ID())) { ?>
			<button class="hanaboard-button btn-secondary btn button hanaboard-button" onclick="location.href='<?php echo hanaboard_add_query_arg( 'mode', 'edit'  );?>';">
				<i class="fa fa-pencil-square-o"></i> <?php _e('Edit', HANA_BOARD_TEXT_DOMAIN)?>
			</button>
		<?php } ?>
		<?php if (hanaboard_current_user_can('edit', get_the_ID())) { ?>
			<button class="hanaboard-button btn-secondary btn button hanaboard-button" id="delete_post_button" data-post-id="<?php the_ID();?>" data-is-author-guest="<?php hanaboard_is_author_guest();?>">
				<i class="fa fa-trash-o"></i> <?php _e('Delete', HANA_BOARD_TEXT_DOMAIN)?>
			</button>
		<?php } ?>
	</div>
	<!-- Post Password Dialog for deletion -->
	<div id="hanaboard-guest-password-dialog" title="<?php _e('Post Password', HANA_BOARD_TEXT_DOMAIN);?>" style="display: none;">
		<form id="hanaboard-delete-post-form" name="hanaboard_delete_post_form" enctype="multipart/form-data" method="POST" class="wp-core-ui">
			<p class="validateTips text-center">
				<?php _e('Enter post password.', HANA_BOARD_TEXT_DOMAIN);?>
			</p>
			<div class="text-center">
				<input type="hidden" name="action" value="hanaboard_delete_post" />
				<input type="hidden" name="hanaboard_ajax_delete_post_nonce" value="<?php echo wp_create_nonce('hanaboard_delete_post_nonce');?>" />
				<input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>" />
				<input type="hidden" name="archive_list_url" value="<?php echo hanaboard_back_to_list_url();?>" />
				<input class="requiredField guest-password" type="password" value="" name="hanaboard_guest_password" id="hanaboard-guest-password-input" placeholder="<?php _e('Post Password', HANA_BOARD_TEXT_DOMAIN);?>" data-mode="<?php echo get_query_var('mode');?>" data-post-id="<?php the_ID();?>" />
				<div id="guest_password_alert_message"></div>
			</div>
		</form>
	</div>
	<div class="adsense" style="text-align: center; max-width: 100%;">
		<!-- //Adsense area -->
	</div>
	<div class="hanaboard-comment-area">
		<?php if(hanaboard_get_option('allow_comments')) { ?>
			<?php comments_template('', true); ?>
		<?php } ?>
	</div>
	<div id="loading_dialog"></div>
</div>