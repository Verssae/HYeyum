<?php
// customizing date format
if (date( 'Yz' ) == get_the_time( 'Yz' ))
	$date = __( 'Today', HANA_BOARD_TEXT_DOMAIN ) . get_the_time( 'H:i' );
else
	$date = get_the_date( __( 'F j, Y', HANA_BOARD_TEXT_DOMAIN ) );
	
	// Calculate width of title col for desktop layout(col-sm)
$title_col_count = 5;
$meta_col_count = 1;
if (! hanaboard_is_show( 'post_no' ))
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
<div class="list-items list-items-body clearfix nopadding list-notice"">

	<?php if (hanaboard_is_show('post_no')) { ?>
	<div class="col-sm-1 nopadding col-no text-center hide-on-xs">
		<?php _e('Notice', HANA_BOARD_TEXT_DOMAIN); ?>
	</div>
	<?php } ?>

	<div class="col-sm-<?php echo $title_col_count;?> col-xs-12 nopadding col-title <?php echo $ellipsis;?>">
		<?php
		if (hanaboard_is_show( 'sub_category' )) {
			// Show category link if the post is not in the category
			echo hanaboard_get_term_link( '[%s]' );
		}
		?>
		<a class="title-link" href="<?php the_permalink(); ?>" rel="bookmark">
			<span class="article-title">
				<?php echo hanaboard_show_reply_icon($depth); ?>
				<?php $title_display_length = hanaboard_get_option('title_display_length'); ?>
				<?php echo hanaboard_substr(hanaboard_get_the_title(), $title_display_length); ?>
			</span>
			<span class="title-add-info">
				<?php
				// Show number of comments
				comments_number( '', '[1]', '[%]' );
				
				// Show private icon
				if (hanaboard_is_post_private())
					echo '<i class="fa fa-lock"></i> ';
					
					// Show image icon
				if (has_post_thumbnail())
					echo ' <i class="fa fa-picture-o"></i> ';
					
					// Show attachment icon
				if (has_post_attachment())
					echo '<i class="fa fa-floppy-o"></i> ';
				
				?>
			</span>
		</a>
	</div>
	<div class="col-sm-2 col-xs-4 nopadding col-author ellipsis">
		<?php echo hanaboard_get_the_author_link(); ?>
	</div>
	<div class="col-sm-<?php echo $meta_col_count;?> col-xs-8 nopadding text-right">
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
