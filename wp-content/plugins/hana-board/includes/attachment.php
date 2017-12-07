<?php
if (! defined( 'ABSPATH' ))
	exit();

if (! function_exists( 'hanaboard_is_file_image' )) {
	
	/**
	 * Check if the file is a image
	 * 
	 * @since 0.1
	 * @param string $file
	 *        	url of the file to check
	 * @param string $mime
	 *        	mime type of the file
	 * @return bool
	 */
	function hanaboard_is_file_image($file, $mime) {
		$ext = preg_match( '/\.([^.]+)$/', $file, $matches ) ? strtolower( $matches [1] ) : false;
		
		$image_exts = array (
				'jpg',
				'jpeg',
				'gif',
				'png',
				'bmp',
				'tif',
				'tiff' 
		);
		
		if ('image/' == substr( $mime, 0, 6 ) || $ext && 'import' == $mime && in_array( $ext, $image_exts )) {
			return true;
		}
		
		return false;
	}
}
/**
 * Get all the image sizes
 * 
 * @return array image sizes
 */
if (! function_exists( 'hanaboard_get_image_sizes' )) {
	function hanaboard_get_image_sizes() {
		clearstatcache();
		$image_sizes_orig = get_intermediate_image_sizes();
		$image_sizes_orig [] = 'full';
		$image_sizes = array ();
		
		foreach ( $image_sizes_orig as $size ) {
			$image_sizes [$size] = $size;
		}
		return $image_sizes;
	}
}
if (! function_exists( 'hanaboard_get_filesize' )) {
	function hanaboard_get_filesize($file) {
		if (is_file( $file )) {
			$bytes = filesize( $file );
			$s = array (
					'B',
					'KB',
					'MB',
					'GB' 
			);
			$e = floor( log( $bytes ) / log( 1024 ) );
			return sprintf( '%.1f' . $s [$e], ($bytes / pow( 1024, floor( $e ) )) );
		}
		return false;
	}
}
/**
 * Adds/Removes mime types to wordpress
 * 
 * @param array $mime
 *        	original mime types
 * @return array modified mime types
 */
if (! function_exists( 'hanaboard_mime' )) {
	add_filter( 'upload_mimes', 'hanaboard_mime' );
	function hanaboard_mime($mime) {
		$unset = array (
				'php',
				'bin',
				'exe',
				'swf',
				'tsv',
				'wp|wpd',
				'onetoc|onetoc2|onetmp|onepkg',
				'class',
				'htm|html',
				'mdb',
				'mpp' 
		);
		$unset_from_option = @explode( ';', hanaboard_get_option( 'allowed_file_extensions' ) );
		$unset = array_merge( $unset, $unset_from_option );
		foreach ( $unset as $val ) {
			unset( $mime [$val] );
		}
		return $mime;
	}
}

if (! function_exists( 'restrict_mime_types_image_only' )) {
	// add_filter( 'upload_mimes', 'restrict_mime_types_image_only', 10, 5 );
	function restrict_mime_types_image_only($mime_types) {
		$term = hanaboard_get_current_term();
		
		if (! hanaboard_get_option( 'allow_attachment' )) {
			$mime_types = array (
					'jpg|jpeg|jpe' => 'image/jpeg',
					'gif' => 'image/gif',
					'png' => 'image/png',
					'bmp' => 'image/bmp',
					'tif|tiff' => 'image/tiff' 
			);
		}
		return $mime_types;
	}
}

if (! function_exists( 'hanaboard_filter_pdf' )) {
	add_filter( 'media_send_to_editor', 'hanaboard_filter_pdf', 20, 3 );
	function hanaboard_filter_pdf($html, $id) {
		$attachment = get_post( $id ); // fetching attachment by $id passed
		                               // through
		
		$mime_type = $attachment->post_mime_type; // getting the mime-type
		if ($mime_type == 'application/pdf') { // checking mime-type
			$src = wp_get_attachment_url( $id );
			
			$html = '<iframe width="500" height="500" src="' . $src . '"></iframe>';
			return $html; // return new $html
		}
		return $html;
	}
}

