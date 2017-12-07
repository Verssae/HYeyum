<?php
// Ignore notice
if ( ! hanaboard_is_post_notice() ) {
	

// customizing date format
if (date( 'Yz', current_time( 'timestamp', 0 ) ) == get_the_time( 'Yz' )) {
	$date = get_the_time( 'H:i' );
} else {
	$date = get_the_date( 'm/d' );
}

$ellipsis = (hanaboard_get_option( 'title_ellipsis' )) ? 'ellipsis' : '';
?>
<div class=" list-items">
	<div class="col-xs-12 board-article-thumb">
		<a href="<?php the_permalink(); ?>">
		<?php
		if (has_post_thumbnail())
			the_post_thumbnail( hanaboard_get_option( 'thumbnail_size' ) );
		else
			echo hanaboard_get_skin_img( 'no_image.jpg', $args );
		?>
		</a>
	</div>
	<div class="col-xs-12 nopadding board-article-title <?php echo $ellipsis;?>">
		<a href="<?php the_permalink(); ?>">
		<?php 
			// Show private icon
			if (hanaboard_is_post_private())
				echo '<i class="fa fa-lock"></i> ';
		?>
	    <?php hanaboard_the_title(); ?>
	    <?php
					if (hanaboard_is_post_new_item())
						echo hanaboard_get_skin_img( 'icon_new.gif' );
					?>
		</a>
	</div>
	<div class=" ">
		<div class="col-xs-7 row-eq-height board-article-meta nopadding article-author ellipsis"><?php echo hanaboard_get_the_author_link(); ?></div>
		<div class="col-xs-5 row-eq-height board-article-meta nopadding text-right article-date">
			<i class="fa fa-clock-o"></i> <?php echo $date; ?></div>
	</div>
	<?php if( hanaboard_is_show('sub_category')) { ?>
	<div class=" board-article-meta">
		<div class="col-xs-12 article-category">
			<i class="fa fa-tags"></i><?php echo hanaboard_get_term_link(); ?>
		</div>
	</div>
	<?php } ?>	
	<div class=" board-article-meta pull-right text-right">
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

<?php } // ignore notice ?>