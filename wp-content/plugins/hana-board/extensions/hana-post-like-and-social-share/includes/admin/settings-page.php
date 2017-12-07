<div id="wrap hanawordpress_settings_page">
	<?php screen_icon(); ?>
	<h2><?php echo esc_html( $title ); ?></h2>
	
	<?php foreach( $messages as $message ) { ?>
	<div id="message" class="updated <?php echo $message['type']; ?>">
		<p><?php echo $message['message']; ?></p>
	</div>
	<?php } ?>

	<div class="settings_column col-sm-9">
		<form name="hana_like-settings-form" method="post">
			<input type="hidden" name="hana_like_options_submit" value="true" />
			<?php echo wp_nonce_field( 'hana_like-settings-form', 'hana_like-settings-nonce' ); ?>
			<h2><?php _e('Post Likes', HANA_LIKE_TEXT_DOMAIN);?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<span><?php _e('Like Text', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<input type="text" name="like_text" value="<?php echo $settings['like_text']; ?>" placeholder="" />
						<p class="description"><?php _e('Keep Empty to use \'Like\'', HANA_LIKE_TEXT_DOMAIN); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<span><?php _e('Liked Text', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<input type="text" name="liked_text" value="<?php echo $settings['liked_text']; ?>" placeholder="" />
						<p class="description"><?php _e('Keep Empty to use \'Liked\'', HANA_LIKE_TEXT_DOMAIN); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<span><?php _e('DisLike Text', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<input type="text" name="dislike_text" value="<?php echo $settings['dislike_text']; ?>" placeholder="" />
						<p class="description"><?php _e('Keep Empty to use \'Dislike\'', HANA_LIKE_TEXT_DOMAIN); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<span><?php _e('DisLiked Text', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<input type="text" name="disliked_text" value="<?php echo $settings['disliked_text']; ?>" placeholder="" />
						<p class="description"><?php _e('Keep Empty to use \'Disliked\'', HANA_LIKE_TEXT_DOMAIN); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<span><?php _e('Skin', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<select name="skin_like">
						<?php
						foreach ($like_skins as $skin_key => $skin_name) {
							$selected = $like_skin_current == $skin_key ? 'selected="selected"' : '';
							?>
								<option value="<?php echo $skin_key; ?>" <?php echo $selected; ?>><?php echo $skin_name; ?></option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>				
				<tr>
					<th scope="row">
						<span><?php _e('Post Types', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
				<?php
				foreach ($post_types as $post_type) {
					if ($post_type == 'hanaboard-post')
						$post_type_name = $hanaboard_post_name;
					else
						$post_type_name = $post_type;
					$checked = isset($settings['post_types_like'][$post_type]) ? 'checked="checked"' : '';
					?>
						<div>
							<label>
								<input type="checkbox" name="post_types_like[<?php echo $post_type; ?>]" <?php echo $checked; ?> /> <?php echo $post_type_name; ?>
							</label>
						</div>
					<?php
				}
				?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<span><?php _e('Like Items', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<div>
							<label>
								<input type="radio" name="like_items" value="like_only" <?php echo ($settings['like_items']=='like_only')? 'checked="checked"':''; ?> /> <?php _e('Like Only',HANA_LIKE_TEXT_DOMAIN); ?>
							</label>
						</div>
						<div>
							<label>
								<input type="radio" name="like_items" value="like_and_dislike" <?php echo ($settings['like_items']=='like_and_dislike')? 'checked="checked"':''; ?> /> <?php _e('Like and Dislike',HANA_LIKE_TEXT_DOMAIN); ?>
							</label>
						</div>
					</td>
				</tr>
				<!-- 
				<tr>
					<th scope="row">
						<span><?php _e('Permission', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<div>
							<label>
								<input type="radio" name="like_permission" value="logged_in_user" <?php echo ($settings['like_permission']=='logged_in_user')? 'checked="checked"':''; ?> /> <?php _e('Logged in users only.',HANA_LIKE_TEXT_DOMAIN); ?>
							</label>
						</div>
						<div>
							<label>
								<input type="radio" name="like_permission" value="everyone" <?php echo ($settings['like_permission']=='everyone')? 'checked="checked"':''; ?> /> <?php _e('Everyone',HANA_LIKE_TEXT_DOMAIN); ?>(<?php _e('If user is not logged on, checking IP address instead of user ID.'); ?>)
							</label>
						</div>
					</td>
				</tr>
				 -->
			</table>
			<h2><?php _e('Social Share', HANA_LIKE_TEXT_DOMAIN);?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<span><?php _e('Share Title', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<input type="text" name="title_social_share" value="<?php echo $settings['title_social_share']; ?>" placeholder="" />
						<p class="description"><?php _e('Keep Empty to use \'Social Share\'', HANA_LIKE_TEXT_DOMAIN); ?></p>
					</td>
				</tr>				
				<tr>
					<th scope="row">
						<span><?php _e('Skin', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<select name="skin_social_share">
						<?php
						$social_share_skin_current = isset($settings['skin_social_share'])?$settings['skin_social_share']:'';
						foreach ($social_share_skins as $skin_key => $skin_name) {
							$selected = $social_share_skin_current == $skin_key ? 'selected="selected"' : '';
							?>
								<option value="<?php echo $skin_key; ?>" <?php echo $selected; ?>><?php echo $skin_name; ?></option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>	
				<tr>
					<th scope="row">
						<span><?php _e('Icon style', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<select name="icon_style_social_share">
						<?php
						$icon_style_social_share_current = isset($settings['icon_style_social_share'])?$settings['icon_style_social_share']:'';
						foreach ($icon_style_social_share as $icon_style_key => $icon_style_name) {
							$selected = $icon_style_social_share_current == $icon_style_key ? 'selected="selected"' : '';
							?>
								<option value="<?php echo $icon_style_key; ?>" <?php echo $selected; ?>><?php echo $icon_style_name; ?></option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>	
				<tr>
					<th scope="row">
						<span><?php _e('Post Types', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
				<?php
				foreach ($post_types as $post_type) {
					if ($post_type == 'hanaboard-post')
						$post_type_name = $hanaboard_post_name;
					else
						$post_type_name = $post_type;
					$checked = isset($settings['post_types_social_share'][$post_type]) ? 'checked="checked"' : '';
					?>
						<div>
							<label>
								<input type="checkbox" name="post_types_social_share[<?php echo $post_type; ?>]" <?php echo $checked; ?> /> <?php echo $post_type_name; ?>
							</label>
						</div>
					<?php
				}
				?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<span><?php _e('SNS Items', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<?php
						foreach ($available_sns as $sns => $sns_name) {
							$checked = isset($settings['sns_enabled'][$sns]) ? 'checked="checked"' : '';
							?>
								<div>
									<label>
										<input type="checkbox" name="sns_enabled[<?php echo $sns; ?>]" <?php echo $checked; ?> /> <?php echo $sns_name; ?>
									</label>
								</div>
							<?php
						}
						?>
					</td>
				</tr>			
				<tr>
					<th scope="row">
						<span><?php _e('Kakao API Key', HANA_LIKE_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<input type="text" name="api_key_kakao" size="50" value="<?php echo $settings['api_key_kakao']; ?>" placeholder="<?php _e('e.g. w1e2b3j4a5n6g7i8nw1e2b3j4a5n6g (32 Characters)', HANA_LIKE_TEXT_DOMAIN);?>" />
						<p class="description"><a href="https://developers.kakao.com/docs/js" target=_blank ><?php _e('How does it work?',HANA_LIKE_TEXT_DOMAIN); ?></a></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button-primary"><?php _e('Save changes', HANA_LIKE_TEXT_DOMAIN); ?></button>
			</p>
		</form>
	</div>
	<div class="col-sm-3">
		<div id="headlines">
			<h3><?php _e('Latest news from HanaWordpress.com', HANA_LIKE_TEXT_DOMAIN); ?></h3>
				<?php
				// echo get_latest_tweet('mattsay');
				
				$rss_options = array(
						'link' => 'http://hanawordpress.com',
						'url' => 'http://hanawordpress.com/feed/',
						'title' => 'hanawordpress.com',
						'items' => 5,
						'show_summary' => 0,
						'show_author' => 0,
						'show_date' => 0,
						'before' => 'text'
				);
				wp_widget_rss_output($rss_options);
				?>
		</div>
	</div>
	<div id="tabs-footer" class="clearfix">
		<div class="copyright">
			<em><a href="http://hanawordpress.com/hana-like">Hana Post Like and Social Share</a> by <a href="http://webjang.in"><?php _e('HanaWordpress',HANA_LIKE_TEXT_DOMAIN); ?></a></em>
		</div>
	</div>
</div>
