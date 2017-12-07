<?php
class Hana_Post_Widget extends WP_Widget {
	private $post_type;
	private $default_atts;
	private $widget_class;
	private $skin_dir, $skin_url;
	private $is_shortcode;
	function __construct() {
		parent::__construct ( 'hana_post_widget', // Base ID
__ ( 'Hana Latest Posts', HANA_WIDGET_TEXT_DOMAIN ), // Name
array (
				'description' => __ ( 'Display any post type posts. Outputs the post thumbnail, title and date per listing', HANA_WIDGET_TEXT_DOMAIN ) 
		) );
		$this->default_atts = array (
				'title' => '',
				'title_link_page' => 0,
				'offset' => 0,
				'scheme' => 'light',
				'skin' => 'default',
				'post_type' => 'post',
				'taxonomy' => 'category',
				'number' => 5,
				'terms' => '',
				'columns' => 1,
				'show_thumbnail' => false,
				'thumbnail_size' => 'hana_wide_thumb',
				'show_post_title' => true,
				'show_author' => false,
				'show_date' => true,
				'show_num_comments' => false,
				'post_id' => null 
		);
		add_action ( 'widgets_init', array (
				&$this,
				'register_widget' 
		) );
		add_shortcode ( 'hana_post_widget', array (
				&$this,
				'widget_shortcode_post' 
		) );
		add_shortcode ( 'hana_board_widget', array (
				&$this,
				'widget_shortcode_board'
		) );		
		add_theme_support ( 'post-thumbnails' );
		add_image_size ( 'hana_wide_thumb', 320, 200, true );
		add_image_size ( 'hana_micro_thumb', 120, 75, true );
		$this->is_shortcode = false;
	}
	function register_widget() {
		register_widget ( 'Hana_Post_Widget' );
	}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance ['title'] = strip_tags ( $new_instance ['title'] );
		$instance ['offset'] = strip_tags ( $new_instance ['offset'] );
		$instance ['post_type'] = strip_tags ( $new_instance ['post_type'] );
		$instance ['number'] = strip_tags ( $new_instance ['number'] );
		$instance ['taxonomy'] = strip_tags ( $new_instance ['taxonomy'] );
		$instance ['terms'] = strip_tags ( $new_instance ['terms'] );
		$instance ['show_author'] = strip_tags ( $new_instance ['show_author'] );
		$instance ['columns'] = strip_tags ( $new_instance ['columns'] );
		$instance ['show_post_title'] = strip_tags ( $new_instance ['show_post_title'] );
		$instance ['show_date'] = strip_tags ( $new_instance ['show_date'] );
		$instance ['show_thumbnail'] = strip_tags ( $new_instance ['show_thumbnail'] );
		$instance ['thumbnail_size'] = strip_tags ( $new_instance ['thumbnail_size'] );
		$instance ['show_num_comments'] = strip_tags ( $new_instance ['show_num_comments'] );
		$instance ['skin'] = strip_tags ( $new_instance ['skin'] );
		return $instance;
	}
	function widget($args, $instance) {
		extract ( $args );
		
		$title = apply_filters ( 'widget_title', $instance ['title'] );
		$number = $instance ['number'];
		$this->is_shortcode = false;
		echo $before_widget;
		if ($title) {
			echo $before_title . $title . $after_title;
		}
		$this->getHanaPostListings ( $args, $instance );
		echo $after_widget;
	}
	function form($instance) {
		$instance = array_merge ( $this->default_atts, $instance );
		extract ( $instance );
		
		$widget_skins = hana_widget_skin_list ( 'post' );
		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hana_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
		name="<?php echo $this->get_field_name('title'); ?>" type="text"
		value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('offset'); ?>"><?php _e('Offset', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('offset'); ?>"
		name="<?php echo $this->get_field_name('offset'); ?>" type="text"
		value="<?php echo $offset; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Listings:', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('number'); ?>"
		name="<?php echo $this->get_field_name('number'); ?>">
			<?php for($x=1;$x<=10;$x++) { ?>
			<option <?php echo ($x == $number ? 'selected="selected"' : ''); ?>
			value="<?php echo $x; ?>"><?php echo $x; ?></option>
			<?php } ?>
		</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('taxonomy'); ?>"
		name="<?php echo $this->get_field_name('taxonomy'); ?>" type="text"
		value="<?php echo $taxonomy; ?>" />
	<div class="description"><?php _e('For displaying Hana Board Articles, put "hanaboard".', HANA_WIDGET_TEXT_DOMAIN); ?></div>
</p>
<p>
	<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('post_type'); ?>"
		name="<?php echo $this->get_field_name('post_type'); ?>" type="text"
		value="<?php echo $post_type; ?>" />
	<div class="description"><?php _e('For displaying Hana Board Articles, put "hanaboard-post".', HANA_WIDGET_TEXT_DOMAIN); ?></div>
</p>
<p>
	<label for="<?php echo $this->get_field_id('skin'); ?>"><?php _e('Widget Skin', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('skin'); ?>"
		name="<?php echo $this->get_field_name('skin'); ?>">
				<?php foreach ($widget_skins as $k=>$v) { ?>
				<option <?php echo ($v == $skin ? 'selected="selected"' : ''); ?>
			value="<?php echo $k; ?>"><?php echo $v; ?></option>
				<?php } ?>
			</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('terms'); ?>"><?php _e('Terms', 'hana_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('terms'); ?>"
		name="<?php echo $this->get_field_name('terms'); ?>" type="text"
		value="<?php echo $terms; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Show Author', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('show_author'); ?>"
		name="<?php echo $this->get_field_name('show_author'); ?>"
		type="checkbox" <?php echo $show_author ? 'checked="checked"' : ''; ?> />
</p>
<p>
	<label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show Date', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('show_date'); ?>"
		name="<?php echo $this->get_field_name('show_date'); ?>"
		type="checkbox" <?php echo $show_date ? 'checked="checked"' : ''; ?> />
</p>
<p>
	<label for="<?php echo $this->get_field_id('show_num_comments'); ?>"><?php _e('Show number of Comments', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('show_num_comments'); ?>"
		name="<?php echo $this->get_field_name('show_num_comments'); ?>"
		type="checkbox"
		<?php echo $show_num_comments ? 'checked="checked"' : ''; ?> />
</p>
<p>
	<label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Columns in a Row', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('columns'); ?>"
		name="<?php echo $this->get_field_name('columns'); ?>">
				<?php foreach (array(1,2,3,4,6) as $v) { ?>
				<option <?php echo ($v == $columns ? 'selected="selected"' : ''); ?>
			value="<?php echo $v; ?>"><?php echo $v; ?></option>
				<?php } ?>
			</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('show_post_title'); ?>"><?php _e('Show Post Title', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('show_post_title'); ?>"
		name="<?php echo $this->get_field_name('show_post_title'); ?>"
		type="checkbox"
		<?php echo $show_post_title ? 'checked="checked"' : ''; ?> />
</p>
<p>
	<label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><?php _e('Show Thumbnail', 'hana_widget'); ?></label>
	<input class="widefat"
		id="<?php echo $this->get_field_id('show_thumbnail'); ?>"
		name="<?php echo $this->get_field_name('show_thumbnail'); ?>"
		type="checkbox"
		<?php echo $show_thumbnail ? 'checked="checked"' : ''; ?> />
</p>
<p>
	<label for="<?php echo $this->get_field_id('thumbnail_size'); ?>"><?php _e('Thumbnail Size', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('thumbnail_size'); ?>"
		name="<?php echo $this->get_field_name('thumbnail_size'); ?>">
				<?php foreach (hana_get_thumbnail_sizes() as $k=>$v) { ?>
				<option <?php echo ($k == $columns ? 'selected="selected"' : ''); ?>
			value="<?php echo $k; ?>"><?php echo $v; ?></option>
				<?php } ?>
			</select>
</p>
<?php
	}
	function getHanaPostListings($args, $instance) { // html
		if (! isset ( $args ['widget_id'] )) {
			$args ['widget_id'] = $this->id;
		}
		
		if (is_array ( $instance ['terms'] ))
			$instance ['terms'] = implode ( ',', array_filter ( $instance ['terms'] ) );
		
		if ($this->is_shortcode) {
			$title = $instance ['title'];
		} else {
			$title = (! empty ( $instance ['title'] )) ? $instance ['title'] : __ ( 'Recent Posts' );
			/**
			 * This filter is documented in wp-includes/widgets/class-wp-widget-pages.php
			 */
			$title = apply_filters ( 'widget_title', $title, $instance, $this->id_base );
		}
		$number = (! empty ( $instance ['number'] )) ? absint ( $instance ['number'] ) : 5;
		
		$this->setCurrentSkin ( $instance ['skin'] );
		if ($instance ['skin'] == 'allery')
			$show_thumbnail = true;

		if ($instance['skin'])
		/**
		 * Filter the arguments for the Recent Posts widget.
		 *
		 * @since 3.4.0
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args
		 *        	An array of arguments used to retrieve the recent posts.
		 */
		remove_filter ( 'hanaboard_connected_page', 'hanaboard_connected_page' );
		
		if ($instance ['post_id']) {
			$query_args = array (
					'post__in' => explode ( ',', $instance ['post_id'] ) 
			);
		} else {
			$query_args = apply_filters ( 'widget_posts_args', array (
					'order_by' => 'date',
					'order' => 'DESC',
					'exclude' => isset ( $instance ['exclude_ids'] ) ? $instance ['exclude_ids'] : '',
					'posts_per_page' => $instance ['number'] ? $instance ['number'] : 5,
					'offset' => $instance ['offset'],
					'no_found_rows' => true,
					'post_status' => 'publish',
					'ignore_sticky_posts' => true 
			) );
			$query_args ['post_type'] = $instance ['post_type'];
			if ($instance ['terms'] != 'all' && $instance ['terms'] != '' && $instance ['terms']) {
				$query_args ['tax_query'] = array (
						array (
								'taxonomy' => $instance ['taxonomy'],
								'field' => 'slug',
								'terms' => explode ( ',', $instance ['terms'] ),
								'include_children' => true,
								'operator' => 'IN' 
						) 
				);
			}
			if ($instance ['show_thumbnail']) {
				$query_args ['meta_query'] [] ['key'] = '_thumbnail_id';
				$query_args ['meta_query'] [] ['compare'] = 'EXISTS';
			}
		}
		$title_link_page = null;
		if ($this->is_shortcode) {
			$title_link_page = $instance ['title_link_page']; // slug
			if ($title_link_page) { // by slug
				$title_link = get_permalink ( get_page_by_path ( $title_link_page ) );
			} else {
				if (sizeof ( $instance ['terms'] ) > 0) {
					if (is_array ( $instance ['terms'] ))
						$terms = $instance ['terms'] [0];
					else
						$terms = $instance ['terms'];
					if (defined ( 'HANA_BOARD_TAXONOMY' ) && $instance ['taxonomy'] == HANA_BOARD_TAXONOMY) {
						$term = get_term_by ( 'slug', $terms, $instance ['taxonomy'] );
						
						if (is_object ( $term ))
							$title_link = hanaboard_get_the_term_link ( $term->term_id );
					} else {
						$title_link = get_term_link ( $terms, $instance ['taxonomy'] );
					}
				}
			}
		}
		if ( ! is_string($title_link ) )
			$title_link = '#';
		
		$show_post_title = $instance ['show_post_title'];
		$show_readcount = $instance ['show_readcount'];
		$show_category = $instance ['show_category'];
		$show_author = $instance ['show_author'];
		$show_date = $instance ['show_date'];
		$show_num_comments = $instance ['show_num_comments'];
		$show_like = $instance ['show_like'];
		$skin_url = $this->skin_url;
		$thumbnail_size = in_array ( $instance ['thumbnail_size'], hana_get_thumbnail_sizes () ) ? $instance ['thumbnail_size'] : 'hana_wide_thumb';
		$class_with_shortcode = $this->is_shortcode ? 'shortcode' : '';
		$r = new WP_Query ( $query_args );
		include $this->skin_dir . '/loop_header.php';
		if ($r->have_posts ()) {
			while ( $r->have_posts () ) {
				$r->the_post ();
				if ($instance ['show_thumbnail']) {
					$thumbnail_url = wp_get_attachment_image_src ( get_post_thumbnail_id ( $post->ID ), 'thumbnail_size' );
					$thumbnail_url = $thumbnail_url [0];
				}
				$term_link = null;
				if (function_exists ( 'hanaboard_get_term_link' ))
					$term_link = hanaboard_get_term_link ( '%s' );
				
				$post = get_post ( get_the_ID () );
				if ($post->post_author)
					$author_name = get_the_author_meta ( 'display_name', $post->post_author );
				else if (function_exists ( 'hanaboard_get_post_meta' ) && hanaboard_get_post_meta ( get_the_ID (), "guest_author" ))
					$author_name = hanaboard_get_post_meta ( get_the_ID(), "guest_author" );
				else
					$author_name = __ ( 'Guest', HANA_WIDGET_TEXT_DOMAIN );
				
				include $this->skin_dir . '/loop_item.php';
			}
		} else {
			include $this->skin_dir . '/loop_no_item.php';
		}
		include $this->skin_dir . '/loop_footer.php';
		
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata ();
	}
	function get_field_name2($str) {
		return $str;
	}
	function get_field_id2($str) {
		return $str;
	}
	function get_field_thumbnail_size($str) {
		return $str;
	}
	function setCurrentSkin($skin) {
		if (! in_array ( $skin, hana_widget_skin_list ( 'post' ) ))
			$skin = 'default';
		$this->current_skin = $skin;
		$this->skin_url = HANA_WIDGET_SKIN_URL . '/post/' . $skin;
		$this->skin_dir = HANA_WIDGET_SKIN_DIR . '/post/' . $skin;
	}
	function widget_shortcode_post($atts) {
		$this->is_shortcode = true;
		// Configure defaults and extract the attributes into variables
		$atts = shortcode_atts ( $this->default_atts, $atts );
		extract ( $atts );
		$args = array (
				'before_widget' => '<div class="widget scheme-' . $scheme . '  ">',
				'after_widget' => '</div>',
				'before_title' => '<h4 class="widgettitle">',
				'after_title' => '</h4>' 
		);
		$instance = array_merge ( $this->default_atts, $atts );
		ob_start ();
		echo $args ['before_widget'];
		$this->getHanaPostListings ( $args, $instance );
		echo $args ['after_widget'];
		$output = ob_get_contents ();
		ob_end_clean ();
		return $output;
	}
	function widget_shortcode_board($atts) {
		$this->is_shortcode = true;
		// Configure defaults and extract the attributes into variables
		$board_default_atts = $this->default_atts;
		$board_default_atts['post_type'] = 'hanaboard-post';
		$board_default_atts['taxonomy'] = 'hanaboard';

		if($atts['skin'] == 'gallery' && ! isset($atts['columns']))
			$atts['columns'] = 2;

		$atts = shortcode_atts ( $board_default_atts, $atts );
		extract ( $atts );
		$args = array (
				'before_widget' => '<div class="widget scheme-' . $scheme . '  ">',
				'after_widget' => '</div>',
				'before_title' => '<h4 class="widgettitle">',
				'after_title' => '</h4>'
		);
		$instance = array_merge ( $board_default_atts, $atts );
		ob_start ();
		echo $args ['before_widget'];
		$this->getHanaPostListings ( $args, $instance );
		echo $args ['after_widget'];
		$output = ob_get_contents ();
		ob_end_clean ();
		return $output;
	}
} // end class Hana_Post_Widget


$Hana_Post_Widget = new Hana_Post_Widget ();
if (! function_exists ( 'hana_get_thumbnail_sizes' )) {
	function hana_get_thumbnail_sizes() {
		$return = array ();
		foreach ( get_intermediate_image_sizes () as $v ) {
			$return [$v] = $v;
		}
		return $return;
	}
}

