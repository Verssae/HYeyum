
<h2><?php echo sprintf(__('Common Settings - %s', HANA_BOARD_TEXT_DOMAIN), $tax_name); ?></h2>
<p class="description">
	<?php ($_GET['page'] == "hanaboard_general_settings") ? _e('General Settings: These settings will be applied .', HANA_BOARD_TEXT_DOMAIN) : ''?>
</p>
<?php do_action('hanaboard_admin_top'); ?>
<form name="board_tax_setting" id="board_tax_setting_form" method="POST">
	<div class="form-wrap">
		<?php
		wp_nonce_field( 'hanaboard-general-settings-form', 'hanaboard-general-settings-nonce' );

		$option_values = get_option( 'hanaboard-general-settings' );

		if (isset( $option_values ) && is_object( $option_values )) {
			$values ['captcha'] = $option_values->captcha;
			$values ['upload_filename_fix'] = $option_values->upload_filename_fix;
		}
		?>
		<div id="hanaboard-admin-tabs">
			<?php
			hanaboard_admin_tabs( 'hanaboard_common_settings' );
			hanaboard_do_settings_sections( 'hanaboard_common_settings', $values );
			?>
		</div>
		<p>
			<input type="button" id="rearrange_post_no" data-tax-id="<?php echo $_GET['tag_ID'];?>" class="button button-secondary" value="<?php _e('Rearrange Post No', HANA_BOARD_TEXT_DOMAIN);?>">
		</p>
		<p>
			<input type="hidden" name="hanaboard_general_options_submit" value="yes" />
			<input type="hidden" name="hanaboard_action" value="<?php echo $_GET['action'];?>" />
		</p>
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save', HANA_BOARD_TEXT_DOMAIN);?>" />
	</div>
</form>
<?php //do_action('hanaboard_admin_bottom'); ?>