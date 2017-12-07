<?php
if (! class_exists( 'WP_List_Table' ))
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
class Admin_HanaBoard_List_Table extends WP_List_Table {
	function __construct() {
		parent::__construct( array (
				'plural' => 'hanaboard',
				'singular' => 'hanaboard-post',
				'ajax' => false,
				'screen' => null 
		) );
	}
	function prepare_items() {
		global $wp_error;
		$columns = $this->get_columns();
		$hidden = array ();
		$sortable = $this->get_sortable_columns();
		
		if ($this->current_action()) {
			$tag_ID = isset( $_REQUEST ['tag_ID'] ) ? $_REQUEST ['tag_ID'] : '';
			$the_term = get_term_by( 'id', $tag_ID, HANA_BOARD_TAXONOMY );
			if (is_object( $the_term )) {
				if ($this->current_action() == 'delete') {
					if ($the_term->count) {
						$wp_error->message = __( 'Delete failed. Move or delete all posts involved.', HANA_BOARD_TEXT_DOMAIN );
					} else {
						// TBD delete taxonomy option
						wp_delete_term( $tag_ID, HANA_BOARD_TAXONOMY ); // delete term
						$wp_error->message = __( 'Board deleted.', HANA_BOARD_TEXT_DOMAIN );
					}
				}
			}
		}
		
		$this->_column_headers = array (
				$columns,
				$hidden,
				$sortable 
		);

		$terms = get_terms( HANA_BOARD_TAXONOMY, array (
				'hide_empty' => 0,
		) );
		$totalCount = sizeof($terms);

		$current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
		if (! isset( $per_page ))
			$per_page = 10;
		$terms = array_slice( $terms, (($current_page - 1) * $per_page), $per_page );

		$this->set_pagination_args( array(
			'per_page'    => $per_page,
			'total_items' => $totalCount,
			'total_pages' => ceil( $totalCount / $per_page )
		) );

		$this->items = $terms;
	}
	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

	function get_columns() {
		$columns = array (
				'name' => __( 'Name', HANA_BOARD_TEXT_DOMAIN ),
				'slug' => __( 'Slug', HANA_BOARD_TEXT_DOMAIN ),
				'shortcode' => __('Shortcode', HANA_BOARD_TEXT_DOMAIN),
				'skin' => __( 'Skin', HANA_BOARD_TEXT_DOMAIN ),
				'count' => __( 'Count', HANA_BOARD_TEXT_DOMAIN ) 
		);
		return $columns;
	}
	function column_title($item) {
		$actions = array (
				'admin' => sprintf( '<a href="admin.php?page=%s&action=%s&tag_ID=%s">%s</a>', 'hanaboard_manage_tax', 'edit', $item->term_id, __( 'Edit', HANA_BOARD_TEXT_DOMAIN ) ),
				'delete' => sprintf( '<a href="admin.php?page=%s&action=%s&tag_ID=%s" class="deleteBoardLink">%s</a>', $_REQUEST ['page'], 'delete', $item->term_id, __( 'Delete', HANA_BOARD_TEXT_DOMAIN ) ),
				'view' => sprintf( '<a href="%s" target=_blank>%s</a>', hanaboard_get_the_term_link( $item->term_id ), __( 'View', HANA_BOARD_TEXT_DOMAIN ) ) 
		);
		return sprintf( '%1$s %2$s', $item->name, $this->row_actions( $actions ) );
	}
	function column_name($item) {
	    ob_start();
	    ?>
        <?php
		$actions = array (
			'admin' => sprintf( '<a href="admin.php?page=%s&action=%s&tag_ID=%s">%s</a>', 'hanaboard_manage_tax', 'edit', $item->term_id, __( 'Edit', HANA_BOARD_TEXT_DOMAIN ) ),
			'delete' => sprintf( '<a href="admin.php?page=%s&action=%s&tag_ID=%s" class="deleteBoardLink">%s</a>', $_REQUEST ['page'], 'delete', $item->term_id, __( 'Delete', HANA_BOARD_TEXT_DOMAIN ) ),
			'view' => sprintf( '<a href="%s" target=_blank>%s</a>', hanaboard_get_the_term_link( $item->term_id ), __( 'View', HANA_BOARD_TEXT_DOMAIN ) )
		);
		echo sprintf( '%1$s %2$s', $item->name, $this->row_actions( $actions ) );

        $data = ob_get_clean();
		return $data;
	}
	function column_parent($item) {
		$parent_term = get_term_by( 'id', $item->parent, HANA_BOARD_TAXONOMY );
		if (is_object( $parent_term ))
			return $parent_term->name;
		else
			return '';
	}
	function column_slug($item) {
		$slug_link = sprintf( '<a href="%s" target="_blank">%s<a/>', hanaboard_get_the_term_link( $item->term_id ), urldecode( $item->slug ) );
		return $slug_link;
	}
	function column_shortcode($item) {
		$widgetAttr = (strtolower($this->column_skin($item)) == 'gallery') ? 'columns="2" ' : '';
		ob_start();
		?>
			<?php _e('Board' , HANA_BOARD_TEXT_DOMAIN) ?>
			<span class="message">(<?php echo sprintf( __('Connected page : <a href="%s">%s</a>', HANA_BOARD_TEXT_DOMAIN), '/wp-admin/post.php?post='.hanaboard_get_connected_page($item->term_id).'&action=edit', get_the_title(hanaboard_get_connected_page($item->term_id))) ?>)</span>
			<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly" value='[hana_board board="<?php echo urldecode($item->slug) ?>"]' class="large-text code" /></span>
			<br>
			<?php _e('Widget', HANA_BOARD_TEXT_DOMAIN) ?>
			<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly" value='[hana_board_widget terms="<?php echo urldecode($item->slug) ?>" skin="<?php echo strtolower($this->column_skin($item)) ?>" <?php echo $widgetAttr ?> title="<?php echo $item->name ?>"]' class="large-text code" /></span>
		<?php
		$data = ob_get_clean();
		return $data;
	}
	function column_skin($item) {
		return hanaboard_get_option( 'board_skin', $item->term_id );
	}
	function column_count($item) {
		//todo : 비밀글 비회원글 카운트 포함
		return $item->count;
	}
	function column_default($item, $column_name) {
		return print_r( $item, true );
	}
}
?>