<?php


class HanaBoard_Ajax {
	function __construct() {

		
		add_action( 'wp_ajax_nopriv_hanaboard_get_child_cats', array (
				$this,
				'get_child_cats' 
		) );
		add_action( 'wp_ajax_hanaboard_get_child_cats', array (
				$this,
				'get_child_cats' 
		) );
		
		add_action( 'wp_ajax_hanaboard_feat_img_del', array (
				$this,
				'featured_img_delete' 
		) );
		add_action( 'wp_ajax_hanaboard_featured_img', array (
				$this,
				'featured_img_upload' 
		) );
	}


	/**
	 * Returns child category dropdown on ajax request
	 */
	function get_child_cats() {
		$parentCat = $_POST ['catID'];
		$result = '';
		if ($parentCat < 1)
			die( $result );
		
		if (get_categories( 'taxonomy=category&child_of=' . $parentCat . '&hide_empty=0' )) {
			$result .= wp_dropdown_categories( 'show_option_none=' . __( '-- Select --', 'hanaboard' ) . '&class=dropdownlist&orderby=name&name=category[]&id=cat-ajax&order=ASC&hide_empty=0&hierarchical=1&taxonomy=category&depth=1&echo=0&child_of=' . $parentCat );
		} else {
			die( '' );
		}
		
		die( $result );
	}
	
	/**
	 * Delete a featured image via ajax
	 * 
	 * @since 0.8
	 */
	function featured_img_delete() {
		check_ajax_referer( 'hanaboard_nonce', 'nonce' );
		
		$attach_id = isset( $_POST ['attach_id'] ) ? intval( $_POST ['attach_id'] ) : 0;
		$attachment = get_post( $attach_id );
		
		// post author or editor role
		if (get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' )) {
			wp_delete_attachment( $attach_id, true );
			echo 'success';
		}
		
		exit();
	}
	
	/**
	 * Upload Featured image via ajax
	 * 
	 * @since 0.8
	 */
	function featured_img_upload() {
		check_ajax_referer( 'hanaboard_featured_img', 'nonce' );
		
		$upload_data = array (
				'name' => $_FILES ['hanaboard_featured_img'] ['name'],
				'type' => $_FILES ['hanaboard_featured_img'] ['type'],
				'tmp_name' => $_FILES ['hanaboard_featured_img'] ['tmp_name'],
				'error' => $_FILES ['hanaboard_featured_img'] ['error'],
				'size' => $_FILES ['hanaboard_featured_img'] ['size'] 
		);
		
		$attach_id = hanaboard_upload_file( $upload_data );
		
		if ($attach_id) {
			$html = hanaboard_feat_img_html( $attach_id );
			
			$response = array (
					'success' => true,
					'html' => $html 
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
}

$hanaboard_ajax = new HanaBoard_Ajax();