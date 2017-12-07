<?php
/**
 * Most Liked Widget Class
 */
class hana_most_liked_widget extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::__construct(false, $name = __('Most Liked Items', 'like_it'), array('description' => __('Show the most liked items', 'like_it')));	
    }
    /** @see WP_Widget::widget */
    function widget($args, $instance) {	
        extract( $args );
        $title 	= apply_filters('widget_title', $instance['title']);
        $number = strip_tags($instance['number']);
		
		echo $before_widget; 
            if ( $title )
                echo $before_title . $title . $after_title; ?>
					<ul class="most-liked">
					<?php
						$args = array(
							'post_type' => 'any',
							'numberposts' => $number,
							'meta_key' => 'hana_like',
							'orderby' => 'meta_value',
							'order' => 'DESC'
						);
						$most_liked = get_posts( $args );
						foreach( $most_liked as $liked ) : ?>
							<li class="liked-item">
								<a href="<?php echo get_permalink($liked->ID); ?>"><?php echo get_the_title($liked->ID); ?></a><br/>
							</li>
						<?php endforeach; ?>
					</ul>
              <?php 
		echo $after_widget;
    }
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = strip_tags($new_instance['number']);
        return $instance;
    }
    /** @see WP_Widget::form */
    function form($instance) {	
        $title = esc_attr($instance['title']);
        $number = esc_attr($instance['number']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number to Show:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
        </p>
        <?php 
    }
}
add_action('widgets_init', create_function('', 'return register_widget("hana_most_liked_widget");'));
