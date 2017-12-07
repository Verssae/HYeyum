<?php
// Calculate width of title col
$title_col_count = 5;
if (! hanaboard_is_show( 'post_no' ))
	$title_col_count ++;
if (! hanaboard_is_show( 'readcount' ))
	$title_col_count ++;
if (! hanaboard_is_show( 'like' ))
	$title_col_count ++;
if (! hanaboard_is_show( 'dislike' ))
	$title_col_count ++;
?>
<div class="hanaboard-list list-default nopadding ">
	<div class="list-header list-items clearfix nopadding">
		<?php if ( hanaboard_is_show('post_no') || hanaboard_is_board_admin() ) { ?>
		<div class="col-sm-1 nopadding list-col col-no">
		<?php if( hanaboard_is_board_admin() ) { ?>
			<span class="list-admin-checkbox">
				<input type="checkbox" name="hanaboard_list_admin_action_all" id="hanaboard_list_admin_action_all" />
			</span>
		<?php } ?>
		<?php if ( hanaboard_is_show('post_no')) { ?>
			<span>
			<?php _e('No.', HANA_BOARD_TEXT_DOMAIN); ?>
			</span>
		<?php } ?>
		</div>
		<?php } ?>

		<div class="col-sm-<?php echo $title_col_count;?> nopadding list-col col-title">
			<span><?php _e('Title', HANA_BOARD_TEXT_DOMAIN); ?></span>
		</div>
		<div class="col-sm-2 nopadding list-col col-author">
			<span><?php _e('Author', HANA_BOARD_TEXT_DOMAIN); ?></span>
		</div>
		<div class="col-sm-1 nopadding list-col col-date">
			<span><?php _e('Date', HANA_BOARD_TEXT_DOMAIN); ?></span>
		</div>

		<?php if (hanaboard_is_show('readcount')) { ?>
		<div class="col-sm-1 nopadding list-col col-readcount">
			<span><?php _e('Read', HANA_BOARD_TEXT_DOMAIN); ?></span>
		</div>
		<?php } ?>
		<?php if (hanaboard_is_show('like')) { ?>
		<div class="col-sm-1 nopadding list-col col-like">
			<span><?php _e('Like', HANA_BOARD_TEXT_DOMAIN); ?></span>
		</div>
		<?php } ?>
		<?php if (hanaboard_is_show('dislike')) { ?>
		<div class="col-sm-1 nopadding list-col col-like">
			<span><?php _e('Dislike', HANA_BOARD_TEXT_DOMAIN); ?></span>
		</div>
		<?php } ?>
	</div>
