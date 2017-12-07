<?php
// customizing date format
if (date('Yz', current_time( 'timestamp', 0 )) == get_comment_date('Yz', $comment->comment_ID)) {
	$the_date = get_comment_date('H:i', $comment->comment_ID);
} else {
	$the_date = get_comment_date('m/d', $comment->comment_ID);
}
?>
<div class="hana-widget-item">
	<div class="widget-item-image">
		<?php echo get_avatar( $comment->user_id ,40);?>
	</div>
	<div class="widget-item-body">
		<div class="row item-title ">
			<a class="title_link ellipsis" href="<?php echo $comment_link; ?>#comments" rel="bookmark">
				<?php echo $comment->comment_content; ?>
	</a>
		</div>
		<div class="row item-meta">
			<a class="meta_link" href="<?php echo $comment_link; ?>#comments" rel="bookmark">
				<span class="col-xs-6 nopadding ellipsis author">
					<?php echo $author_name; ?>
				</span>

			<?php if ( $show_like ) { ?>
				<span class="pull-right col-like text-right">
					<i class="fa fa-thumbs-o-up"></i>
					<?php echo intval(hana_like_get_like_count()); ?>
				</span>
			<?php } ?>
			<?php if ( $show_date ) { ?>
				<span class="nopadding list-col item-info col-date pull-right text-right">
					<i class="fa fa-clock-o"></i>
					<?php echo $the_date; ?>
				</span>
			<?php } ?>
			</a>
		</div>
	</div>
</div>
