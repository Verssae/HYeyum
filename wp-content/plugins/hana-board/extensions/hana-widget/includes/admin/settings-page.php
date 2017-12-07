<div id="wrap hanawordpress_settings_page">
	<?php screen_icon(); ?>
	<h2><?php echo esc_html( $title ); ?></h2>
	
	<?php foreach( $messages as $message ) { ?>
	<div id="message" class="updated <?php echo isset($message['type']) ? $message['type'] : ''; ?>">
		<p><?php echo $message['message']; ?></p>
	</div>
	<?php } ?>
<?php 
/*				'title' => '',
				'offset' => 0,
				'scheme' => 'light',
				'skin' => 'default',
				'post_type' => 'post',
				'taxonomy' => 'category',
				'number' => 5,
				'terms' => '',
				'columns' => 1,
				'show_thumbnail' => false,
				'thumbnail_size' => 'hana_wide_thumb',
				'show_post_title' => true,
				'show_author' => false,
				'show_date' => true,
				'show_num_comments' => false 
*/
?>
<p class="description">하나 위젯은 사이드바 또는 하단의 위젯 메뉴에서 사용하실 수 있으며, shortcode를 사용하여 어떠한 포스트 타입도 출력 가능합니다.</p>
<h2>Shortcode 사용 예</h2>
<table>
<tr>
	<th>HanaBoard</th><td>[hana_board_widget terms="free-board" skin="default"][/hana_board_widget]</td>
</tr>
<tr>
	<th>Blog Post</th><td>[hana_post_widget terms="카테고리1" skin="default"][/hana_post_widget]</td>
</tr>
<tr>
	<th>Post Type</th><td>[hana_post_widget terms="카테고리1" skin="default" taxonomy="custom_taxonomy" post_type="custom_post_type"][/hana_post_widget]</td>
</tr>
<tr>
	<th>특정 포스트</th><td>terms="" 대신 post_id="123"를 입력하시면 특정 포스트를 출력합니다.</td>
</tr>
<tr>
	<th>Comment</th><td>[hana_comment_widget skin="default" number="5"][/hana_comment_widget]</td>
</tr>

</table>

<h3>Shortcode 속성 - 포스트 위젯</h3>
<p class="description">포스트 위젯 스킨을 추가 또는 수정하실 경우, plugins/hana-widget/layouts/post/안에 스킨 폴더로르 업로드해 주세요.</p>
<table>
<tr>
	<th>skin</th><td>게시판 스킨을 선택합니다.</td>
</tr>
<tr>
	<th>post_type</th><td>포스트 타입을 지정합니다.<br>기본값 : hanaboard-post</td>
</tr>
<tr>
	<th>taxonomy</th><td>카테고리 유형(taxonomy)를 선택합니다.<br>기본값 : hanaboard</td>
</tr>
<tr>
	<th>number</th><td>게시물 수를 선택합니다.<br>기본값 : 5</td>
</tr>
<tr>
	<th>terms</th><td>카테고리명(slug)를 지정합니다.<br>기본값 : 모든 카테고리. 콤마로 지정 가능합니다.(예: free,notice,gallery)</td>
</tr>
<tr>
	<th>columns</th><td>칼럼 수를 지정합니다(gallery에서만 사용됩니다)<br>기본값 : 1</td>
</tr>
<tr>
	<th>offset</th><td>오프셋을 지정합니다.<br>기본값 : 0. N개의 게시물 다음부터 출력합니다.</td>
</tr>
<tr>
	<th>show_author</th><td>글쓴이를 표시합니다.<br>기본값 : 0. 출력하려면 1 또는 true를 입력합니다.</td>
</tr>
<tr>
	<th>show_date</th><td>날짜를 표시합니다.<br>기본값 : 1. 숨기려면 0을 입력합니다.</td>
</tr>
</table>
<input type="text" size="200" style="max-width: 80%;" placeholder="메모장입니다. 여기에 입력 후 복사하세요." />

<h3>Shortcode 속성 - 댓글 위젯</h3>
<p class="description">댓글 위젯 스킨을 추가 또는 수정하실 경우, plugins/hana-widget/layouts/comment/안에 스킨 폴더로르 업로드해 주세요.</p>
<table>
<tr>
	<th>number</th><td>표시할 댓글 수를 지정합니다.<br>기본값 : 5.</td>
