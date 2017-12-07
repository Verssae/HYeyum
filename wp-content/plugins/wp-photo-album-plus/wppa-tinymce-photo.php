<?php
/* wppa-tinymce-photo.php
* Pachkage: wp-photo-album-plus
*
* Version 6.7.08
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

class wppaPhoto
{
    function __construct() {
		add_action( 'init', array( $this, 'action_admin_init' ) ); // 'admin_init'
	}

	function action_admin_init() {
		// only hook up these filters if we're in the admin panel, and the current user has permission
		// to edit posts or pages, and the feature is enabled
		// Only activate if the [photo] shortcode is enabled
		if ( wppa_switch( 'photo_shortcode_enabled' ) ) {
			if ( wppa_user_is( 'administrator' ) || ( wppa_switch( 'enable_generator' ) && ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) ) ) {
				add_filter( 'mce_buttons', array( $this, 'filter_mce_button' ) );
				add_filter( 'mce_external_plugins', array( $this, 'filter_mce_plugin' ) );
			}
		}
	}

	function filter_mce_button( $buttons ) {
		// add a separation before our button.
		array_push( $buttons, ' ', 'myphoto_button' );
		return $buttons;
	}

	function filter_mce_plugin( $plugins ) {
		// this plugin file will work the magic of our button
		$file = 'js/wppa-tinymce-photo.js';
		$plugins['wppaphoto'] = plugin_dir_url( __FILE__ ) . $file;
		return $plugins;
	}

}

$wppaphoto = new wppaPhoto();

add_action('admin_head', 'wppa_inject_2_js');

function wppa_inject_2_js() {
global $wppa_api_version;

	// Things that wppa-tinymce.js AND OTHER MODULES!!! need to know
	echo('<script type="text/javascript">'."\n");
	echo('/* <![CDATA[ */'."\n");
		echo("\t".'wppaImageDirectory = "'.wppa_get_imgdir().'";'."\n");
		echo("\t".'wppaAjaxUrl = "'.admin_url('admin-ajax.php').'";'."\n");
		echo("\t".'wppaPhotoDirectory = "'.WPPA_UPLOAD_URL.'/";'."\n");
		echo("\t".'wppaThumbDirectory = "'.WPPA_UPLOAD_URL.'/thumbs/";'."\n");
		echo("\t".'wppaTempDirectory = "'.WPPA_UPLOAD_URL.'/temp/";'."\n");
		echo("\t".'wppaFontDirectory = "'.WPPA_UPLOAD_URL.'/fonts/";'."\n");
		echo("\t".'wppaNoPreview = "'.__('No Preview available', 'wp-photo-album-plus').'";'."\n");
		echo("\t".'wppaVersion = "'.$wppa_api_version.'";'."\n");
		echo("\t".'wppaSiteUrl = "'.site_url().'";'."\n");
		echo("\t".'wppaWppaUrl = "'.WPPA_URL.'";'."\n");
		echo("\t".'wppaIncludeUrl = "'.trim(includes_url(), '/').'";'."\n");
		echo("\t".'wppaUIERR = "'.__('Unimplemented virtual album', 'wp-photo-album-plus').'";');
		echo("\t".'wppaTxtProcessing = "'.__('Processing...', 'wp-photo-album-plus').'";');
		echo("\t".'wppaTxtDone = "'.__('Done!', 'wp-photo-album-plus').'";');
		echo("\t".'wppaTxtErrUnable = "'.__( 'ERROR: unable to upload files.', 'wp-photo-album-plus' ).'";');
//		echo("\t".'wppaTxt = "'.__(, 'wp-photo-album-plus').'";');
//		echo("\t".'wppaTxt = "'.__(, 'wp-photo-album-plus').'";');
//		echo("\t".'wppaTxt = "'.__(, 'wp-photo-album-plus').'";');
	echo("/* ]]> */\n");
	echo("</script>\n");
}

