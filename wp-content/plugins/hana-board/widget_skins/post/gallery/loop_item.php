<?php
// customizing date format
if (date( 'Yz', current_time( 'timestamp', 0 ) ) == get_the_time( 'Yz' )) {
	$the_date = get_the_time( 'H:i' );
} else {
	$the_date = get_the_date( 'm/d' );
}
?>
<div class="hana-widget-item">
	<a href="<?php the_permalink();?>">
		<span class="widget-thumbnail">
				<?php
				if (has_post_thumbnail()) {
					the_post_thumbnail( $thumbnail_size );
				} else {
					// show no-image
					printf( '<img src="%1$s" class="attachment-thumbnail wp-post-image" alt="%2$s" title="%2$s" width="%3$s" />', $skin_url . '/images/no_image.jpg', __( 'No Image', 'hanaboard' ), $thumbnail_size [0] );
				}
				?>
		</span>
		<?php if ( $show_post_title ) { ?>
		<span class="widget-post-title">
			<?php the_title(); ?>
		</span>
		<?php } ?>
		<span class="widget-meta">
	
			<?php if ( $show_author ) { ?>
			<span class="author"><?php echo $author_name; ?></span>
			<?php } ?>
	
			<?php if ( $show_date ) { ?>
			
			<span class="date"><i class="fa fa-clock-o"></i> <?php echo $the_date; ?></span>
			<?php } ?>
	
			<?php if ( $show_num_comments ) { ?>
			<span class="num_comments"><?php comments_number('', '[1]', '[%]'); ?></span>
			<?php } ?>
	
		</span>
	</a>
</div>
