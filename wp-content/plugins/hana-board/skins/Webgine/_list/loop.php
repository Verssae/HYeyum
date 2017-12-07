<?php

// Ignore notice
if ( ! hanaboard_is_post_notice() ) {

$col_xs = 12;
$col_md = 12;
// customizing date format
if (date( 'Yz', current_time( 'timestamp', 0 ) ) == get_the_time( 'Yz' )) {
	$date = get_the_time( 'H:i' );
} else {
	$date = get_the_date( 'm/d' );
}

$ellipsis = (hanaboard_get_option( 'title_ellipsis' )) ? 'ellipsis' : '';
?>
<div class="list-items clearfix">
	<?php if (has_post_thumbnail()) { ?>
	<div class="col-xs-4 col-md-3 hanaboard-left-thumbnail nopadding-left">
		<a href="<?php hanaboard_the_permalink(); ?>" rel="bookmark">
		<?php the_post_thumbnail(hanaboard_get_option('thumbnail_size')); ?>
		</a>
	</div>
	<?php $col_xs = 8; $col_md = 9; ?>
	<?php } ?>
	<div class="col-xs-<?php echo $col_xs; ?> col-md-<?php echo $col_md; ?> nopadding">
		<div class="article-title <?php echo $ellipsis;?>">
			<a href="<?php the_permalink(); ?>">
	   		<?php hanaboard_the_title(); ?>
			<?php 
				// Show private icon
				if (hanaboard_is_post_private())
					echo '<i class="fa fa-lock"></i> ';
			?>	   		
	   		<?php if (hanaboard_is_post_new_item()) echo hanaboard_get_skin_img( 'icon_new.gif' ); ?>
			</a>
		</div>
		<div class="hanaboard-article-meta ">
			<?php if( hanaboard_is_show('sub_category')) { ?>
			<div class="col-xs-12 nopadding article-category">
				<i class="fa fa-tags"></i><?php echo hanaboard_get_term_link(); ?>
			</div>
			<?php } ?>
			<div class="col-xs-8 nopadding article-author">
			<?php echo hanaboard_get_the_author(get_the_ID(), false); ?>
			</div>
			<div class="col-xs-4 nopadding pull-right text-right article-date">
				<i class="fa fa-clock-o"></i> <?php echo $date; ?>
			</div>
		</div>
		<div class=" hanaboard-article-excerpt">
			<div class="col-xs-12 nopadding text-justify  ">
				<a href="<?php the_permalink(); ?>">
				<?php hanaboard_the_excerpt(); ?>
				</a>
			</div>
		</div>
		<div class=" hanaboard-article-meta-bottom ">
			<div class="col-xs-12 nopadding text-right">
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
					<i class="fa fa-comments-o"></i> <?php comments_number( '0', '1', '%' ); ?>
			</span>
			</div>
		</div>
	</div>
</div>

<?php } ?>
