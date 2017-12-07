<?php

/*
 * The comments page for hana-comments
 */

// Do not delete these lines
if (! empty( $_SERVER ['SCRIPT_FILENAME'] ) && 'comments.php' == basename( $_SERVER ['SCRIPT_FILENAME'] ))
	die( 'Please do not load this page directly. Thanks!' );
?>
<section class="comments-wrap">
	<div id="comments" class="comments-area">
		<div class="hana-comments-hr-title hana-hr-long">
			<abbr>
				<?php
				$number = get_comments_number( $id );
				if ($number > 1) {
					$output = str_replace( '%', $number, '% Comments' );
				} elseif ($number == 0) {
					$output = '0 Comments';
				} else { // must be one
					$output = '1 Comment';
				}
				echo $output;
				?>
				</abbr>
		</div>

  <?php // do_action( 'comment_form', $post->ID ); ?>
  <?php hana_comments_form(); ?>
			<?php if (have_comments()) : ?>
			<div id="comments-list">

				<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>

				<div id="comments-nav-above" class="comments-navigation" role="navigation">
				<div class="paginated-comments-links clearfix">
						<?php
					
					paginate_comments_links( array (
							'type' => 'list',
							'prev_text' => esc_html__( 'Previous', HANA_BOARD_TEXT_DOMAIN ),
							'next_text' => esc_html__( 'Next', HANA_BOARD_TEXT_DOMAIN ) 
					) );
					?></div>
			</div>
			<!-- #comments-nav-above -->
				<?php endif; // Check for comment navigation. ?>
				<ol>
					<?php
				wp_list_comments( 'type=comment&callback=hana_custom_comments' );
				?>
				</ol>

				<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
				<div id="comments-nav-below" class="comments-navigation" role="navigation">
				<div class="paginated-comments-links comments-links-after clearfix"><?php
					
					paginate_comments_links( array (
							'type' => 'list',
							'prev_text' => esc_html__( 'Previous', HANA_BOARD_TEXT_DOMAIN ),
							'next_text' => esc_html__( 'Next', HANA_BOARD_TEXT_DOMAIN ) 
					) );
					?></div>
			</div>
			<!-- #comments-nav-below -->
				<?php endif; // Check for comment navigation. ?>


                <div class="activity-timeline"></div>
		</div>

			
			
			
			
			
			
			<?php
endif; // have_comments() ?>



		</div>
	<!-- #comments -->
</section>
