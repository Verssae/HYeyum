<?php
$selected = array (
	(get_query_var( 'search-with' ) == 'title_content') ? 'selected="selected"' : '',
	(get_query_var( 'search-with' ) == 'title') ? 'selected="selected"' : '',
	(get_query_var( 'search-with' ) == 'author') ? 'selected="selected"' : ''
);
$search_str = get_query_var( 'search-str' );
?>
<div class="form-group hanaboard-list-filter">
	<?php if (sizeof(hanaboard_get_post_subcategory()) > 0) { ?>
		<?php echo hanaboard_get_filter_subcategory(); ?>
	<?php } ?>
</div>
<div class="form-group hanaboard-list-search clearfix pull-right">
	<div class="pull-right">
		<form name="hanaboard-search-form" class="form-inline">
			<div class="hana-form-group nopadding clearfix">
				<input type="hidden" name="page_id" value="<?php echo hanaboard_get_connected_page();?>" />
				<input type="hidden" name="paged" value="1" />
				<div class="col-xs-5 nopadding text-right">
					<select name="search-with" class="selectpicker">
						<option value="title_content" <?php echo $selected[0]; ?>><?php _e('Title+Content', HANA_BOARD_TEXT_DOMAIN); ?></option>
						<option value="title" <?php echo $selected[1]; ?>><?php _e('Title', HANA_BOARD_TEXT_DOMAIN); ?></option>
						<option value="author" <?php echo $selected[2]; ?>><?php _e('Author', HANA_BOARD_TEXT_DOMAIN); ?></option>
					</select>
				</div>
				<div class="col-xs-6 nopadding">
					<input type="text" name="search-str" class="search-input" placeholder="<?php _e('Search', HANA_BOARD_TEXT_DOMAIN);?>" value="<?php echo $search_str;?>" />
				</div>
				<div class="col-xs-1 nopadding text-right">
					<button type="submit" class="btn btn-default" id="search_button">
						<i class="fa fa-search"></i>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