function wppa_make_tinymce_photo_dialog() {
global $wpdb;

	// Prepare albuminfo
	$albums = $wpdb->get_results( 	"SELECT `id`, `name` " .
									"FROM `" . WPPA_ALBUMS . "` " .
									"WHERE `owner` = '" . wppa_get_user() . "' " .
									"OR `owner` = '--- public ---' " .
									"ORDER BY `name` ",
									ARRAY_A );

	// Make the html
	// Open wrapper
	$result =
	'<div id="wppaphoto-form">' .
		'<style>
			#TB_ajaxContent {
				box-sizing:border-box; width:100% !important;
			}
			.wppa-bar {
				background-color: #7f7;
				width:0%;
				height:18px;
				border-radius: 3px;
				line-height: 18px;
				margin: 0;
			}
			.wppa-percent {
				position:relative;
				display:inline-block;
				top:-19px;
				font-size: 12px;
				line-height: 18px;
				margin: 0;
			}
			.wppa-message {
				clear: both;
			}
			#wppaphoto-table tr, #wppaphoto-table th, #wppaphoto-table td {
				padding: 2px; 0;
			}
		</style>' .

		// Open table
		'
		<table id="wppaphoto-table" class="form-table" >' .
			'<tbody>' .

				// My photos selection
				'
				<tr id="wppaphoto-myphoto-tr" style="" >' .
					'<th><label for="wppaphoto-myphoto" class="wppaphoto-myphoto" >'.__('My Photo to be used', 'wp-photo-album-plus').':</label></th>'.
					'<td>'.
						'<select id="wppaphoto-myphoto" name="photo" class="wppaphoto-myphoto" onchange="wppaPhotoEvaluate()" >' .
							wppa_get_myphotos_selection_body_for_tinymce() .
						'</select>'.
						'<input' .
							' type="button"' .
							' value="' . esc_attr( __( 'All photos', 'wp-photo-album-plus' ) ) . '"' .
							' onclick="jQuery(\'#wppaphoto-myphoto-tr\').hide();jQuery(\'#wppaphoto-allphoto-tr\').show();wppaMyPhotoSelection=false;jQuery(\'#wppaphoto-photo-preview\').html(\'\');wppaPhotoEvaluate();"' .
						' />' .
						'<br />'.
						'<small style="" class="wppamyphoto-photo" >'.
							__('Specify the photo to be used', 'wp-photo-album-plus').'<br />'.
							__('You can select one of your photos from a maximum of 100 most recently added', 'wp-photo-album-plus').'<br />'.
						'</small>'.
					'</td>'.
				'</tr>' .

				// Photo selection max 100 of all photos
				'
				<tr id="wppaphoto-allphoto-tr" style="display:none;" >'.
					'<th><label for="wppaphoto-allphoto" class="wppaphoto-allphoto" >'.__('The Photo to be used', 'wp-photo-album-plus').':</label></th>'.
					'<td>'.
						'<select id="wppaphoto-allphoto" name="photo" class="wppaphoto-allphoto" onchange="wppaPhotoEvaluate()" >' .
							wppa_get_allphotos_selection_body_for_tinymce() .
						'</select>' .
						'<br />' .
						'<small style="" class="wppaphoto-allphoto" >'.
							__('Specify the photo to be used', 'wp-photo-album-plus').'<br />'.
							__('You can select from a maximum of 100 most recently added photos', 'wp-photo-album-plus').'<br />'.
						'</small>'.
					'</td>'.
				'</tr>'.

				// Photo preview
				'
				<tr id="wppaphoto-photo-preview-tr" style="" >'.
					'<th>' .
						__( 'Preview image', 'wp-photo-album-plus').':' .
					'</th>'.
					'<td id="wppaphoto-photo-preview" style="text-align:center;" >' .
					'</td>' .
				'</tr>' .

				// Upload new photo dialog
				( count( $albums ) > 0 ?
					'
					<tr>' .
						'<th>' .
							'<a' .
								' style="cursor:pointer;"' .
								' onclick="jQuery(\'#upload-td\').show();jQuery( \'#wppa-user-upload\' ).click();"' .
								' >' .
							__( 'Upload new photo', 'wp-photo-album-plus' ) . ':' .
							'</a>' .
						'</th>'.
						'<td id="upload-td" style="display:none;" >' .

							// Open form
							'<form' .
								' id="wppa-uplform"' .
								' action="' . WPPA_URL . '/wppa-ajax-front.php?action=wppa&amp;wppa-action=do-fe-upload&amp;fromtinymce=1"' .
								' method="post"' .
								' enctype="multipart/form-data"' .
								' >' .
								wppa_nonce_field( 'wppa-check' , 'wppa-nonce', false, false ) .

								// Single album
								( ( count( $albums ) == 1 ) ?

									'<input' .
										' type="hidden"' .
										' id="wppa-upload-album"' .
										' name="wppa-upload-album"' .
										' value="' . $albums[0]['id'] . '"' .
									' />' .

									__( 'Upload to album', 'wp-photo-album-plus' ) . ': <b>' . wppa_get_album_name( $albums[0]['id'] ) . '</b>' :


									// Multiple albums
									__( 'Upload to album', 'wp-photo-album-plus' ) . ':' .
									wppa_album_select_a( array( 	'tagid' 			=> 'wppa-upload-album',
																	'tagname' 			=> 'wppa-upload-album',
																	'tagopen' 			=> '<select' .
																								' id="wppa-upload-album"' .
																								' name="wppa-upload-album"' .
																								' style="max-width:300px;"' .
																								' >' ,
																	'addpleaseselect' 	=> true,
																	'checkupload' 		=> true,
																	'checkowner' 		=> true,

																				) ) ) .

								// The (hidden) functional button
								'
								<input' .
									' type="file"' .
									' style="' .
										'display:none;' .
										'"' .
									' id="wppa-user-upload"' .
									' name="wppa-user-upload"' .
									' onchange="jQuery( \'#wppa-user-upload-submit\' ).css( \'display\', \'block\' );wppaDisplaySelectedFile(\'wppa-user-upload\', \'wppa-user-upload-submit\');"' .
								' />' .

								// The upload submit button
								'
								<input' .
									' type="submit"' .
									' id="wppa-user-upload-submit"' .
									' onclick="if ( document.getElementById( \'wppa-upload-album\' ).value == 0 )' .
											' {alert( \''.esc_js( __( 'Please select an album and try again', 'wp-photo-album-plus' ) ).'\' );return false;}"' .
									' style="display:none;margin: 6px 0;"' .
									' class="wppa-user-upload-submit"' .
									' name="wppa-user-upload-submit"' .
									' value=""' .
								' />' .

								// The progression bar
								'
								<div' .
									' id="progress"' .
									' class="wppa-progress "' .
									' style="clear:both;width:70%;border-color:#777;height:18px;border:1px solid;padding:1px;border-radius:3px;line-height: 18px;text-align: center;"' .
									' >' .
									'<div id="bar" class="wppa-bar" ></div>' .
									'<div id="percent" class="wppa-percent" >0%</div >' .
								'</div>' .
								'<div id="message" class="wppa-message" ></div>' .


							// Form complete
							'</form>' .
						'</td>' .
					'</tr>' : '' ) .

				// Shortcode preview
				'
				<tr>' .
					'<th>' .
						__( 'Shortcode', 'wp-photo-album-plus' ) . ':' .
					'</th>' .
					'<td id="wppaphoto-shortcode-preview-container" >' .
						'<input type="text" id="wppaphoto-shortcode-preview" style="background-color:#ddd; width:100%; height:26px;" value="[photo]" />' .
					'</td>' .
				'</tr>' .

			'</tbody>' .

		'</table>' .

		// Insert shortcode button
		'
		<p class="submit">'.
			'<input type="button" id="wppaphoto-submit" class="button-primary" value="'.__( 'Insert Photo', 'wp-photo-album-plus').'" name="submit" />&nbsp;'.
			'<input type="button" id="wppaphoto-submit-notok" class="button-secundary" value="'.__( 'Insert Photo', 'wp-photo-album-plus').'" onclick="alert(\''.esc_js(__('Please select a photo', 'wp-photo-album-plus')).'\')" />&nbsp;'.
		'</p>' .

		// Initial evaluate
		'<script type="text/javascript" >wppaPhotoEvaluate()</script>' .

	// Close main wrapper
	'
	</div>';

	return $result;
}