if (! function_exists( 'hanaboard_send_to_editor_not_media' )) {
	add_filter( 'media_send_to_editor', 'hanaboard_send_to_editor_not_media', 20, 5 );
	function hanaboard_send_to_editor_not_media($html, $id) {
		$attachment = get_post( $id ); // fetching attachment by $id passed
		                               // through
		
		$mime_type = $attachment->post_mime_type; // getting the mime-type
		if (! hanaboard_is_file_image( $attachment->guid, $mime_type ) && $mime_type != 'application/pdf') {
			$src = wp_get_attachment_url( $id );
			$html = '<span class="attachment_file">' . $html . '</span>';
		}
		return $html;
	}
}
/**
 * Upload the files to the post as attachemnt
 * 
 * @param <type> $post_id        	
 */
if (! function_exists( 'hanaboard_upload_attachment' )) {
	function hanaboard_upload_attachment($post_id) {
		if (! isset( $_FILES ['hanaboard_post_attachments'] )) {
			return false;
		}
		
		$fields = ( int ) hanaboard_get_option( 'attachment_num' );
		
		for($i = 0; $i < $fields; $i ++) {
			$file_name = basename( $_FILES ['hanaboard_post_attachments'] ['name'] [$i] );
			
			if ($file_name) {
				$upload = array (
						'name' => $_FILES ['hanaboard_post_attachments'] ['name'] [$i],
						'type' => $_FILES ['hanaboard_post_attachments'] ['type'] [$i],
						'tmp_name' => $_FILES ['hanaboard_post_attachments'] ['tmp_name'] [$i],
						'error' => $_FILES ['hanaboard_post_attachments'] ['error'] [$i],
						'size' => $_FILES ['hanaboard_post_attachments'] ['size'] [$i] 
				);
				hanaboard_upload_file( $upload );
			} // file exists
		} // end for
	}
}

if (! function_exists( 'hanaboard_get_attachment_info' )) {
	function hanaboard_get_attachment_info($attach_id) {
		$attachment = get_post( $attach_id );
		$url = wp_get_attachment_url( $attach_id );
		$wp_upload_dir = wp_upload_dir();
		$filesize = hanaboard_get_filesize( $wp_upload_dir ['path'] . '/' . basename( $url ) );
		
		$info = array (
				'id' => $attachment->ID,
				'title' => $attachment->post_title,
				'post_title' => $attachment->post_title,
				'filename' => basename( $url ),
				'url' => $url,
				'mime' => $attachment->post_mime_type,
				'filesize' => $filesize 
		);
		return ( object ) $info;
	}
}

/**
 * Generic function to upload a file
 * 
 * @since 0.1
 * @param string $field_name
 *        	file input field name
 * @return bool|int attachment id on success, bool false instead
 */
if (! function_exists( 'hanaboard_upload_file' )) {
	function hanaboard_upload_file($upload_data) {
		$uploaded_file = wp_handle_upload( $upload_data, array (
				'test_form' => false 
		) );
		
		// If the wp_handle_upload call returned a local path for the image
		if (isset( $uploaded_file ['file'] )) {
			$file_loc = $uploaded_file ['file'];
			$file_name = basename( $upload_data ['name'] );
			$file_type = wp_check_filetype( $file_name );
			$attachment = array (
					'post_mime_type' => $file_type ['type'],
					'post_title' => basename( $upload_data ['name'] ),
					'post_content' => '',
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'comment_status' => 'closed' 
			);
			
			$attach_id = wp_insert_attachment( $attachment, $file_loc );
			
			$attach_data = @wp_generate_attachment_metadata( $attach_id, $file_loc );
			
			wp_update_attachment_metadata( $attach_id, $attach_data );
			
			return $attach_id;
		}
		return false;
	}
}

/**
 * Checks the submitted files if has any errors
 * 
 * @return array error list
 */
