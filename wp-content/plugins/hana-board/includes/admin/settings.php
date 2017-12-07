<?php
if (! defined ( 'HANAWORDPRESS_MENU' ))
	define ( 'HANAWORDPRESS_MENU', 'hanawordpress-main' );

/**
 * WordPress settings API class
 *
 * @author HanaWordpress
 */
class HanaBoard_Settings {
	private $settings_api;
	private $page;
	function __construct() {		
		// add_action('welcome_panel', array(&$this,
		// 'dashboard_widget_function'));
		add_action ( 'wp_dashboard_setup', array (
				&$this,
				'add_dashboard_widget' 
		) );
		
		add_action ( 'admin_init', array (
				&$this,
				'admin_init' 
		) );
		add_action ( 'admin_menu', array (
				&$this,
				'admin_menu' 
		) );
		add_filter ( 'custom_menu_order', array (
				&$this,
				'custom_menu_order' 
		) ); // Activate custom_menu_order
		add_filter ( 'menu_order', array (
				&$this,
				'custom_menu_order' 
		) );
		
		add_filter ( 'parent_file', array (
				&$this,
				'set_current_menu_hightlight' 
		) );
		add_filter ( 'manage_hanaboard-post_posts_columns', array (
				&$this,
				'hanaboard_post_table_head' 
		) );
		add_filter ( 'manage_hanaboard-post_posts_custom_column', array (
				&$this,
				'hanaboard_post_table_content' 
		), 10, 2 );
		add_action ( 'restrict_manage_posts', array (
				&$this,
				'hanaboard_posts_adjust_filter' 
		) );
		add_action ( 'admin_head', array (
				&$this,
				'add_menu_icons_styles' 
		) );
		
		add_action ( 'wp_ajax_hanaboard_rearrange_post_no', array (
				&$this,
				'rearrange_post_no' 
		) );
		add_action ( 'wp_ajax_nopriv_hanaboard_rearrange_post_no', array (
				&$this,
				'rearrange_post_no' 
		) );
		
		add_action ( 'wp_ajax_hanawordpress_save_client_id', array (
				&$this,
				'hanawordpress_save_client_id' 
		) );
		add_action ( 'wp_ajax_nopriv_hanawordpress_save_client_id', array (
				&$this,
				'hanawordpress_save_client_id' 
		) );
		
		add_action ( 'admin_enqueue_scripts', array (
				&$this,
				'admin_enqueue_scripts' 
		) );
	}
	function admin_init() {
		// initialize settings
		if (isset ( $_POST ['hanaboard_tax_options_submit'] )) {
			if (! wp_verify_nonce ( $_POST ['hanaboard-tax-settings-nonce'], 'hanaboard-tax-settings-form' )) {
				wp_die ( __ ( 'Cheating?', HANA_BOARD_TEXT_DOMAIN ) );
			}
			add_action ( 'init', array (
					$this,
					'board_tax_settings_submit' 
			) );
			$this->board_tax_settings_submit ();
		}
	}
	function add_dashboard_widgets() {
		wp_add_dashboard_widget ( 'hanaboard_dashboard_widget', // Widget slug.
__ ( 'HanaBoard Dashboard', HANA_BOARD_TEXT_DOMAIN ), // Title.
array (
				&$this,
				'dashboard_widget_function' 
		) );
	}
	