// The my photos selection box body
function wppa_get_myphotos_selection_body_for_tinymce( $selected = 0 ) {
global $wpdb;

	// Init
	$result = '';

	// Prepare photoinfo
	$my_photos = $wpdb->get_results( 	"SELECT `id`, `name`, `album`, `ext` " .
										"FROM `" . WPPA_PHOTOS . "` " .
										"WHERE `owner` = '" . wppa_get_user() . "' " .
										"ORDER BY `timestamp` DESC LIMIT 100",
										ARRAY_A );

	if ( $my_photos ) {

		// Please select
		$result .= 	'<option' .
						' value=""' .
						' disabled="disabled"' .
						( $selected ? '' : ' selected="selected"' ) .
						' style="color:#700"' .
						' >' .
						'-- ' .	__( 'Please select a photo', 'wp-photo-album-plus' ) . ' --' .
					'</option>';

		// Most recent 100 photos of this owner
		foreach ( $my_photos as $photo ) {

			// Find name
			$name = stripslashes( __( $photo['name'] ) );
			if ( strlen( $name ) > '50' ) $name = substr( $name, '0', '50' ) . '...';

			// Make the html
			if ( get_option( 'wppa_file_system' ) == 'flat' ) {
				$result .= 	'<option' .
								' value="' . wppa_fix_poster_ext( $photo['id'] . '.' . $photo['ext'], $photo['id'] ) . '"' .
								( $selected == $photo['id'] ? ' selected="selected"' : '' ) .
								' >' .
								$name .
								' (' . wppa_get_album_name( $photo['album'] ) . ')' .
							'</option>';
			}
			else {
				$result .= 	'<option' .
								' value="' . wppa_fix_poster_ext( wppa_expand_id( $photo['id'] ) . '.' . $photo['ext'], $photo['id'] ) . '"' .
								( $selected == $photo['id'] ? ' selected="selected"' : '' ) .
								' >' .
								$name .
								' (' . wppa_get_album_name( $photo['album'] ) . ')' .
							'</option>';
			}
		}
	}
	else {
		$result .= 	'<option value="0" >' .
						__( 'You have no photos yet', 'wp-photo-album-plus' ) .
					'</option>';
	}

	return $result;
}

