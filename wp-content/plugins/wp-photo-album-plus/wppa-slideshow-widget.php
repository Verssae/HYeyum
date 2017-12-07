<?php
/* wppa-slideshow-widget.php
* Package: wp-photo-album-plus
*
* display a slideshow in the sidebar
* Version 6.7.01
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

/**
 * SlideshowWidget Class
 */
class SlideshowWidget extends WP_Widget {

    /** constructor */
    function __construct() {
		$widget_ops = array( 'classname' => 'slideshow_widget', 'description' => __( 'Display a slideshow', 'wp-photo-album-plus' ) );
		parent::__construct( 'slideshow_widget', __( 'WPPA+ Sidebar Slideshow', 'wp-photo-album-plus' ), $widget_ops);
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {
		global $wpdb;

		require_once(dirname(__FILE__) . '/wppa-links.php');
		require_once(dirname(__FILE__) . '/wppa-styles.php');
		require_once(dirname(__FILE__) . '/wppa-functions.php');
		require_once(dirname(__FILE__) . '/wppa-thumbnails.php');
		require_once(dirname(__FILE__) . '/wppa-boxes-html.php');
		require_once(dirname(__FILE__) . '/wppa-slideshow.php');
		wppa_initialize_runtime();

        extract( $args );

		$instance = wp_parse_args( (array) $instance,
									array( 	'title' 	=> __( 'Sidebar Slideshow', 'wp-photo-album-plus' ),
											'album' 	=> '',
											'width' 	=> wppa_opt( 'widget_width' ),
											'height' 	=> round( wppa_opt( 'widget_width' ) * wppa_opt( 'maxheight' ) / wppa_opt( 'fullsize' ) ),
											'ponly' 	=> 'no',
											'linkurl' 	=> '',
											'linktitle' => '',
											'subtext' 	=> '',
											'supertext' => '',
											'valign' 	=> 'center',
											'timeout' 	=> '4',
											'film' 		=> 'no',
											'browse' 	=> 'no',
											'name' 		=> 'no',
											'numbar'	=> 'no',
											'desc' 		=> 'no'
											) );

		$title 		= apply_filters( 'widget_title', $instance['title'] );
		$album 		= $instance['album'];
		if ( $instance['height'] == '0' ) {
			$instance['height'] = round( $instance['width'] * wppa_opt( 'maxheight' ) / wppa_opt( 'fullsize' ) );
		}
		$page 		= in_array( wppa_opt( 'slideonly_widget_linktype' ), wppa( 'links_no_page' ) ) ? '' :
					  wppa_get_the_landing_page( 'slideonly_widget_linkpage', __( 'Widget landing page', 'wp-photo-album-plus' ) );

		// Do the widget if we know the album
		if ( is_numeric( $album ) && wppa_album_exists( $album ) ) {

			// Open widget
			echo $before_widget;

				// Widget title
				if ( ! empty( $title ) ) {
					echo $before_title . $title . $after_title;
				}

				// Show text above slideshow
				if ( __( $instance['supertext'] ) ) {
					echo
					'<div style="padding-top:2px; padding-bottom:4px; text-align:center">' .
						__( $instance['supertext'] ) .
					'</div>';
				}

				// Fill in runtime parameters to tune the slideshow
				if ( $instance['linkurl'] && wppa_opt( 'slideonly_widget_linktype' ) == 'widget' ) {
					wppa( 'in_widget_linkurl', $instance['linkurl'] );
					wppa( 'in_widget_linktitle', __( $instance['linktitle'] ) );
				}
				wppa( 'auto_colwidth', false );
				wppa( 'in_widget', 'ss' );
				wppa( 'in_widget_frame_height', $instance['height'] );
				wppa( 'in_widget_frame_width', $instance['width'] );
				wppa( 'in_widget_timeout', $instance['timeout'] * 1000 );
				wppa( 'portrait_only', wppa_checked( $instance['ponly'] ) );
				wppa( 'ss_widget_valign', $instance['valign'] );
				wppa( 'film_on', wppa_checked( $instance['film'] ) );
				wppa( 'browse_on', wppa_checked( $instance['browse'] ) );
				wppa( 'name_on', wppa_checked( $instance['name'] ) );
				wppa( 'numbar_on', wppa_checked( $instance['numbar'] ) );
				wppa( 'desc_on', wppa_checked( $instance['desc'] ) );

				// Open the slideshow container
				echo
				'<div style="padding-top:2px; padding-bottom:4px;" >';

					// The very slideshow
					echo wppa_albums( $album, 'slideonly', $instance['width'], 'center' );

				// Close slideshw container
				echo
				'</div>';

				// Reset runtime parameters
				wppa_reset_occurrance();

				// Show text below the slideshow
				if ( __( $instance['subtext'] ) ) {
					echo
					'<div style="padding-top:2px; padding-bottom:0px; text-align:center" >' .
						__( $instance['subtext'] ) .
					'</div>';
				}

			// Close the widget
			echo $after_widget;
		}

		// No album specified
		else {
			echo "\n" . $before_widget;
			if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
			echo __( 'Unknown album or album does not exist', 'wp-photo-album-plus' );
			echo $after_widget;
		}
    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] 		= strip_tags( $new_instance['title'] );
		$instance['album'] 		= $new_instance['album'];
		$instance['width'] 		= $new_instance['width'];
		$instance['height'] 	= $new_instance['height'];
		$instance['ponly'] 		= $new_instance['ponly'];
		$instance['linkurl'] 	= $new_instance['linkurl'];
		$instance['linktitle'] 	= $new_instance['linktitle'];
		$instance['supertext'] 	= $new_instance['supertext'];
		$instance['subtext'] 	= $new_instance['subtext'];
		if ( wppa_checked( $instance['ponly'] ) ) {
			$instance['valign'] = 'fit';
		}
		else {
			$instance['valign'] = $new_instance['valign'];
		}
		$instance['timeout'] 	= $new_instance['timeout'];
		$instance['film'] 		= $new_instance['film'];
		$instance['browse'] 	= $new_instance['browse'];
		$instance['name'] 		= $new_instance['name'];
		$instance['numbar'] 	= $new_instance['numbar'];
		$instance['desc'] 		= $new_instance['desc'];

        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {

		// Defaults
		$instance = wp_parse_args( (array) $instance,
									array( 	'title' 	=> __( 'Sidebar Slideshow' , 'wp-photo-album-plus'),
											'album' 	=> '',
											'width' 	=> wppa_opt( 'widget_width' ),
											'height' 	=> round( wppa_opt( 'widget_width' ) * wppa_opt( 'maxheight' ) / wppa_opt( 'fullsize' ) ),
											'ponly' 	=> false,
											'linkurl' 	=> '',
											'linktitle' => '',
											'subtext' 	=> '',
											'supertext' => '',
											'valign' 	=> 'center',
											'timeout' 	=> '4',
											'film' 		=> false,
											'browse' 	=> false,
											'name' 		=> false,
											'numbar'	=> false,
											'desc' 		=> false,
											) );

		// Title
		echo
		wppa_widget_input( $this, 'title', $instance['title'], __( 'Title', 'wp-photo-album-plus' ) );

		// Album
		$body =
		'<option value="-2">' . __( '--- all ---', 'wp-photo-album-plus' ) . '</option>' .
		wppa_album_select_a( array ( 'selected' => $instance['album'], 'path' => wppa_switch( 'hier_albsel' ) ) );

		echo
		wppa_widget_selection_frame( $this, 'album', $body, __( 'Album', 'wp-photo-album-plus' ) );

		// Sizes and alignment
		echo
		__( 'Sizes and alignment', 'wp-photo-album-plus' ) . ':' .
		'<div style="padding:6px;border:1px solid lightgray;margin-top:2px;" >' .
			__( 'Enter the width and optionally the height of the area wherein the slides will appear. If you specify a 0 for the height, it will be calculated. The value for the height will be ignored if you set the vertical alignment to \'fit\'.', 'wp-photo-album-plus' ) .
			' ' .
			__( 'Tick the portrait only checkbox if there are only portrait images in the album and you want the photos to fill the full width of the widget.', 'wp-photo-album-plus' ) .
			' ' .
			__ ( 'If portrait only is checked, the vertical alignment will be forced to \'fit\'.', 'wp-photo-album-plus' );

			// Width
			echo
			wppa_widget_number( $this,
								'width',
								$instance['width'],
								__( 'Width in pixels', 'wp-photo-album-plus' ),
								'50',
								'500',
								'',
								'float'
								);

			// Height
			echo
			wppa_widget_number( $this,
								'height',
								$instance['height'],
								__( 'Height in pixels', 'wp-photo-album-plus' ),
								'50',
								'500',
								'',
								'float'
								);

			// Portrait only
			echo
			wppa_widget_checkbox( 	$this,
									'ponly',
									$instance['ponly'],
									__( 'Portrait only:', 'wp-photo-album-plus' )
									);

			// Vertical alignment
			$options = array(	__( 'top', 'wp-photo-album-plus' ),
								__( 'center', 'wp-photo-album-plus' ),
								__( 'bottom', 'wp-photo-album-plus' ),
								__( 'fit', 'wp-photo-album-plus' ),
								);
			$values  = array(	'top',
								'center',
								'bottom',
								'fit',
								);
			echo
			wppa_widget_selection( 	$this,
									'valign',
									$instance['valign'],
									__( 'Vertical alignment', 'wp-photo-album-plus' ),
									$options,
									$values,
									array(),
									'',
									__( 'Set the desired vertical alignment method.', 'wp-photo-album-plus')
									);


		echo
		'</div>';

		echo
		// Timeout
		wppa_widget_number( $this, 'timeout', $instance['timeout'], __( 'Slideshow timeout in seconds', 'wp-photo-album-plus' ), '1', '60' ) .

		// Linkurl
		wppa_widget_input( 	$this,
							'linkurl',
							$instance['linkurl'],
							__( 'Link to', 'wp-photo-album-plus' ),
							__( 'If you want that a click on the image links to another web address, type the full url here.', 'wp-photo-album-plus' )
							) .

		// Name
		wppa_widget_checkbox( $this, 'name', $instance['name'], __( 'Show name', 'wp-photo-album-plus' ) ) .

		// Description
		wppa_widget_checkbox( $this, 'desc', $instance['desc'], __( 'Show description', 'wp-photo-album-plus' ) ) .

		// Filmstrip
		wppa_widget_checkbox( $this, 'film', $instance['film'], __( 'Show filmstrip', 'wp-photo-album-plus' ) ) .

		// Browsebar
		wppa_widget_checkbox( $this, 'browse', $instance['browse'], __( 'Show browsebar', 'wp-photo-album-plus' ) ) .

		// Numbar
		wppa_widget_checkbox( $this, 'numbar', $instance['numbar'], __( 'Show number bar', 'wp-photo-album-plus' ) );

		// qTranslate supported textfields
		echo
		__( 'The following text fields support qTranslate', 'wp-photo-album-plus' ) .
		'<div style="padding:6px;border:1px solid lightgray;margin-top:2px;" >' .

			// Link title
			wppa_widget_input( $this, 'linktitle', $instance['linktitle'], __( 'Tooltip text', 'wp-photo-album-plus' ) ) .

			// Supertext
			wppa_widget_input( $this, 'supertext', $instance['supertext'], __( 'Text above photos', 'wp-photo-album-plus' ) ) .

			// Sutext
			wppa_widget_input( $this, 'subtext', $instance['subtext'], __( 'Text below photos', 'wp-photo-album-plus' ) ) .

		'</div>';

    }

} // class SlideshowWidget

// register SlideshowWidget widget
add_action('widgets_init', 'wppa_register_SlideshowWidget' );

function wppa_register_SlideshowWidget() {
	register_widget("SlideshowWidget");
}
