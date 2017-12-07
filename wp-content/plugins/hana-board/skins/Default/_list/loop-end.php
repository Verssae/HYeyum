</div>
<?php if(hanaboard_is_board_admin()) { ?>
<form name="hanaboard_list_admin_action_form" class="hanaboard_list_admin_action_form">
	<input type="hidden" name="checked_post_ids" />
	<span><?php _e('Checked Articles : ',HANA_BOARD_TEXT_DOMAIN); ?></span>
	<select name="hanaboard_list_admin_action">
		<option value=""><?php _e('--Select--',HANA_BOARD_TEXT_DOMAIN); ?></option>
		<option value="move"><?php _e('Move to another board',HANA_BOARD_TEXT_DOMAIN); ?></option>
		<option value="trash"><?php _e('Move to trash',HANA_BOARD_TEXT_DOMAIN); ?></option>
	</select>
	<span id="hanaboard_list_admin_action_target_wrapper" style="display:none; ">
	<?php echo hanaboard_dropdown_category_list(array('childof'=>null,'include'=>null,'id'=>'hanaboard_list_admin_action_target','exclude'=>hanaboard_get_current_term_id(),'selected'=>null)); ?>
	</span>	
	<button type="button" class="hanaboard-button button button-secondary" id="hanaboard_list_admin_action_button" data-term-id="<?php echo hanaboard_get_current_term_id(); ?>">
		<?php _e('Move',HANA_BOARD_TEXT_DOMAIN);?>
	</button>
	<span id="hanaboard_list_admin_action_spinner" style="display:none;">
		<i class="fa fa-spinner fa-pulse"></i>
	</span>
</form>
<?php } ?>
