<?php
// customizing date format
if (date( 'Yz', current_time( 'timestamp', 0 ) ) == get_the_time( 'Yz' )) {
	$the_date = get_the_time( 'H:i' );
} else {
	$the_date = get_the_date( 'm/d' );
}
?>

	<div class="hana-widget-item">

	<a href="<?php echo get_the_permalink($post->ID);?>">
		<span class="widget-post-title">
			<?php echo the_title(); ?>
		</span>

		<?php if ( $show_author ) { ?>
		<span class="author"><?php $author_name; ?></span>
		<?php } ?>
	
		<?php if ( $show_date && 0 ) { ?>
		<span class="date"><?php echo $the_date; ?></span>
		<?php } ?>
	
		<?php if ( $show_num_comments && 0 ) { ?>
		<span class="num_comments"><?php comments_number('', '[1]', '[%]'); ?></span>
		<?php } ?>
	</a>

	</div>
<script>
	jQuery( document ).ready(function($) {
	var html = '<div class="singer-title">[허니지]</div>';
	$(".tribe-events-category-honeyg").each(function(){
		$(this).html(html+$(".tribe-events-category-honeyg").html());
		console.log($(".tribe-events-category-honeyg").html());
	});
});
</script>