	/**
	 * Register the admin menu
	 *
	 * @since 0.1
	 */
	function admin_menu() {
		if (empty( $GLOBALS ['admin_page_hooks'] [HANAWORDPRESS_MENU] )) {
			$this->page = add_menu_page ( __ ( 'Hana Board', HANA_BOARD_TEXT_DOMAIN ), __ ( 'Hana Board', HANA_BOARD_TEXT_DOMAIN ), 'activate_plugins', HANAWORDPRESS_MENU, array (
					&$this,
					'page_dashboard' 
			), hanaboard_plugins_url ( 'includes/admin/images/webjangin_icon.png' ), 1 );
			add_submenu_page ( HANAWORDPRESS_MENU, __ ( 'DashBoard', HANA_BOARD_TEXT_DOMAIN ), __ ( 'DashBoard', HANA_BOARD_TEXT_DOMAIN ), 'activate_plugins', HANAWORDPRESS_MENU, array (
					&$this,
					'page_dashboard' 
			) );
		}
		add_submenu_page ( HANAWORDPRESS_MENU, __ ( 'Boards', HANA_BOARD_TEXT_DOMAIN ), __ ( 'Manage Boards', HANA_BOARD_TEXT_DOMAIN ), 'activate_plugins', 'hanaboard_manage_tax', array (
				&$this,
				'hanaboard_manage_tax'
		) );
		// add_submenu_page(HANAWORDPRESS_MENU, __('Board Articles', HANA_BOARD_TEXT_DOMAIN), __('Manage Board Articles', HANA_BOARD_TEXT_DOMAIN), 'edit_posts', 'edit.php?post_type=' . HANA_BOARD_POST_TYPE, NULL);
		
		/* Add callbacks for this screen only */
		add_action ( 'load-' . $this->page, array (
				$this,
				'page_actions' 
		), 9 );
		add_action ( 'admin_footer-' . $this->page, array (
				$this,
				'footer_scripts' 
		) );
		
		add_action ( 'add_meta_boxes_' . HANAWORDPRESS_MENU, array (
				&$this,
				'hana_meta_box_add' 
		), 10, 1 );
	}
	function footer_scripts() {
		?>
<script> postboxes.add_postbox_toggles(pagenow);</script>
<?php
	}
	
