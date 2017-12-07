
<h2><?php echo sprintf(__('Board Settings - %s', HANA_BOARD_TEXT_DOMAIN), $tax_name); ?></h2>
<p class="description">
<?php ($_GET['page'] == "hanaboard_default_tax_settings") ? _e('Default Board Settings: These settings will be applied as default values when you create a new board.', HANA_BOARD_TEXT_DOMAIN) : ''?>
</p>
<?php do_action('hanaboard_admin_top'); ?>
<?php
$skin_admin_settings_path = hanaboard_get_current_skin_dir ($tax_id) . 'functions-admin.php';
if(file_exists($skin_admin_settings_path)) {
	include_once $skin_admin_settings_path;
}
?>
<form name="board_tax_setting" id="board_tax_setting_form" method="POST">
	<div class="form-wrap">
		<?php
		wp_nonce_field( 'hanaboard-tax-settings-form', 'hanaboard-tax-settings-nonce' );
		
		$values = get_option( HANA_BOARD_TAX_META_HEADER . $tax_id );
		if ($_GET ['action'] == "add")
			$values ['connect_page'] = '';
		
		if (isset( $tax ) && is_object( $tax )) {
			$values ['name'] = $tax->name;
			$values ['slug'] = urldecode( $tax->slug );
			$values ['parent'] = $tax->parent;
			$values ['description'] = $tax->description;
			if ($values ['include_cats'] == '')
				$values ['include_cats'] = $tax_id;
		}
		?>
		<div id="hanaboard-admin-tabs">
			<?php
			hanaboard_admin_tabs( 'hanaboard_tax_options' );
			hanaboard_do_settings_sections( 'hanaboard_tax_options', $values );
			?>
		</div>
		<p>
			<input type="button" id="rearrange_post_no" data-tax-id="<?php echo $_GET['tag_ID'];?>" class="button button-secondary" value="<?php _e('Rearrange Post No', HANA_BOARD_TEXT_DOMAIN);?>">
		</p>
		<p>
			<input type="hidden" name="hanaboard_tax_options_submit" value="yes" />
			<input type="hidden" name="hanaboard_action" value="<?php echo $_GET['action'];?>" />
			<input type="hidden" name="hanaboard_tax" value="<?php echo $tax_id;?>" />
		</p>
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save', HANA_BOARD_TEXT_DOMAIN);?>" />
	</div>
</form>
<?php //do_action('hanaboard_admin_bottom'); ?>