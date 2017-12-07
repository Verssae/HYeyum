<?php
// customizing date format
if (date ( 'Yz', current_time ( 'timestamp', 0 ) ) == get_the_time ( 'Yz' )) {
	$the_date = get_the_time ( 'H:i' );
} else {
	$the_date = get_the_date ( 'm/d' );
}
$col_xs = 12;
$col_md = 12;
?>
<div class="hana-widget-item row">
<?php if (has_post_thumbnail()) { ?>
	<div class="col-xs-4 col-md-3 widget-item-image nopadding-left">
		<a href="<?php the_permalink();?>">
			<?php the_post_thumbnail( $thumbnail_size ); ?>
		</a>
	</div>
	<?php $col_xs = 8; $col_md = 9; ?>
<?php } ?>
	<div class="widget-item-body col-xs-<?php echo $col_xs; ?> col-md-<?php echo $col_md; ?> nopadding">
		<div class="row item-title ">
			<a class="title_link ellipsis"
				href="<?php echo get_the_permalink(); ?>" rel="bookmark">
				<?php the_title(); ?>
			</a>
		</div>
		<div class="row nopadding item-meta">
			<a class="meta_link" href="<?php echo get_the_permalink(); ?>"
				rel="bookmark"> <span class="col-xs-6 nopadding ellipsis author">
					<?php echo $author_name; ?>
				</span>
			<?php if ( $show_category ) { ?>
				<span>
					<?php echo $term_link; ?>
				</span>
			<?php } ?>
			<?php if ( $show_readcount ) { ?>
    			<span class="pull-right col-readcount text-right"> <i
					class="fa fa-search"></i>
					<?php echo hanaboard_get_readcount(); ?>
				</span>
			<?php } ?>
		
	  		<?php if ( $show_num_comments ) { ?>
	  			<span class="pull-right col-num-comments text-right"> <i
					class="fa fa-comments-o"></i>
	  		  		<?php comments_number('0', '1', '%'); ?>
	  			</span>
	  		<?php } ?>
			<?php if ( $show_like ) { ?>
				<span class="pull-right col-like text-right"> <i
					class="fa fa-thumbs-o-up"></i>
					<?php echo intval(hana_like_get_like_count()); ?>
				</span>
			<?php } ?>
			<?php if ( $show_date ) { ?>
				<span
				class="nopadding list-col item-info col-date pull-right text-right">
					<i class="fa fa-clock-o"></i>
					<?php echo $the_date; ?>
				</span>
			<?php } ?>
			</a>
		</div>
	</div>
</div>