if (! function_exists( 'hanaboard_check_upload' )) {
	function hanaboard_check_upload() {
		global $errors;
		if (! is_wp_error( $errors ))
			$errors = new WP_Error();
		$mime = get_allowed_mime_types();
		
		$size_limit = ( int ) (hanaboard_get_option( 'attachment_max_size' ) * 1024);
		$fields = ( int ) hanaboard_get_option( 'attachment_num' );
		
		for($i = 0; $i < $fields; $i ++) {
			$tmp_name = basename( $_FILES ['hanaboard_post_attachments'] ['tmp_name'] [$i] );
			$file_name = basename( $_FILES ['hanaboard_post_attachments'] ['name'] [$i] );
			
			// if file is uploaded
			if ($file_name) {
				$attach_type = wp_check_filetype( $file_name );
				$attach_size = $_FILES ['hanaboard_post_attachments'] ['size'] [$i];
				
				// check file size
				if ($attach_size > $size_limit) {
					$wp_error->add( 'large_file_size', __( "Attachment file is too big", HANA_BOARD_TEXT_DOMAIN ) );
				}
				
				// check file type
				if (! in_array( $attach_type ['type'], $mime )) {
					$wp_error->add( 'invalid_file_type', __( "Invalid attachment file type", HANA_BOARD_TEXT_DOMAIN ) );
				}
			} // if $filename
		} // endfor
	}
	add_action( 'hanaboard_submit_check_errors', 'hanaboard_check_upload' );
}
/**
 * Get the attachments of a post
 * 
 * @param int $post_id        	
 * @return array attachment list
 */
if (! function_exists( 'hanaboard_get_attachments' )) {
	function hanaboard_get_attachments($post_id = null) {
		$att_list = array ();
		
		if (is_hanaboard_page( 'write_reply' ))
			return $att_list;
		
		if (! $post_id) {
			$post_id = get_the_ID();
		}
		
		$args = array (
				'post_type' => 'attachment',
				'numberposts' => - 1,
				'post_status' => null,
				'post_parent' => $post_id,
				'order' => 'ASC',
				'orderby' => 'menu_order' 
		);
		$attachments = get_posts( $args );
		$featured_image = get_post_thumbnail_id( $post_id );
		
		foreach ( $attachments as $attachment ) {
			if (! hanaboard_get_option( 'show_images_on_attachments_list' )) {
				if (strpos( $attachment->post_mime_type, 'image/' ) !== false)
					continue;
			}
			$att_list [] = hanaboard_get_attachment_info( $attachment->ID );
		}
		return $att_list;
	}
}

if (! function_exists( 'has_post_attachment' )) {
	function has_post_attachment($post_id = null) {
		$att_list = hanaboard_get_attachments( $post_id );
		if (count( $att_list ))
			return true;
		return false;
	}
}

/**
 * Attachments preview on edit page
 * 
 * @param int $post_id        	
 */
if (! function_exists( 'hanaboard_edit_attachment' )) {
	function hanaboard_edit_attachment($post_id) {
		$attach = hanaboard_get_attachments( $post_id );
		if ($attach) {
			$count = 1;
			foreach ( $attach as $a ) {
				echo 'Attachment ' . $count . ': <a href="' . $a ['url'] . '">' . $a ['title'] . '</a>';
				echo "<form name=\"hanaboard_edit_attachment\" id=\"hanaboard_edit_attachment_{$post_id}\" action=\"\" method=\"POST\">";
				echo "<input type=\"hidden\" name=\"attach_id\" value=\"{$a['id']}\" />";
				echo "<input type=\"hidden\" name=\"action\" value=\"del\" />";
				wp_nonce_field( 'hanaboard_attach_del' );
				echo '<input class="attachment-delete" type="submit" name="hanaboard_attachment_delete" value="delete" onclick="return confirm(\'Are you sure to delete this attachment?\');">';
				echo "</form>";
				echo "<br>";
				$count ++;
			}
		}
	}
}

if (! function_exists( 'hanaboard_attachment_fields' )) {
	function hanaboard_attachment_fields($edit = false, $post_id = false) {
		if (hanaboard_get_option( 'allow_attachment' ) || hanaboard_get_option( 'allow_upload_media' )) {
			$fields = ( int ) hanaboard_get_option( 'attachment_num' );
			if ($edit && $post_id) {
				$fields = abs( $fields - count( hanaboard_get_attachments( $post_id ) ) );
			}
			for($i = 0; $i < $fields; $i ++) {
				?>
<div>
	<label for="hanaboard_post_attachments">
						Attachment <?php echo $i + 1; ?>:
					</label>
	<input type="file" name="hanaboard_post_attachments[]">
	<div class="clear"></div>
</div>
<?php
			}
		}
	}
}

/**
 * Attachment Uploader class
 * 
 * @since 0.1
 * @package
 *
 */
