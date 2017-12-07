<?php
// customizing date format
if (date('Yz', current_time( 'timestamp', 0 )) == get_comment_date('Yz', $comment->comment_ID)) {
	$the_date = get_comment_date('H:i', $comment->comment_ID);
} else {
	$the_date = get_comment_date('m/d', $comment->comment_ID);
}
$author_name = get_the_author_meta('display_name', $comment->user_id);
?>

	<div class="hana-widget-item">

	<a href="<?php echo get_the_permalink($comment->comment_post_ID); ?>#comments">
		<span class="widget-comment-title">
			<?php echo $comment->comment_content; ?>
		</span>

		<?php if ( $show_author ) { ?>
		<span class="author"><?php $comment->comment_author; ?></span>
		<?php } ?>
	
		<?php if ( $show_date ) { ?>
		<span class="date"><?php echo $the_date; ?></span>
		<?php } ?>
	</a>

	</div>

