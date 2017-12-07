
<?php if (get_query_var('search-str')) { ?>
<div class="hanaboard-list-search-count"><?php printf(__('\'<span>%s</span>\' Search Result - <span>%d</span> Posts', HANA_BOARD_TEXT_DOMAIN), urldecode(get_query_var('search-str')), $found_posts); ?></div>
<?php
} elseif (hanaboard_get_option( 'show_post_count' )) {
	?>
<div class="hanaboard-list-total-count"><?php printf(__('<span>%d</span> Posts', HANA_BOARD_TEXT_DOMAIN), $found_posts); ?></div>
<?php } ?>
<div class="hanaboard-list list-gallery nopadding col-xs-12">
	<div class="list-header"></div>