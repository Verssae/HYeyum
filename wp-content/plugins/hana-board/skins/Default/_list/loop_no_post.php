
<div class="hanaboard-no-post">
	<?php
	printf( __( 'No post found.', HANA_BOARD_TEXT_DOMAIN ) );
	do_action( 'hanaboard_list_posts_nopost', $userdata->ID, $post_type_obj );
	?>
</div>
