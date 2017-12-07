<?php
// customizing date format
if (date ( 'Yz', current_time ( 'timestamp', 0 ) ) == get_the_time ( 'Yz' )) {
	$date = get_the_time ( 'H:i' );
} else {
	$date = get_the_date ( 'm/d' );
}

// Calculate width of title col
$col_title = 12;
if (hanaboard_is_show ( 'date' ))
	$col_title = 9;
?>
<div class="list-items loop-notice clearfix">
	<div class="list-item-body">
		<div class="col-head">
			<div class="col-title col-xs-<?php echo $col_title; ?> nopadding">
				<a class="title_link ellipsis" href="<?php the_permalink(); ?>"
					rel="bookmark"> <span class="the_title">
						<?php echo hanaboard_show_reply_icon($depth); ?>
						<?php $title_display_length = hanaboard_get_option('title_display_length'); ?>
						<?php
						echo hanaboard_substr ( get_the_title (), $title_display_length );
						?>
					</span> <span class="title-add-info">
						<?php
						// Show private icon
						if (hanaboard_is_post_private ()) {
							echo hanaboard_get_skin_img ( 'icon_private.gif' );
						}
						
						// Show image icon
						if (has_post_thumbnail ()) {
							echo hanaboard_get_skin_img ( 'icon_image.gif' );
						}
						
						// Show attachment icon
						if (has_post_attachment ()) {
							echo hanaboard_get_skin_img ( 'icon_file.gif' );
						}
						
						// Show new icon
						if (hanaboard_is_post_new_item ())
							echo hanaboard_get_skin_img ( 'icon_new.gif' );
						
						?>
					</span>
				</a>
			</div>
			<div class="col-xs-3 nopadding text-right article-date ">
				<i class="fa fa-clock-o"></i> <?php echo $date; ?>
			</div>
		</div>
		<div class="board-article-meta">
			<div class="col-xs-5 article-author ellipsis nopadding">
				<?php echo hanaboard_get_the_author_link(); ?>
			</div>
			<div class="col-xs-7 text-right nopadding">
				<a class="meta_link" href="<?php the_permalink(); ?>" rel="bookmark">
				<?php if (hanaboard_is_show('readcount')) { ?>
				<span> <i class="fa fa-search"></i> <?php echo hanaboard_get_readcount(); ?>
				</span>
				<?php } ?>
				<?php if (hanaboard_is_show('like')) { ?>
				<span> <i class="fa fa-thumbs-o-up"></i> <?php echo hana_like_get_like_count(); ?>
				</span>
				<?php } ?>
				<?php if (hanaboard_is_show('dislike')) { ?>
				<span> <i class="fa fa-thumbs-o-down"></i> <?php echo hana_like_get_dislike_count(); ?>
				</span>
				<?php } ?>
				<span> <i class="fa fa-comments-o"></i> <?php comments_number( '0', '1', '%' ); ?>
				</span>
				</a>
			</div>
		</div>
	</div>
</div>