// The my photos selection box body
function wppa_get_allphotos_selection_body_for_tinymce() {
global $wpdb;

	// Init
	$result = '';

	// Prepare photoinfo
	$all_photos = $wpdb->get_results( 	"SELECT `id`, `name`, `album`, `ext` " .
										"FROM `" . WPPA_PHOTOS . "` " .
										"ORDER BY `timestamp` DESC LIMIT 100",
										ARRAY_A );

	if ( $all_photos ) {

		// Please select
		$result .= 	'<option' .
						' value=""' .
						' disabled="disabled"' .
						' selected="selected"' .
						' style="color:#700"' .
						' >' .
						'-- ' . __( 'Please select a photo', 'wp-photo-album-plus' ) . ' --' .
					'</option>';

		// Most recent 100 photos of this owner
		foreach ( $all_photos as $photo ) {
			$name = stripslashes(__($photo['name']));
			if ( strlen($name) > '50') $name = substr($name, '0', '50').'...';
			if ( get_option( 'wppa_file_system' ) == 'flat' ) {
				$result .= 	'<option' .
								' value="' . wppa_fix_poster_ext( $photo['id'] . '.' . $photo['ext'], $photo['id'] ) . '"' .
								' >' .
								$name .
								' (' . wppa_get_album_name( $photo['album'] ) . ')' .
							'</option>';
			}
			else {
				$result .= 	'<option' .
								' value="' . wppa_fix_poster_ext( wppa_expand_id( $photo['id'] ) . '.' . $photo['ext'], $photo['id'] ) . '"' .
								' >' .
								$name .
								' (' . wppa_get_album_name( $photo['album'] ) . ')' .
							'</option>';
			}
		}
	}
	else {
		$result .= 	'<option value="0" >' .
						__( 'There are no photos yet', 'wp-photo-album-plus' ) .
					'</option>';
	}

	return $result;
}