	/*
	 * Actions to be taken prior to page loading. This is after headers have been set. call on load-$hook This calls the add_meta_boxes hooks, adds screen options and enqueues the postbox.js script.
	 */
	function page_actions() {
		do_action ( 'add_meta_boxes_' . $this->page, null );
		do_action ( 'add_meta_boxes', $this->page, null );
		
		/* User can choose between 1 or 2 columns (default 2) */
		add_screen_option ( 'layout_columns', array (
				'max' => 2,
				'default' => 2 
		) );
		
		/* Enqueue WordPress' script for handling the metaboxes */
		wp_enqueue_script ( 'postbox' );
	}
	function render_page() {
		?>
<div class="wrap">

			<?php screen_icon(); ?>

			 <h2> <?php echo esc_html($this->title); ?> </h2>
	<form name="my_form" method="post">
		<input type="hidden" name="action" value="some-action">
				<?php
		
		wp_nonce_field ( 'some-action-nonce' );
		
		/* Used to save closed metaboxes and their order */
		wp_nonce_field ( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field ( 'closedpostboxes', 'closedpostboxesnonce', false );
		?>

				<div id="poststuff">
			<div id="post-body"
				class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
				<div id="post-body-content">
							<?php call_user_func($this->body_content_cb); ?>
						  </div>
				<div id="postbox-container-1" class="postbox-container">
						        <?php do_meta_boxes('', 'side', null); ?>
						  </div>
				<div id="postbox-container-2" class="postbox-container">
						        <?php do_meta_boxes('', 'normal', null); ?>
						        <?php do_meta_boxes('', 'advanced', null); ?>
						  </div>
			</div>
			<!-- #post-body -->
		</div>
		<!-- #poststuff -->
	</form>
</div>
<!-- .wrap -->
<?php
	}
	function custom_menu_order($menu_ord) {
		if (! $menu_ord)
			return true;
		
		return array (
				HANAWORDPRESS_MENU, // Dashboard
				'edit.php?post_type=' . HANA_BOARD_POST_TYPE, // Pages
				'hanaboard_manage_tax', // First separator
				'hanaboard_default_tax_settings' 
		); // First separator
	}
	function add_menu_icons_styles() {
		/*
		 * ?> <style> #adminmenu .toplevel_page_hanawordpress-menu div.wp-menu-image:before { // content: '\f119'; } </style> <?php
		 */
	}
	function admin_enqueue_scripts() {
		global $wp_scripts;
		wp_enqueue_script ( 'jquery' );
		wp_enqueue_script ( 'jquery-ui-draggable' );
		wp_enqueue_script ( 'jquery-ui-tabs' );
		$ui = $wp_scripts->query ( 'jquery-ui-core' );
		wp_enqueue_script ( 'hanaboard', hanaboard_plugins_url ( 'includes/admin/js/hanaboard_admin.js' ), array (
				'jquery',
				'jquery-ui-dialog' 
		) );
		wp_enqueue_script ( 'validate', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js', array (
				'jquery' 
		), true );
		wp_enqueue_style ( 'hanaboard_bootstrap_grid', hanaboard_plugins_url ( 'css/bootstrap-grid12.css' ) );
		wp_register_style ( 'jquery-ui-bootstrap', hanaboard_plugins_url ( 'css/jquery-ui-1.10.3.custom.css' ) );
		wp_enqueue_style ( 'jquery-ui-bootstrap' );
		
		wp_register_style ( 'fontawesome-45', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );
		wp_enqueue_style ( 'fontawesome-45' );
		wp_enqueue_style ( 'hanaboard_admin', hanaboard_plugins_url ( 'includes/admin/css/admin.css' ) );

		wp_localize_script ( 'hanaboard', 'hanaboard_admin', array (
				'msg' => array (
						'confirmRearrangePostNo' => __ ( 'Are you sure to numbering all post again?', HANA_BOARD_TEXT_DOMAIN ),
						'confirmDeleteBoard' => __ ( 'Are you sure to delete this board?', HANA_BOARD_TEXT_DOMAIN ) 
				) 
		) );
	}
	function set_current_menu_hightlight($parent_file) {
		global $submenu_file, $wp_screen, $pagenow;
		$current_screen = get_current_screen ();
		// Set the submenu as active/current while anywhere in your Custom Post
		// Type (nwcm_news)
		if ($current_screen->post_type == HANA_BOARD_POST_TYPE) {
			if ($current_screen->base == 'edit') {
				$submenu_file = 'edit.php?post_type=' . $current_screen->post_type;
			}
			$parent_file = HANAWORDPRESS_MENU;
		} else {
			if ($current_screen->base == 'settings_page_hanaboard_tax_options') {
				$submenu_file = 'admin.php?page=hanaboard_tax_options';
				$parent_file = 'hanaboard-list';
			}
		}
		return $parent_file;
	}
	function board_default_tax_settings() {
		$tax_id = 'default';
		$tax_name = 'Default';
		
		$option_tax_values = array ();
		
		include plugin_dir_path ( __FILE__ ) . 'page_tax_settings.php';
	}
	function board_tax_settings() {
		$option_tax_values = array ();
		if (isset ( $_GET ['action'] ) && $_GET ['action'] == 'edit') { // edit
			if (isset ( $_GET ['tag_ID'] )) {
				$tax_id = $_GET ['tag_ID'];
				$tax = get_term_by ( 'id', $tax_id, HANA_BOARD_TAXONOMY );
				if (! is_object ( $tax ))
					wp_die ( __ ( 'Wrong taxonomy ID.', HANA_BOARD_TEXT_DOMAIN ), 'Error', array (
							'response' => 500,
							'back_link' => true 
					) );
			}
			$tax_name = $tax->name;
			$option_tax_values = get_option ( HANA_BOARD_TAX_META_HEADER . $tax_id );
			include plugin_dir_path ( __FILE__ ) . 'page_tax_settings.php';
		} else if ($_GET ['action'] == 'add') { // add new
			$tax_id = null;
			$tax_name = __ ( 'Add new', HANA_BOARD_TEXT_DOMAIN );
			$option_tax_values = array ();
			include plugin_dir_path ( __FILE__ ) . 'page_tax_settings.php';
		}
	}
	function board_tax_settings_submit() {
		if (! isset($_POST ['hanaboard_tax_options_submit']) || $_POST ['hanaboard_tax_options_submit'] != 'yes')
			return false;
		
		$errors = new WP_Error ();
		
		$term_meta = $_POST ['term_meta'];
		
		if ($_POST ['hanaboard_action'] == "default") { // Board Default Settings
			$tax_id = 'default';
		} else { // Edit or Add new
			$tax_id = $_POST ['hanaboard_tax'];
			$wp_term_args = array (
					'name' => $term_meta ['name'],
					'description' => $term_meta ['description'],
					'slug' => $term_meta ['slug'],
					'parent' => $term_meta ['parent'] 
			);
			
			if ($_POST ['hanaboard_action'] == "edit") {
				$res = wp_update_term ( $_POST ['hanaboard_tax'], HANA_BOARD_TAXONOMY, $wp_term_args );
			} else if ($_POST ['hanaboard_action'] == "add") {
				$res = wp_insert_term ( $term_meta ['name'], HANA_BOARD_TAXONOMY, $wp_term_args );
			}
			if (is_wp_error ( $res ))
				$errors->add ( 'add_term_failed', '<strong>ERROR></strong>: Error while adding or editing.' );
			else {
				$term = get_term_by ( 'id', $res ['term_id'], HANA_BOARD_TAXONOMY );
				$tax_id = $term->term_id;
			}
			if ($errors->get_error_code ())
				return $errors->get_error_message ();
		}
		
		// 'Create a new page and connect automatically'
		if ($tax_id != 'default') {
			if ($term_meta ['connect_page'] > 0) {
				$page_post = get_post ( $term_meta ['connect_page'] );
				$original_page = $page_post;
				
				$page_post->post_title = $term_meta ['name'];
				$page_post->post_name = $term->slug;
				// If shortcode is not exists in the page content, add
				// automatically
				$page_post->post_content = remove_shortcode ( 'hanaboard', $content );
				if (strpos ( $page_post->post_content, hanaboard_get_shortcode_header () ) === FALSE) {
					$page_post->post_content .= $page_post->post_content . hanaboard_get_shortcode ( $term->slug );
				}
				wp_update_post ( $page_post );
			} else {
				$new_page_post = array (
						'post_content' => hanaboard_get_shortcode ( $term->slug ),
						'post_name' => $term->slug,
						'post_title' => $term_meta ['name'],
						'post_type' => 'page',
						'post_status' => 'publish',
						'post_author' => get_current_user_id () 
				);
				$page_id = wp_insert_post ( $new_page_post );
				$term_meta ['connect_page'] = $page_id;
			}
		} else {
			$term_meta ['connect_page'] = 0;
		}
		
		// These metas are saved with wp_insert_term
		unset ( $term_meta ['name'] );
		unset ( $term_meta ['slug'] );
		unset ( $term_meta ['parent'] );
		unset ( $term_meta ['description'] );
		// unset( $term_meta['connect_page'] );
		
		update_option ( HANA_BOARD_TAX_META_HEADER . $tax_id, $term_meta );
		if (isset ( $_POST ['apply_to_all'] ) && $_POST ['apply_to_all'] == 'on') {
			$terms = get_terms ( array (
					HANA_BOARD_TAXONOMY 
			), array (
					'hide_empty' => false 
			) );
			foreach ( $terms as $v ) {
				update_option ( HANA_BOARD_TAX_META_HEADER . $v->term_id, $term_meta );
			}
		}
		
		if ($tax_id != 'default')
			wp_redirect ( admin_url ( 'admin.php?page=hanaboard_manage_tax' ) );
	}
	function update_board_default_settings($arr) {
		if (is_array ( $arr )) {
			update_option ( HANA_BOARD_TAX_META_HEADER . 'default', $arr );
		}
	}
	function update_tax_meta_all($term_id, $arr) {
		if (is_array ( $arr )) {
			update_option ( HANA_BOARD_TAXONOMY . '_tax_meta_' . $term_id, $arr );
		}
	}
	function update_tax_meta($term_id, $key = null, $value = null) {
		$m = get_option ( HANA_BOARD_TAXONOMY . '_tax_meta_' . $term_id );
		if (! $key) {
			if (is_array ( $m )) {
				foreach ( $m as $k => $v ) {
					update_option ( 'tax_meta_' . $term_id, $v );
				}
			}
		} else {
			$m [$key] = $value;
			update_option ( 'tax_meta_' . $term_id, $m );
		}
	}
	// get term meta field
	function get_tax_meta($term_id, $key = null, $multi = false) {
		$t_id = (is_object ( $term_id )) ? $term_id->term_id : $term_id;
		$m = get_option ( HANA_BOARD_TAXONOMY . '_tax_meta_' . $t_id );
		if (! $key) {
			return $m;
		} else {
			if (isset ( $m [$key] )) {
				return $m [$key];
			} else {
				return '';
			}
		}
	}
	
