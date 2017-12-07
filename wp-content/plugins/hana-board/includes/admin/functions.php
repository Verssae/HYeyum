<?php
function hanaboard_admin_register_settings() {
	$option_name = 'hanaboard_taxonomy_fields';
}
function hanaboard_admin_tabs($page, $current = 'general') {
	$tabs = hanaboard_settings_sections( $page );
	if (! isset( $page ))
		return;
	if (! isset( $tabs ))
		return;
	echo '<ul>';
	foreach ( $tabs as $tab ) {
		if (! isset( $tab ['title'] ) || ! isset( $tab ['id'] ))
			continue;
		
		if (isset( $tab ['callback'] ))
			call_user_func( $section ['callback'], $section );
		
		$class = ($tab ['id'] == $current) ? ' nav-tab-active' : '';
		
		echo "<li><a class='$class' href='#tab-{$tab['id']}'>{$tab['title']}</a></li>";
	}
	echo '</ul>';
}

add_action( 'hanaboard_admin_top', 'wp_hana_admin_ad' );
function wp_hana_admin_ad() {
	?>
<div style="margin-bottom: 10px;">
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<!-- hanaboard-admin -->
	<ins class="adsbygoogle" style="display: block" data-ad-client="ca-pub-9117132428021929" data-ad-slot="3649028183" data-ad-format="auto"></ins>
	<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>
<?php
}
function hanaboard_do_settings_sections($page, $values = array()) {
	$sections_fields = hanaboard_settings_fields( $page );
	if (! isset( $sections_fields ))
		return;
	if ($_GET ['page'] == "hanaboard_default_tax_settings") {
		unset( $sections_fields ['general'] [0] ); // name
		unset( $sections_fields ['general'] [1] ); // slug
		unset( $sections_fields ['general'] [2] ); // parent
		unset( $sections_fields ['general'] [3] ); // description
		unset( $sections_fields ['general'] [4] ); // Connect a page
	}
	foreach ( ( array ) $sections_fields as $section_key => $section_fields ) {
		echo "<div class='section' id='tab-{$section_key}'>";
		if (isset( $section_fields ['title'] ))
			echo "<h3>{$section['title']}</h3>\n";
		
		if (isset( $section_fields ['callback'] ))
			call_user_func( $section_fields ['callback'], $section_fields );
		
		if (! is_array( $section_fields ))
			continue;
		
		echo "<table class='form-table'>";
		hanaboard_do_settings_fields( $section_fields, $values );
		echo '</table>';
		echo '</div>';
	}
	
	// if ( isset( $values['include_cats'] ) )
	
	hanaboard_include_cats_select();
}
function hanaboard_admin_build_form($page) {
	hanaboard_do_settings_sections( $page );
}
function hanaboard_do_settings_fields($fields, $values = '') {
	if (! is_array( $fields ))
		return;
	
	foreach ( $fields as $element ) {
		$default = array (
				'name' => null,
				'label' => null,
				'desc' => null,
				'id' => null,
				'class' => null,
				'css' => null,
				'type' => null,
				'default' => null,
				'js' => null,
				'alt' => null,
				'options' => null,
				'size' => '25',
				'rows' => '10',
				'cols' => '40',
				'onchange' => null,
				'required' => null 
		);
		
		$element += $default;
		
		if ($_GET ['action'] == "add") {
			$value = stripslashes( $element ['default'] );
		} else {
			$value = $values [$element ['name']];
		}
		
		if ($element ['required'] == 'required')
			$required = 'required="required"';
		else
			$required = '';
		
		?>
<tr class="form-field">
	<th scope="row">
		<label for="<?php echo $element['name']?>"><?php echo $element['label'] ?></label>
	</th>
	<td>
			<?php
		
		switch ($element ['type']) {
			case 'text' :
				?>
						<input type="text" name="term_meta[<?php echo $element['name']; ?>]" value="<?php echo $value; ?>" id="<?php echo $element['name']; ?>" style="<?php echo $element['css']; ?>" size="<?php echo $element['size']; ?>" <?php echo $required;?> />
						<?php
				break;
			case 'textarea' :
				?>
						<textarea name="term_meta[<?php echo $element['name']; ?>]" id="<?php echo $element['name']; ?>" style="<?php echo $element['css']; ?>" rows="<?php echo $element['rows']; ?>" cols="<?php echo $element['cols']; ?>"><?php echo $value; ?></textarea>
						<?php
				break;
			case 'richtext' :
				echo $value;
				wp_editor( $value, $element ['name'], array (
						'textarea_name' => $element ['name'],
						'editor_class' => $element ['name'],
						'textarea_rows' => 7,
						'dfw' => true,
						'drag_drop_upload' => true,
						'quicktags' => true,
						'media_buttons' => true 
				) );
				break;
			case 'select' :
				?>
				<select name="term_meta[<?php echo $element['name']; ?>]" id="<?php echo $element['name']; ?>" <?php echo $element['onchange']; ?>>
				  <?php foreach ($element['options'] as $key => $val) : ?>
					<option value="<?php echo $key; ?>" <?php if ( $value == $key ) echo ' selected="selected"'; ?>><?php echo $val; ?></option>
					  <?php endforeach; ?>
				</select>
				<?php
				break;
			case 'checkbox' :
				$checked = ($value) ? 'checked="checked"' : '';
				?>
				<input type="checkbox" name="term_meta[<?php echo $element['name'] ?>]" id="<?php echo $element['name'] ?>" <?php echo $checked; ?> />
				
				<?php
				break;
			case 'checkbox_group' :
				?>
				<select name="term_meta[<?php echo $element['name'] ?>]" id="<?php echo $element['name'] ?>" data-role="slider">
			<option value="Off" <?php if($value!==true || $value=="Off") echo 'selected="selected"'; ?>><?php _e('Off', HANA_BOARD_TEXT_DOMAIN); ?></option>
			<option value="On" <?php if( $value===true || $value=="On" ) echo 'selected="selected"'; ?>><?php _e('On', HANA_BOARD_TEXT_DOMAIN); ?></option>
		</select>
				<?php
				break;
			case 'skin_selector' :
				?>
				<select name="term_meta[<?php echo $element['name']; ?>]" id="<?php echo $element['name']; ?>" <?php echo $element['onchange']; ?>>
					<?php foreach ($element['options'] as $key => $val) : ?>
						<option value="<?php echo $key; ?>" <?php if ( $value == $key ) echo ' selected="selected"'; ?>><?php echo $val['name']; ?></option>
					<?php endforeach; ?>
				</select>
				<input type="submit" value="적용" class="btn button btn-primary"  />
				<span>Skin path : <?php echo $element['options'][$value]['url'] ?></span>
				<?php
				break;
			default :
				break;
		} // switch
		if ($element ['desc']) {
			?>
			<p class="description"><?php echo $element['desc']; ?></p>
			<?php
		}
		?>
	</td>
</tr>
<?php
	} // foreach
}
function hanaboard_include_cats_select($selected_cats = "") {
	$selected_cats_array = explode( ',', $selected_cats );
	echo '<div id="include-cats-modal" title="Include categories in">';
	hanaboard_category_checklist( 0, $selected_cats_array );
	echo '</div>';
}
?>