class HANA_BOARD_Attachment {
	function __construct() {
		add_action( 'hanaboard_add_post_form_attachments', array (
				&$this,
				'add_post_fields' 
		), 10, 1 );
		add_action( 'wp_enqueue_scripts', array (
				&$this,
				'scripts' 
		) );
		add_action( 'wp_ajax_hanaboard_attach_upload', array (
				&$this,
				'upload_file' 
		) );
		add_action( 'wp_ajax_hanaboard_upload_error_message', array (
				&$this,
				'upload_error_message' 
		) );
		add_action( 'wp_ajax_hanaboard_attach_del', array (
				&$this,
				'delete_file' 
		) );
		
		add_action( 'wp_ajax_nopriv_hanaboard_attach_upload', array (
				&$this,
				'upload_file' 
		) );
		add_action( 'wp_ajax_nopriv_hanaboard_upload_error_message', array (
				&$this,
				'upload_error_message' 
		) );
		add_action( 'wp_ajax_nopriv_hanaboard_attach_del', array (
				&$this,
				'delete_file' 
		) );
		
		add_action( 'hanaboard_add_post_after_insert', array (
				&$this,
				'attach_file_to_post' 
		) );
		add_action( 'hanaboard_edit_post_after_update', array (
				&$this,
				'attach_file_to_post' 
		) );
	}
	function scripts() {
		if (! is_hanaboard_page( 'form' ))
			return;
		
		$max_file_size = intval( hanaboard_get_option( 'attachment_max_size' ) ) * 1024;
		$max_upload = intval( hanaboard_get_option( 'attachment_num' ) );
		$allow_attachment = (hanaboard_get_option( 'allow_attachment' )) ? true : false;
		$allow_upload_media = (hanaboard_get_option( 'allow_upload_meida' )) ? true : false;
		
		$image_ext = array (
				'jpg',
				'jpeg',
				'gif',
				'png',
				'bmp',
				'tif',
				'tiff' 
		);
		if ($allow_attachment) {
			$allow_extensions = "*";
		} else
			$allow_extensions = join( ',', $image_ext );
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_print_scripts( 'editor' );
		wp_print_scripts( 'media-upload' );
		wp_enqueue_script( 'plupload-handlers' );
		
		wp_enqueue_script( 'hanaboard_attachment', plugins_url( 'js/attachment.js', dirname( __FILE__ ) ), array (
				'jquery',
				'media-upload' 
		), HANA_BOARD_VERSION, true );
		
		if (function_exists( 'wp_tiny_mce' ))
			wp_tiny_mce();
		
		$max_file_size = (( int ) hanaboard_get_option( 'attachment_max_size' ) > 512) ? hanaboard_get_option( 'attachment_max_size' ) . 'kb' : "10mb";
		// 'runtimes' => 'html5,flash,silverlight,html4',
		$plupload_params = array (
				'runtimes' => 'html5,browserplus,silverlight,flash,gears,html4',
				'browse_button' => 'hanaboard-attachment-upload-pickfiles',
				'file_data_name' => 'hanaboard_attachment_file',
				'autostart' => true,
				'max_file_size' => $max_file_size,
				'flash_swf_url' => '/wp-includes/js/plupload/plupload.flash.swf',
				'silverlight_xap_url' => '/wp-includes/js/plupload/plupload.silverlight.xap',
				
				'url' => admin_url( 'admin-ajax.php' ) . '?action=hanaboard_attach_upload&nonce=' . wp_create_nonce( 'hanaboard_attachment_file' ),
				'filters' => array (
						array (
								'title' => __( 'Allowed Files', HANA_BOARD_TEXT_DOMAIN ),
								'extensions' => $allow_extensions 
						) 
				),
				'multipart' => true 
		);
		wp_localize_script( 'hanaboard_attachment', 'hanaboard_attachment', array (
				'nonce' => wp_create_nonce( 'hanaboard_attachment' ),
				'use_media_library' => hanaboard_get_option( 'use_media_library' ),
				'number' => $max_upload,
				'attachment_enabled' => 1,
				'msg' => array (
						'Delete' => __( 'Delete', HANA_BOARD_TEXT_DOMAIN ),
						'deleteConfirmMsg' => __( 'Are you sure to delete attachment?', HANA_BOARD_TEXT_DOMAIN ) 
				),
				'plupload' => $plupload_params,
				'error_handler' => array (
						'ajaxurl' => admin_url( 'admin-ajax.php' ) . '?action=hanaboard_upload_error_message&nonce=' . wp_create_nonce( 'hanaboard_upload_error' ) 
				),
				'path' => array (
						'hanaboard_image_path' => hanaboard_plugins_url( 'images/' ) 
				) 
		) );
	}
	function add_post_fields($post_id = null) {
		$attachments = array ();
		if (! $post_id)
			$post_id = get_the_ID();
		if ($post_id > 0 && is_hanaboard_page( 'edit' ))
			$attachments = hanaboard_get_attachments( $post_id );
		if (is_array( $attachments ) && sizeof( $attachments )) {
			foreach ( $attachments as $attach ) {
				echo $this->attach_html( $attach->id );
			}
		}
	}
	function upload_file() {
		check_ajax_referer( 'hanaboard_attachment_file', 'nonce' );
		$upload = array (
				'name' => $_FILES ['hanaboard_attachment_file'] ['name'],
				'type' => $_FILES ['hanaboard_attachment_file'] ['type'],
				'tmp_name' => $_FILES ['hanaboard_attachment_file'] ['tmp_name'],
				'error' => $_FILES ['hanaboard_attachment_file'] ['error'],
				'size' => $_FILES ['hanaboard_attachment_file'] ['size'] 
		);
		
		// Block file extensions from board option
		$array = explode( '.', $_FILES ['hanaboard_attachment_file'] ['name'] );
		$extension = end( $array );
		$block_file_extensions = hanaboard_get_option( 'block_file_extensions' );
		$block_file_extensions = explode( ';', $block_file_extensions );
		if (in_array( $extension, $block_file_extensions )) {
			$response = array (
					'success' => false,
					'attach_data' => null,
					'errorMsg' => sprintf( __( 'Uploading file extension \'%s\' is not allowed.', HANA_BOARD_TEXT_DOMAIN ), $extension ) 
			);
		}
		$attach_id = hanaboard_upload_file( $upload );
		if ($attach_id) {
			$response = array (
					'success' => true,
					'attach_data' => $this->attach_data( $attach_id ) 
			);
			echo json_encode( $response );
			exit();
		}
		
		$response = array (
				'success' => false 
		);
		echo json_encode( $response );
		exit();
	}
	function upload_error_message() {
		check_ajax_referer( 'hanaboard_upload_error', 'nonce' );
		
		$errorType = $_POST ['errorType'];
		$response = array ();
		if ($errorType == 'permission') {
			$response ['code'] = "550";
			$response ['message'] = __( 'No Permission. Access to file is denied.', HANA_BOARD_TEXT_DOMAIN );
		} else {
			$response ['code'] = "500";
			$response ['message'] = $errorType . __( 'Upload Error. Ask to webmaster.', HANA_BOARD_TEXT_DOMAIN );
		}
		echo json_encode( $response );
		exit();
	}
	function attach_data($attach_id) {
		$img_class = "";
		$att = hanaboard_get_attachment_info( $attach_id );
		if ($this->is_image( $attach_id )) {
			$default_image_size = hanaboard_get_option( 'default_image_size' );
			$attachment_img_src = wp_get_attachment_image_src( $attach_id, HANA_BOARD_CONTENT_IMAGE_SIZE, false, $img_class );
			$attachment_full_img_src = wp_get_attachment_image_src( $attach_id, 'full', false, $img_class );
			$image_meta = wp_get_attachment_metadata( $attach_id );
		}
		$res = array (
				'attach_id' => $attach_id,
				'filename' => urldecode( $att->title ),
				'filesize' => $att->filesize,
				'mime' => $att->mime,
				'is_image' => $this->is_image( $attach_id ) 
		);
		if ($res ['is_image']) {
			$res ['url'] = $attachment_img_src [0];
			$res ['url_full'] = $attachment_full_img_src [0];
		}
		return $res;
	}
	function attach_html($attach_id) {
		$attachment = hanaboard_get_attachment_info( $attach_id );
		$html = '';
		$html .= '<li class="hanaboard-attachment">';
		$html .= '<span class="handle"></span>';
		$html .= sprintf( '<input type="hidden" name="hanaboard_attach_title[]" value="%s" />', esc_attr( $attachment->post_title ) );
		$html .= sprintf( '<span class="attachment-name">%s</span>', urldecode( esc_attr( $attachment->post_title ) ) );
		$html .= sprintf( '<span class="attachment-size">(%s)</span>', esc_attr( $attachment->filesize ) );
		if (! $this->is_image( $attach_id )) {
			// $html .= sprintf( '<span class="attachment-actions"><a href="#"
			// class="attachment-media-to-content hanaboard-button"
			// data-attach_id="%d">%s</a></span>', $attach_id, __( 'Insert to
			// content', 'hanaboard' ) );
		}
		$html .= sprintf( '<span class="attachment-actions"><a href="#" class="attachment-delete hanaboard-button" data-attach_id="%d">%s</a></span>', $attach_id, __( 'Delete', HANA_BOARD_TEXT_DOMAIN ) );
		$html .= sprintf( '<input type="hidden" name="hanaboard_attach_id[]" value="%d" />', $attach_id );
		$html .= '</li>';
		
		return $html;
	}
	function is_image($attach_id) {
		$attachment = hanaboard_get_attachment_info( $attach_id );
		$file = $attachment->url;
		$mime = $attachment->mime;
		
		$ext = preg_match( '/\.([^.]+)$/', $file, $matches ) ? strtolower( $matches [1] ) : false;
		$image_exts = array (
				'jpg',
				'jpeg',
				'gif',
				'png',
				'bmp',
				'tif',
				'tiff' 
		);
		if ('image/' == substr( $mime, 0, 6 ) || $ext && 'import' == $mime && in_array( $ext, $image_exts )) {
			return true;
		}
		return false;
	}
	function html_send_to_content($attach_id) {
		$att = hanaboard_get_attachment_info( $attach_id );
		$img_class = array (
				'class' => 'alignnone size-large wp-image-' . $attach_id 
		);
		$html = '';
		if ($this->is_image( $attach_id )) {
			$default_image_size = 'hana-thumb-1000';
			$attachment_img_src = wp_get_attachment_image_src( $attach_id, $default_image_size, false, $img_class );
			$image_meta = wp_get_attachment_metadata( $attach_id );
			$html .= sprintf( '<a href="%1$s" data-mce-href="%1$s" rel="alinnone attachment wp-att-%2$d" data-lightbox="hanaboard">', $attachment_img_src [0], true );
			// $html .= sprintf( '<img src="%s" alt="%s" class="alignnone
			// size-%s wp-image-%s" /></a>', $image_url, $default_image_size,
			// urldecode($att->title), $attach_id);
			$html .= wp_get_attachment_image( $attach_id, 'large', false, $img_class );
			$html .= "</a>";
		}
		/*
		 * if ( $this->is_image( $attach_id ) ) {
		 * $default_image_size = hanaboard_get_option( 'default_image_size',
		 * 'large' );
		 * $html .= sprintf( '<a href="%s" rel="attachment wp-att-%s">',
		 * get_attachment_link( $attach_id ), $attach_id );
		 * // $html .= sprintf( '<img src="%s" alt="%s" class="alignnone size-%s
		 * wp-image-%s" /></a>', wp_get_attachment_image($attach_id,
		 * $size=$default_image_size, $icon = false), $default_image_size,
		 * urldecode($att->title), $attach_id);
		 * $html .= wp_get_attachment_image($attach_id, 'large', false,
		 * $img_class );
		 * $html .= "</a>";
		 * }
		 */
		
		return $html;
	}
	function delete_file() {
		check_ajax_referer( 'hanaboard_attachment', 'nonce' );
		
		$attach_id = isset( $_POST ['attach_id'] ) ? intval( $_POST ['attach_id'] ) : 0;
		$attachment = get_post( $attach_id );
		
		// post author or editor role
		if (get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' )) {
			wp_delete_attachment( $attach_id, true );
			echo 'success';
		}
		
		exit();
	}
	function attach_file_to_post($post_id) {
		$posted = $_POST;
		
		if (isset( $posted ['hanaboard_attach_id'] )) {
			foreach ( $posted ['hanaboard_attach_id'] as $index => $attach_id ) {
				$postarr = array (
						'ID' => $attach_id,
						'post_title' => $posted ['post_title'] [$index],
						'post_title' => $posted ['hanaboard_attach_title'] [$index],
						'post_parent' => $post_id,
						'menu_order' => $index 
				);
				wp_update_post( $postarr );
			}
		}
	}
}

$hanaboard_attachment = new HANA_BOARD_Attachment();