	// delete meta
	function delete_tax_meta($term_id, $key) {
		$m = get_option ( HANA_BOARD_TAXONOMY . '_tax_meta_' . $term_id );
		if (isset ( $m [$key] )) {
			unset ( $m [$key] );
		}
		update_option ( HANA_BOARD_TAXONOMY . '_tax_meta_' . $term_id, $m );
	}
	function hanaboard_post_table_head($defaults) {
		$defaults ['author'] = 'Author';
		$defaults ['comments'] = 'Comments';
		$defaults ['recommends'] = 'Recommends';
		$defaults ['readcount'] = 'Read Count';
		return $defaults;
	}
	function hanaboard_post_table_content($column_name, $post_id) {
		if ($column_name == 'readcount') {
			$readcount = get_post_meta ( $post_id, '_hanaboard_post_readcount', true );
			echo $readcount;
		}
		if ($column_name == 'recommends') {
			$recommends = get_post_meta ( $post_id, '_hanaboard_post_recommends', true );
			echo $recommends;
		}
	}
	function hanaboard_posts_adjust_filter() {
		$hanaboard_post_type = HANA_BOARD_POST_TYPE;
		$taxonomy = HANA_BOARD_TAXONOMY;
		if ($hanaboard_post_type != "page" && $hanaboard_post_type == HANA_BOARD_POST_TYPE) {
			$filters = array (
					$taxonomy 
			);
			foreach ( $filters as $tax_slug ) {
				$tax_obj = get_taxonomy ( $tax_slug );
				$tax_name = $tax_obj->labels->name;
				$terms = get_terms ( $tax_slug );
				echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
				echo "<option value=''>All $tax_name</option>";
				foreach ( $terms as $term ) {
					echo '<option value=' . $term->slug, $_GET [$tax_slug] == $term->slug ? ' selected="selected"' : '', '>' . $term->name . ' (' . $term->count . ')</option>';
				}
				echo "</select>";
			}
		}
	}
	function hanaboard_manage_tax() {
		global $wp_error;
		$modes_tax_page = array (
				'edit',
				'add' 
		);
		if (isset ( $_GET ['action'] ) && in_array ( $_GET ['action'], $modes_tax_page )) {
			$this->board_tax_settings ();
		} else {
			
			require_once (plugin_dir_path ( __FILE__ ) . 'class-admin-hanaboard-list.php');
			$admin_hanaboard_list_table = new Admin_HanaBoard_List_Table ();
			$admin_hanaboard_list_table->prepare_items ();
			if (isset ( $wp_error->message )) {
				echo "<div id='message' class='updated'>";
				echo "<p>$wp_error->message;</p>";
				echo "</div>";
			}
			?>
<h2><?php _e('Manage Boards', HANA_BOARD_TEXT_DOMAIN); ?></h2>
<a href="admin.php?page=hanaboard_manage_tax&action=add"
	class='button-primary' type='button' id="addNewButton"><?php _e('Add New', HANA_BOARD_TEXT_DOMAIN); ?></a>
<?php
			$admin_hanaboard_list_table->display ();
		}
	}
	function rearrange_post_no() {
		if (isset ( $_GET ['tax_id'] )) {
			$q = array (
					'post_type' => HANA_BOARD_POST_TYPE,
					'numberposts' => - 1,
					'post_status' => 'any',
					'tax_query' => array (
							array (
									'taxonomy' => HANA_BOARD_TAXONOMY,
									'field' => 'id',
									'terms' => $_GET ['tax_id'] 
							) 
					) 
			); // Where term_id of Term 1 is "1".
			
			$posts = get_posts ( $q );
			$no = sizeof ( $posts );
			foreach ( $posts as $k => $post ) {
				update_post_meta ( $post->ID, 'hanaboard_post_no', $no );
				$no --;
			}
		}
		die ();
	}
	
	/**
	 * HanaBoard Settings sections
	 *
	 * @since 0.1
	 * @return array
	 */
	function get_settings_sections($page = null) {
		return hanaboard_settings_sections ( $page );
	}
	function page_dashboard() {
		do_action ( 'hanawordpress_admin_top' );
		do_action ( 'hanawordpress_display_dashboard' );
		do_action ( 'hanawordpress_admin_bottom' );
	}
	function hana_meta_box_add() {
		add_meta_box ( 'hanawordpress-metabox-news', __ ( 'Hana Wordpress News', HANA_BOARD_TEXT_DOMAIN ), array (
				&$this,
				'hana_meta_box_cb' 
		), HANAWORDPRESS_MENU, 'normal', 'high' );
	}
	function hana_meta_box_cb() {
		// echo hanawordpress_dashboard_widget_function();
	}
	function add_dashboard_widget() {
		// wp_add_dashboard_widget( 'hanawordpress_dashboard_widget', 'Recent News from HanaWordpress.com', 'hanawordpress_dashboard_widget_function' );
	}
}

$hanaboard_settings = new HanaBoard_Settings ();


