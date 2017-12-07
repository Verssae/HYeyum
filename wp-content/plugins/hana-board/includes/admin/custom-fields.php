<?php
function hanaboard_custom_fields() {
	$action = (isset( $_GET ['action'] )) ? $_GET ['action'] : '';
	
	switch ($action) {
		case 'edit' :
			hanaboard_custom_fields_edit();
			break;
		
		default :
			hanaboard_custom_fields_main();
			break;
	}
	?>
<script type="text/javascript">
		function hanaboard_show(el){

			var d = jQuery('#hanaboard_field_values_row'),
				val = jQuery(el).val();

			if (val === 'select' || val === 'checkbox') {
				d.show();
			} else {
				d.hide();
			}
		}

		//tooltip function
		jQuery(document).ready(function($) {

			$('.hanaboard-admin #type').change();

		});
	</script>
<?php
}
function hanaboard_custom_fields_main() {
	global $custom_fields, $wpdb;
	?>
<div class="wrap hanaboard-admin custom-fields">
	<div class="icon32" id="icon-options-general">
		<br>
	</div>
	<h2><?php _e('HanaWordpress HanaBoard', HANA_BOARD_TEXT_DOMAIN) ?>: <?php _e('Custom Fields', HANA_BOARD_TEXT_DOMAIN) ?></h2>

		<?php
	if (isset( $_POST ['hanaboard_add_custom'] )) {
		check_admin_referer( 'hanaboard_add', 'hanaboard_add' );
		
		// do some minimal validation
		$error = false;
		
		if ($_POST ['field'] == '') {
			$error = 'Please enter field name';
		} else if ($_POST ['label'] == '') {
			$error = 'Please enter label name';
		}
		
		if (! $error) { // no errors
		                // whatever, insert the values
			$data = array (
					'field' => 'cf_' . $_POST ['field'],
					'label' => $_POST ['label'],
					'desc' => $_POST ['help'],
					'required' => $_POST ['required'],
					'region' => $_POST ['region'],
					'order' => $_POST ['order'],
					'type' => $_POST ['type'],
					'values' => $_POST ['field_values'] 
			);
			
			$result = $wpdb->insert( $wpdb->prefix . 'hanaboard_customfields', $data, array (
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s' 
			) );
			
			// if row inserted
			if ($result) {
				echo '<div class="updated"><p><strong>Field added</strong></p></div>';
			} else {
				echo '<div class="error"><p><strong>Something went wrong</strong></p></div>';
			}
		} else { // we got some error
			echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
		}
	}
	?>

		<form action="" method="post" style="margin-top: 20px;">

			<?php wp_nonce_field('hanaboard_add', 'hanaboard_add'); ?>

			<table class="widefat meta" style="width: 850px">
			<thead>
				<tr>
					<th scope="col" colspan="2" style="font-size: 14px;">Add New Custom Field</th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="field"><?php _e('Field Name', HANA_BOARD_TEXT_DOMAIN) ?></label>
					</td>
					<td>
						<input type="text" size="25" style="" id="field" value="" name="field" />
						<span class="description"><?php _e('Name without space. Will be used to store the value in this custom field', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="label"><?php _e('Label', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<input type="text" size="25" style="" id="label" value="" name="label" />
						<span class="description"><?php _e('This will be used as your input fields title', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="help"><?php _e('Help Text', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<input type="text" size="25" style="" id="help" value="" name="help" />
						<span class="description"><?php _e('Text will be shown to user as help text', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="required"><?php _e('Required', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<select id="required" name="required">
							<option value="no"><?php _e('No', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="yes"><?php _e('Yes', HANA_BOARD_TEXT_DOMAIN); ?></option>
						</select>
						<span class="description"><?php _e('A validation criteria. User must provide input in that field', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="region"><?php _e('Region', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<select id="region" name="region">
							<option value="top"><?php _e('Top', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="description"><?php _e('Before Description', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="tag"><?php _e('After Description', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="bottom"><?php _e('Bottom', HANA_BOARD_TEXT_DOMAIN); ?></option>
						</select>
						<span class="description"><?php _e('Where do you want to show this input field?', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="order"><?php _e('Order', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<input name="order" value="" id="order" style="" size="2" type="text">
						<span class="description"><?php _e('Which order this input field will show in a region', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="type"><?php _e('Type', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<select name="type" id="type" onchange="hanaboard_show(this)">
							<option value="text"><?php _e('Text Box', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="textarea"><?php _e('Text Area', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="select"><?php _e('Dropdown', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="checkbox"><?php _e('Checkbox', HANA_BOARD_TEXT_DOMAIN); ?></option>
						</select>
						<span class="description"></span>
					</td>
				</tr>
				<tr valign="top" id="hanaboard_field_values_row" style="display: none;">
					<td scope="row" class="label">
						<label for="hanaboard_field_values"><?php _e('Values', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<textarea name="field_values" id="hanaboard_field_values" cols="30"></textarea>
						<span class="description">
							<br><?php _e('This will be used as option fields. Please separate values with comma', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<input name="hanaboard_add_custom" type="submit" class="button-primary" value="<?php _e( 'Add Field', 'hanaboard' ) ?>" style="margin-top: 10px;" />
	</form>
	<h2>Custom Fields</h2>

		<?php
	// delete service
	if (isset( $_REQUEST ['action'] ) && $_REQUEST ['action'] == "del") {
		check_admin_referer( 'hanaboard_del' );
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}hanaboard_customfields` WHERE `id`={$_REQUEST['id']};" );
		echo '<div class="updated fade" id="message"><p><strong>Field Deleted.</strong></p></div>';
	}
	?>

		<table class="widefat meta" style="margin-bottom: 20px;">
		<thead>
			<tr>
				<th scope="col">Name</th>
				<th scope="col">Type</th>
				<th scope="col">Description</th>
				<th scope="col">Position</th>
				<th scope="col">Order</th>
				<th scope="col">Action</th>
			</tr>
		</thead>
			<?php
	$fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}hanaboard_customfields ORDER BY `region` DESC", OBJECT );
	if ($wpdb->num_rows > 0) :
		
		$type = array (
				'text' => 'Text Box',
				'textarea' => 'Text Area',
				'select' => 'Dropdown',
				'checkbox' => 'Checkbox' 
		);
		$position = array (
				'top' => 'Top',
				'description' => 'Before Description',
				'tag' => 'After Description',
				'bottom' => 'Bottom' 
		);
		
		$count = 0;
		foreach ( $fields as $row ) :
			
			if (! hanaboard_starts_with( $row->field, 'cf_' )) {
				continue;
			}
			?>
					<tr valign="top" <?php echo ( ($count % 2) == 0) ? 'class="alternate"' : ''; ?>>
			<td><?php echo stripslashes($row->label ); ?></td>
			<td><?php echo $type[$row->type]; ?></td>
			<td><?php echo stripslashes($row->desc ); ?></td>
			<td><?php echo $position[$row->region]; ?></td>
			<td><?php echo $row->order; ?></td>
			<td>
				<a href="admin.php?page=hanaboard_custom_fields&action=edit&id=<?php echo $row->id; ?>">
					<img src="<?php echo plugins_url( '', dirname( __FILE__ ) ); ?>/images/edit.png">
				</a>
				<a href="<?php echo wp_nonce_url( "admin.php?page=hanaboard_custom_fields&action=del&id=" . $row->id, 'hanaboard_del' ) ?>" onclick="return confirm('Are you sure to delete this field?');">
					<img src="<?php echo plugins_url( '', dirname( __FILE__ ) ); ?>/images/del.png">
				</a>
			</td>
		</tr>
					<?php
			
			$count ++;
		endforeach
		;
		?>
			<?php else : ?>
				<tr>
			<td colspan="5">Nothing Found</td>
		</tr>
			<?php endif; ?>

		</table>
</div>
<?php
}
function hanaboard_custom_fields_edit() {
	global $wpdb, $custom_fields;
	
	$id = intval( $_GET ['id'] );
	?>
<div class="wrap hanaboard-admin">

		<?php
	// update the fields
	if (isset( $_POST ['hanaboard_edit_custom'] )) {
		
		check_admin_referer( 'hanaboard_edit', 'hanaboard_edit' );
		
		$error = false;
		
		if ($_POST ['field'] == '') {
			$error = 'Please enter field name';
		} else if ($_POST ['label'] == '') {
			$error = 'Please enter label name';
		}
		
		if (! $error) { // no errors
		                // whatever, insert the values
			if (! hanaboard_starts_with( $_POST ['field'], 'cf_' )) {
				$_POST ['field'] = 'cf_' . $_POST ['field'];
			}
			
			$data = array (
					'field' => $_POST ['field'],
					'label' => $_POST ['label'],
					'desc' => $_POST ['help'],
					'required' => $_POST ['required'],
					'region' => $_POST ['region'],
					'order' => $_POST ['order'],
					'type' => $_POST ['type'],
					'values' => $_POST ['field_values'] 
			);
			
			$result = $wpdb->update( $wpdb->prefix . 'hanaboard_customfields', $data, array (
					'id' => $id 
			), array (
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s' 
			), array (
					'%d' 
			) );
			
			// if row inserted
			if ($result) {
				echo '<div class="updated"><p><strong>Field Updated</strong></p></div>';
			} else {
				echo "<div class='error'><p><strong>Something went wrong or you didn't changed anything</strong></p></div>";
			}
		} else { // we got some error
			echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
		}
	} // finished updating
	  // now show it
	$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}hanaboard_customfields WHERE `id`=$id" );
	?>


		<?php // if ( $row ) { ?>
			<form action="" method="post" style="margin-top: 20px;">
				<?php wp_nonce_field('hanaboard_edit', 'hanaboard_edit'); ?>
				<table class="widefat meta" style="width: 850px">
			<thead>
				<tr>
					<th scope="col" colspan="2" style="font-size: 14px;">Edit Custom Field</th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="field"><?php _e('Field Name', HANA_BOARD_TEXT_DOMAIN) ?></label>
					</td>
					<td>
						<input type="text" size="25" style="" id="field" value="<?php echo esc_attr( $row->field ); ?>" name="field" />
						<span class="description"><?php _e('Name without space. Will be used to store the value in this custom field', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="label"><?php _e('Label', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<input type="text" size="25" style="" id="label" value="<?php echo esc_attr( $row->label ); ?>" name="label" />
						<span class="description"><?php _e('This will be used as your input fields title', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="help"><?php _e('Help Text', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<input type="text" size="25" style="" id="help" value="<?php echo esc_attr( $row->desc ); ?>" name="help" />
						<span class="description"><?php _e('Text will be shown to user as help text', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="required"><?php _e('Required', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<select id="required" name="required">
							<option value="no" <?php selected( $row->required, 'no' ); ?>><?php _e('No', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="yes" <?php selected( $row->required, 'yes' ); ?>><?php _e('Yes', HANA_BOARD_TEXT_DOMAIN); ?></option>
						</select>
						<span class="description"><?php _e('A validation criteria. User must provide input in that field', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="region"><?php _e('Region', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<select id="region" name="region">
							<option value="top" <?php selected( $row->region, 'top' ); ?>><?php _e('Top', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="description" <?php selected( $row->region, 'description' ); ?>><?php _e('Before Description', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="tag" <?php selected( $row->region, 'tag' ); ?>><?php _e('After Description', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="bottom" <?php selected( $row->region, 'bottom' ); ?>><?php _e('Bottom', HANA_BOARD_TEXT_DOMAIN); ?></option>
						</select>
						<span class="description"><?php _e('Where do you want to show this input field?', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="order"><?php _e('Order', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<input name="order" value="<?php echo esc_attr( $row->order ); ?>" id="order" style="" size="2" type="text">
						<span class="description"><?php _e('Which order this input field will show in a region', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row" class="label">
						<label for="type"><?php _e('Type', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<select name="type" id="type" onchange="hanaboard_show(this)">
							<option value="text" <?php selected( $row->type, 'text' ); ?>><?php _e('Text Box', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="textarea" <?php selected( $row->type, 'textarea' ); ?>><?php _e('Text Area', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="select" <?php selected( $row->type, 'select' ); ?>><?php _e('Dropdown', HANA_BOARD_TEXT_DOMAIN); ?></option>
							<option value="checkbox" <?php selected( $row->type, 'checkbox' ); ?>><?php _e('Checkbox', HANA_BOARD_TEXT_DOMAIN); ?></option>
						</select>
						<span class="description"></span>
					</td>
				</tr>
				<tr valign="top" id="hanaboard_field_values_row" style="display: none;">
					<td scope="row" class="label">
						<label for="hanaboard_field_values"><?php _e('Values', HANA_BOARD_TEXT_DOMAIN); ?></label>
					</td>
					<td>
						<textarea name="field_values" id="hanaboard_field_values" cols="30"><?php echo esc_textarea($row->values ); ?></textarea>
						<span class="description">
							<br><?php _e('This will be used as option fields. Please separate values with comma', HANA_BOARD_TEXT_DOMAIN); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<input name="hanaboard_edit_custom" type="submit" class="button-primary" value="<?php _e( 'Update Field', 'hanaboard' ) ?>" style="margin-top: 10px;" />
	</form>
		<?php //} else { ?>
			<h2><?php _e('Nothing found', HANA_BOARD_TEXT_DOMAIN); ?></h2>
		<?php //} ?>

	</div>
<?php
}
