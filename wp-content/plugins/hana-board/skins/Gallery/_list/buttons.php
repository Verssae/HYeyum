
<div class="clearfix text-right">
	<?php if( hanaboard_is_display_write_button() ) { ?>
	<button onclick="location.href='<?php echo hanaboard_url_board_write();?>';" class="hanaboard-button btn-primary btn button hanaboard-write-button">
		<i class="fa fa-pencil-square-o"></i> <?php _e('Write', HANA_BOARD_TEXT_DOMAIN)?>
	</button>
	<?php } ?>
</div>
