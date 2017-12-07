<?php

/* https://codex.wordpress.org/Function_Reference/get_comments */
class Hana_Comment_Widget extends WP_Widget {
	var $default_atts;
	var $widget_class;
	var $skin_dir, $skin_url;
	private $is_shortcode;	
	function __construct() {
		parent::__construct( 'hana_comment_widget', // Base ID
__( 'Hana Latest Comments', HANA_WIDGET_TEXT_DOMAIN ), // Name
array (
				'description' => __( 'Display recent comments.',HANA_WIDGET_TEXT_DOMAIN ) 
		) );
		$this->default_atts = array (
				'title' => '',
				'count' => 5,
				'orderby' => 'comment_date',
				'order' => 'desc',
				'show_author' => false,
				'show_date' => false,
				'ellipsis' => false,
				'date_query' => '',
				'skin' => 'default',
				'hide_admin' => false
		);
		add_action( 'widgets_init', array (
				&$this,
				'register_widget' 
		) );
		add_shortcode( 'hana_comment_widget', array (
				&$this,
				'widget_shortcode' 
		) );
		$this->is_shortcode = false;		
	}
	function setCurrentSkin($skin) {
		if (! in_array( $skin, hana_widget_skin_list( 'comment' ) ))
			$skin = 'default';
		$this->current_skin = $skin;
		
		$this->skin_url = HANA_WIDGET_SKIN_URL . '/comment/' . $skin;
		$this->skin_dir = HANA_WIDGET_SKIN_DIR . '/comment/' . $skin;
	}

	function register_widget() {
		register_widget( 'Hana_Comment_Widget' );
	}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance ['title'] = strip_tags( $new_instance ['title'] );
		$instance ['show_author'] = strip_tags( $new_instance ['show_author'] );
		$instance ['show_date'] = strip_tags( $new_instance ['show_date'] );
		$instance ['ellipsis'] = strip_tags( $new_instance ['ellipsis'] );
		$instance ['skin'] = strip_tags( $new_instance ['skin'] );
		$instance ['author__not_in'] = strip_tags( $new_instance ['author__not_in'] );
		$instance ['hide_admin'] = strip_tags( $new_instance ['hide_admin']);
		return $instance;
	}
	function widget($args, $instance) {
		extract( $args );
		
		$title = apply_filters( 'widget_title', $instance ['title'] );
		$this->is_shortcode = false;
		echo $before_widget;
		
		if ($title) {
			echo $before_title . $title . $after_title;
		}
		
		$this->getCommentListings( $args, $instance );
		echo $after_widget;
	}
	function form($instance) {
		$instance = array_merge( $this->default_atts, $instance );
		extract( $instance );
		$orderby_array = array (
				'comment_date' => 'Date',
				'wpdiscuz_votes' => 'WP Discuz Votes' 
		);
		$order_array = array (
				'DESC' => 'DESC',
				'ASC' => 'ASC' 
		);
		$widget_skins = hana_widget_skin_list( 'comment' );
		
		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hana_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Count', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>">
		<?php for($x=1;$x<=20;$x++) { ?>
		<option <?php echo ($x == $count ? 'selected="selected"' : ''); ?> value="<?php echo $x; ?>"><?php echo $x; ?></option>
		<?php } ?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('skin'); ?>"><?php _e('Widget Skin', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('skin'); ?>" name="<?php echo $this->get_field_name('skin'); ?>">
		<?php foreach ($widget_skins as $k=>$v) { ?>
		<option <?php echo ($v == $skin ? 'selected="selected"' : ''); ?> value="<?php echo $k; ?>"><?php echo $v; ?></option>
		<?php } ?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
		<?php foreach( $orderby_array as $k => $v ) { ?>
		<option <?php echo ($v == $orderby ? 'selected="selected"' : ''); ?> value="<?php echo $v; ?>"><?php echo $k; ?></option>
		<?php } ?>
	</select>		
</p>
<p>
	<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order', 'hana_widget'); ?></label>
	<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
			<?php foreach( $order_array as $k => $v ) { ?>
			<option <?php echo ($v == $orderby ? 'selected="selected"' : ''); ?> value="<?php echo $v; ?>"><?php echo $k; ?></option>
			<?php } ?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Show Author', 'hana_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" type="checkbox" <?php echo $show_author ? 'checked="checked"' : ''; ?> />
</p>
<p>
	<label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show Date', 'hana_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" type="checkbox" <?php echo $show_date ? 'checked="checked"' : ''; ?> />
</p>
<p>
	<label for="<?php echo $this->get_field_id('author__not_in'); ?>"><?php _e('Comments author not in', 'hana_widget'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('author__not_in'); ?>" name="<?php echo $this->get_field_name('author__not_in'); ?>" type="text" value="<?php echo $author__not_in; ?>" />
</p>
<?php
	}
	function getCommentListings($args, $instance) { // html
		extract( $args );
		extract( $instance );
		
		// if hide admin
		$hide_admin = true;
		if ($hide_admin) {
			global $wpdb;
			$ids = $wpdb->get_row( "SELECT $wpdb->users.ID FROM $wpdb->users WHERE (SELECT $wpdb->usermeta.meta_value FROM $wpdb->usermeta WHERE $wpdb->usermeta.user_id = wp_users.ID AND $wpdb->usermeta.meta_key = 'wp_capabilities') LIKE '%administrator%'", ARRAY_N, 0 );
			if (is_array( $ids )) {
				$author__not_in = implode( ',', $ids );
			}
		}
		remove_filter('hanaboard_connected_page', 'hanaboard_connected_page');
		
		$args = array (
				'author__not_in' => $author__not_in,
				'status' => 'approve',
				'number' => $count <= 0 ? $count : 5,
				'order_by' => $orderby,
				'order' => $order,
				'post_status' => 'publish',
				'paged' => 1 
		);
		$this->setCurrentSkin($skin);
		$class_with_shortcode = $this->is_shortcode ? 'shortcode' : '';		
		$comments = get_comments( $args );
		include $this->skin_dir . '/loop_header.php';
		if (is_array( $comments )) {
			foreach ( $comments as $comment ) {
				$comment_link = get_permalink( $comment->comment_post_ID );
				if( $comment->user_id )
					$author_name = get_the_author_meta('display_name', $comment->user_id);
				else 
					$author_name = $comment->comment_author;
				include $this->skin_dir . '/loop_item.php';
			}
		} else {
			include $this->skin_dir . '/loop_no_item.php';
		}
		include $this->skin_dir . '/loop_footer.php';
	}
	function widget_shortcode($atts) {
		$this->is_shortcode = true;
		// Configure defaults and extract the attributes into variables
		$atts = shortcode_atts( $this->default_atts, $atts );
		extract( $atts );
		// $terms = array_map('trim', explode(',', $atts['terms']) );
		$instance = array (
				'count' => $count 
		);
		$args = array (
				'before_widget' => '<div class="widget scheme-' . $scheme . '  ">',
				'after_widget' => '</div>',
				'before_title' => '<h4 class="widgettitle">',
				'after_title' => '</h4>' 
		);
		foreach ( $this->default_atts as $k => $v ) {
			// $args[$k] = $$k;
		}
		$args = array_merge( $this->default_atts, $args, $atts );
		ob_start();
		echo $args ['before_widget'];
		if ($title) {
	//		echo $args ['before_title'] . $title . $args ['after_title'];
		}
		$this->getCommentListings( $args, $instance );
		echo $args ['after_widget'];
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
} // end class Hana_Comment_Widget

$Hana_Comment_Widget = new Hana_Comment_Widget();
