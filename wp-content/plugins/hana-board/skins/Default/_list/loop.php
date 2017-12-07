<?php
// customizing date format
if (date( 'Yz', current_time( 'timestamp', 0 ) ) == get_the_time( 'Yz' )) {
	$date = get_the_time( 'H:i' );
} else {
	$date = get_the_date( 'm/d' );
}

// Calculate width of title col for desktop layout(col-sm)
$title_col_count = 5;
$meta_col_count = 1;
if (! (hanaboard_is_show( 'post_no' ) || hanaboard_is_board_admin()))
	$title_col_count ++;

if (! hanaboard_is_show( 'readcount' ))
	$title_col_count ++;
else
	$meta_col_count ++;

if (! hanaboard_is_show( 'like' ))
	$title_col_count ++;
else
	$meta_col_count ++;

if (! hanaboard_is_show( 'dislike' ))
	$title_col_count ++;
else
	$meta_col_count ++;

$meta_col = 12 / $meta_col_count;

$ellipsis = (hanaboard_get_option( 'title_ellipsis' )) ? 'ellipsis' : '';

?>
<div class="list-items list-items-body clearfix nopadding">

	<?php if ( hanaboard_is_board_admin() ||  hanaboard_is_show('post_no')  ) { ?>
		<div class="col-sm-1 nopadding col-no text-center hide-on-xs">
		<?php if ( hanaboard_is_board_admin() ) { ?>
			<span class="list-admin-checkbox">
			<input type="checkbox" name="hanaboard_list_admin_action[]" class="hanaboard_list_admin_action_checkbox" value="<?php the_ID(); ?>" />
		</span>
		<?php } ?>
		<?php if ( hanaboard_is_show('post_no') ) { ?>
			<span>
			<?php echo hanaboard_get_post_no(); ?>
			</span>
		<?php } ?>
		</div>
	<?php } ?>


	<div class="col-sm-<?php echo $title_col_count;?> col-xs-12 nopadding col-title <?php echo $ellipsis;?>">
		<?php if (hanaboard_has_post_subboard()) { ?>
			<span class="loop-subboard">[<?php echo hanaboard_get_post_subboard() ?>]</span>
		<?php } ?>

		<?php if(hanaboard_has_post_subcategory()) { ?>
			<span class="loop-subcategory">[<?php echo hanaboard_get_post_subcategory() ?>]</span>
		<?php } ?>

		<a class="title-link" href="<?php the_permalink(); ?>" rel="bookmark" style="<?php echo hanaboard_get_option('list_link_style'); ?>">
			<span class="article-title">
				<?php echo hanaboard_show_reply_icon($depth); ?>
				<?php $title_display_length = hanaboard_get_option('title_display_length'); ?>
				<?php echo hanaboard_substr(hanaboard_get_the_title(), $title_display_length); ?>
			</span>
			<span class="title-add-info">
				<?php
				// Show number of comments
				comments_number( '', '<span class="comments_count">(1)</span>', '<span class="comments_count">(%)</span>&nbsp;&nbsp;' );
				
				// Show private icon
				if (hanaboard_is_post_private())
					echo '<i class="fa fa-lock"></i> ';
					
					// Show image icon
				if (has_post_thumbnail())
					echo hanaboard_get_skin_img( 'icon_image.gif' );
					
					// Show attachment icon
				if (has_post_attachment())
					echo hanaboard_get_skin_img( 'icon_file.gif' );
				
				if (hanaboard_is_post_new_item())
					echo hanaboard_get_skin_img( 'icon_new.gif' );
				?>
			</span>
		</a>
	</div>
	<div class="col-sm-2 col-xs-4 nopadding col-author ellipsis">
		<?php echo hanaboard_get_the_author(get_the_ID(), false); ?>
	</div>
	<div class="col-sm-<?php echo $meta_col_count;?> col-xs-8 col-info nopadding text-right">
		<div class="col-sm-<?php echo $meta_col; ?> nopadding  item-info col-date">
			<span class="col-label">
				<i class="fa fa-clock-o"></i>
			</span> <?php the_time('m/d'); ?>
	</div>
	<?php if (hanaboard_is_show('readcount')) { ?>
	<div class="col-sm-<?php echo $meta_col; ?> nopadding item-info col-readcount ">
			<span class="col-label">
				<i class="fa fa-search"></i>
			</span> <?php echo hanaboard_get_readcount(); ?>
	</div>
	<?php } ?>

	<?php if (hanaboard_is_show('like')) { ?>
	<div class="col-sm-<?php echo $meta_col; ?> nopadding item-info col-like ">
			<span class="col-label">
				<i class="fa fa-thumbs-o-up"></i>
			</span> <?php echo hana_like_get_like_count(); ?>
	</div>
	<?php } ?>
	
	<?php if (hanaboard_is_show('dislike')) { ?>
	<div class="col-sm-<?php echo $meta_col; ?> nopadding item-info col-dislike ">
			<span class="col-label">
				<i class="fa fa-thumbs-o-down"></i>
			</span> <?php echo hana_like_get_dislike_count(); ?>
	</div>
	<?php } ?>
	</div>
</div>