</tr>
<tr>
	<th>author__not_in</th><td>숨길 글쓴이를 지정합니다. 글쓴이의 ID를 입력해 주세요.<br>기본값 : None. 콤마(,)로 구분해 주세요.</td>
</tr>
<tr>
	<th>skin</th><td>스킨을 지정합니다.<br>기본값 : default</td>
</tr>
</table>
<h3>ShortCode Generator가 업데이트 될 예정입니다.</h3>

<!-- 
	<div class="settings_column col-sm-9">
		<form name="hana_widget-settings-form" method="post">
			<input type="hidden" name="hana_like_options_submit" value="true" />
			<?php echo wp_nonce_field( 'hana_like-settings-form', 'hana_like-settings-nonce' ); ?>

			<table class="form-table" id="predefined_shortcode_table">
				<?php foreach($predfined_shortcodes as $key=>$predefined_shortcode){ ?>
				<tr>
					<th scope="row">
						<span><?php echo $key; ?></span>
					</th>
					<td>
						<textarea name="predefined_shortcode[$key]"><?php echo $predefined_shortcode; ?></textarea>
					</td>
					<td>
						<button class="button button-secondary"><?php _e('Modify', HANA_WIDGET_TEXT_DOMAIN); ?></button>
						<button class="button button-secondary"><?php _e('Delete', HANA_WIDGET_TEXT_DOMAIN); ?></button>
						<button class="button button-secondary"><?php _e('Duplicate', HANA_WIDGET_TEXT_DOMAIN); ?></button>
					</td>
				</tr>				
				<?php } ?>
			</table>
			<h2><?php _e('Shortcode Generator', HANA_WIDGET_TEXT_DOMAIN);?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<span><?php _e('Data type', HANA_WIDGET_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<select name="data_type">
							<option value="post"><?php _e('post',HANA_WIDGET_TEXT_DOMAIN); ?></option>
							<option value="comment"><?php _e('comment',HANA_WIDGET_TEXT_DOMAIN); ?></option>
						</select>
						<p class="description"><?php _e("Select post or comment.", HANA_WIDGET_TEXT_DOMAIN); ?></p>
					</td>
				</tr>				
				<tr>
					<th scope="row">
						<span><?php _e('Post Type', HANA_WIDGET_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<select name="post_type">
				<?php
				foreach ($post_types as $post_type) {
					if ($post_type == 'hanaboard-post')
						$post_type_name = $hanaboard_post_name;
					else
						$post_type_name = $post_type;
					$selected = isset($settings['post_types_social_share'][$post_type]) ? 'selected="selected"' : '';
					?>
								<option value="<?php echo $post_type; ?>" <?php echo $selected; ?>><?php echo $post_type_name; ?></option>
					<?php
				}
				?>
						</select>
					</td>
				</tr>		
				<tr>
					<th scope="row">
						<span><?php _e('Terms', HANA_WIDGET_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<input type="text" name="terms" value="<?php echo $settings['title_social_share']; ?>" placeholder="" />
						<p class="description"><?php _e("Select terms to show. Put 'all' to show all categories in selected post type.", HANA_WIDGET_TEXT_DOMAIN); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<span><?php _e('Skin', HANA_WIDGET_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<select name="skin">
						<?php
						$widget_skin_current = isset($settings['skin'])?$settings['skin']:'';
						foreach ($skin_list_post as $skin_key => $skin_name) {
							$selected = $widget_skin_current == $skin_key ? 'selected="selected"' : '';
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
						<span><?php _e('Count', HANA_WIDGET_TEXT_DOMAIN); ?></span>
					</th>
					<td>
						<select name="count">
						<?php
						$count = isset($settings['count'])?$settings['count']:5;
						for ($i=1; $i < 20; $i++) {
							$selected = $i == $count ? 'selected="selected"' : '';
							?>
								<option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button-primary"><?php _e('Generate', HANA_WIDGET_TEXT_DOMAIN); ?></button>
			</p>
		</form>
	</div>
	<div class="col-sm-3">
		<div id="headlines">
			<h3><?php _e('Latest news from HanaWordpress.com', HANA_WIDGET_TEXT_DOMAIN); ?></h3>
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
	
-->
	<div id="tabs-footer" class="clearfix">
		<div class="copyright">
			<em><a href="http://hanawordpress.com/hana-widget">Hana Widget</a> by <a href="http://hanawordpress.com"><?php _e('HanaWordpress',HANA_WIDGET_TEXT_DOMAIN); ?></a></em>
		</div>
	</div>
</div>
