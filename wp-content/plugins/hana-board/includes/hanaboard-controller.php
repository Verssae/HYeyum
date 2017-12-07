<?php

/**
 * Dashboard class
 * 
 * @author HanaWordpress
 * @package Hana Board
 */
class HanaBoard_Controller {
	var $_var_mode;
	var $_var_post;
	var $_write_url;
	var $_post_type;
	var $term = null;
	var $post;
	protected static $_instance = null;
	public static function instance() {
		if (is_null( self::$_instance )) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	function __construct() {
		add_action( 'wp_loaded', array (
				&$this,
				'init' 
		) );
		add_filter( 'init', array (
				&$this,
				'role_set' 
		), 10, 4 );
		add_action( ' admin_init', array (
				&$this,
				'role_set' 
		) );
		
		add_action( 'wp_ajax_hanaboard_check_guest_password', array (
				$this,
				'ajax_check_guest_password' 
		) );
		add_action( 'wp_ajax_nopriv_hanaboard_check_guest_password', array (
				$this,
				'ajax_check_guest_password' 
		) );
		add_action( 'wp_ajax_hanaboard_ajax_submit_post', array (
				$this,
				'ajax_submit_post' 
		) );
		add_action( 'wp_ajax_nopriv_hanaboard_ajax_submit_post', array (
				$this,
				'ajax_submit_post' 
		) );
		add_action( 'wp_ajax_hanaboard_ajax_delete_post', array (
				$this,
				'ajax_delete_post' 
		) );
		add_action( 'wp_ajax_nopriv_hanaboard_ajax_delete_post', array (
				$this,
				'ajax_delete_post' 
		) );
		add_shortcode( 'hanaboard', array (
				&$this,
				'shortcode' 
		) );
		
		add_filter( 'map_meta_cap', array (
				&$this,
				'map_meta_cap' 
		), 10, 4 );
		add_filter( 'hanaboard_get_current_term', array (
				&$this,
				'get_current_term' 
		) );
	}
	function get_current_term() {
		return $this->term;
	}
	function shortcode($atts) {
		global $post;
		$default = Array (
				"board" => null,
				"term_slug" => null 
		);
		extract( shortcode_atts( $default, $atts ) );
		$boards = explode( ",", $board );
		$this->term = get_term_by( 'slug', $boards [0], HANA_BOARD_TAXONOMY );
		// change page url to current page if shortcode is enabled
		
		if (get_query_var( 'article' )) {
			$post_id = get_query_var( 'article' );
			$hanaboard_post = get_post( $post_id );
			
			if (is_object( $hanaboard_post )) {
				$post = clone $hanaboard_post;
				setup_postdata( $post );
			}
		}
        hanaboard_skin_option();
		do_action( 'hanaboard_skin_option' );
		do_action( 'hanaboard_before_template' );
		
		// 수정 - 기본 비밀글이 아닌데 비회원 글쓰기시비밀글이 되어버
		$has_no_permission = $this->check_permission_before_load();
		if ($has_no_permission) {
			if ($has_no_permission == 'login_form') {
				hanaboard_auth_redirect_login();
			} elseif ($has_no_permission == 'guest_password_form') {
				if (isset( $_POST ['hanaboard_guest_password'] )) {
					$errors = array ();
					$errors [0] ['type'] = 'danger';
					$errors [0] ['message'] = __( 'Incorrect post password', HANA_BOARD_TEXT_DOMAIN );
					hanaboard_display_errors_before_load( $errors, false );
				}
				hanaboard_guest_password_form();
			} else {
				hanaboard_wp_die( __( 'You have no permissions!', HANA_BOARD_TEXT_DOMAIN ) );
			}
		} else {
			if (get_query_var( 'article' )) {
				
				if (is_object( $post )) {
					$invisible_status = array (
							'draft',
							'trash' 
					);
					if (in_array( $post->post_status, $invisible_status )) {
						$errors = array ();
						$errors [0] ['type'] = 'danger';
						$errors [0] ['message'] = __( 'Deleted post.', HANA_BOARD_TEXT_DOMAIN );
						
						hanaboard_display_errors_before_load( $errors );
						// Used for trashed post currently. Editor or Administrator
						// can see trashed post.
						global $hanaboard_skip_template;
						if ($hanaboard_skip_template)
							return;
					}
				}
			}
            ob_start();
			if (is_hanaboard_page( 'archive' )) {
//				do_action( 'hanaboard_skin_option' );
//				do_action( 'hanaboard_before_article_loop' );
//				do_action( 'hanaboard_archive_list' );
//				do_action( 'hanaboard_after_article_loop' );
                hanaboard_archive_list_search();

                hanaboard_archive_list_header();
                //do_action( 'hanaboard_archive_list' );
                hanaboard_archive_list_loop_notice();
                hanaboard_archive_list_loop();
                hanaboard_archive_list_footer();

                hanaboard_archive_list_paging();
                hanaboard_archive_list_buttons();

			} elseif (is_hanaboard_page( 'view' )) {
//				do_action( 'hanaboard_skin_option' );
                hanaboard_single_post();
//				do_action( 'hanaboard_single_post' );

				if (hanaboard_get_option( 'show_list_on_view' )) {
					//do_action( 'hanaboard_before_article_loop' );
                    hanaboard_archive_list_search();

                    hanaboard_archive_list_header();
                    //do_action( 'hanaboard_archive_list' );
                    hanaboard_archive_list_loop_notice();
                    hanaboard_archive_list_loop();
                    hanaboard_archive_list_footer();

                    hanaboard_archive_list_paging();
                    hanaboard_archive_list_buttons();
                    //do_action( 'hanaboard_after_article_loop' );
				}
			} elseif (is_hanaboard_page( 'write' ) || is_hanaboard_page( 'edit' ) || is_hanaboard_page( 'write_reply' )) {
//				do_action( 'hanaboard_skin_option' );
                hanaboard_single_post_form();
				//do_action( 'hanaboard_single_post_form' );
			}
		}
		wp_reset_postdata();
		do_action( 'hanaboard_after_template' );
        return ob_get_clean();
	}
	function init() {
		// write an post
		if (isset( $_POST ) && isset( $_POST ['action'] ) && $_POST ['action'] == 'hanaboard_submit_post') {
			$this->submit_post();
		}
		if (isset( $_POST ) && isset( $_POST ['action'] ) && $_POST ['action'] == 'hanaboard_delete_post') {
			$this->delete_post_on_submit();
		}
	}
	function role_set() {
		global $wp_roles;
		
		$all_roles = $wp_roles->roles;
		$editable_roles = apply_filters( 'editable_roles', $all_roles );
		foreach ( $editable_roles as $k => $v ) {
			$role = get_role( $k );
			if ($v == 'administrator' || $v == 'editor') {
				$role->add_cap( 'manage_' . HANA_BOARD_POST_TYPE );
				$role->add_cap( 'read_private_' . HANA_BOARD_POST_TYPE );
				$role->add_cap( 'edit_others_' . HANA_BOARD_POST_TYPE );
				$role->add_cap( 'delete_others_' . HANA_BOARD_POST_TYPE );
			}
			$role->add_cap( 'publish_' . HANA_BOARD_POST_TYPE );
			$role->add_cap( 'write_reply_' . HANA_BOARD_POST_TYPE );
			$role->add_cap( 'edit_' . HANA_BOARD_POST_TYPE );
			$role->add_cap( 'delete_' . HANA_BOARD_POST_TYPE );
			$role->add_cap( 'read_' . HANA_BOARD_POST_TYPE );
			$role->add_cap( 'list_' . HANA_BOARD_POST_TYPE );
			$role->add_cap( 'moderate_comments_' . HANA_BOARD_POST_TYPE );
		}
	}
	function ajax_check_guest_password() {
		$res = array ();
		if ($this->check_guest_password())
			$res ['error'] = 0;
		else
			$res ['error'] = 1;
		echo json_encode( $res );
		exit();
	}
	function check_guest_password() {
		$res = array ();
		$post_id = $_POST ['post_id'];
		$guest_password = $_POST ['hanaboard_guest_password'];
		if (current_user_can( 'edit_others_post' )) {
			return true;
		} elseif (hanaboard_get_post_meta( $post_id, 'guest_password' ) == $guest_password) {
			return true;
		} else {
			return false;
		}
	}
	function check_permission_before_load() {
		$term = hanaboard_get_current_term();
		$term_id = hanaboard_get_current_term_id();
		$post_id = get_query_var( 'article' );
		if ($post_id)
			$post = get_post( $post_id );
		do_action( 'hanaboard_custom_permission' );
		if (current_user_can( 'editor' ) || current_user_can( 'contributor' ) || current_user_can( 'administrator' ))
			return null;
		switch (hanaboard_page_now()) {
			case 'edit' :
				if ($post->post_author > 0 && ! is_user_logged_in())
					return 'login_form';
				$guest_password = hanaboard_get_post_meta( $post_id, 'guest_password' );
				if (hanaboard_current_user_can( 'edit', $post_id )) {
					return null;
				} elseif ($post->post_author == 0 && $guest_password != "") {
					if ($this->check_guest_password())
						return null;
					else
						return 'guest_password_form';
				}
				break;
			case 'view' :
				if (hanaboard_is_post_notice( $post_id ))
					return null;
				if ($post->post_author == 0) {
					if ( ! hanaboard_is_post_private($post_id) )
						return null;

                    if (isset($_SESSION['hanaboard_guest_authorized_posts'][$post_id]) && $_SESSION['hanaboard_guest_authorized_posts'][$post_id] )
                        return null;
					if (isset( $_POST ['hanaboard_guest_password'] ) && $this->check_guest_password()) {
					    $_SESSION['hanaboard_guest_authorized_posts'][$post_id] = true;
                        return null;
                    }
					else
						return 'guest_password_form';
				} elseif (! hanaboard_current_user_can( 'read', $post_id )) {
					if (! is_user_logged_in()) {
					    // If reply
					    if ( $post->post_parent > 0 ) {
					        $parent_post = get_post($post->post_parent);
                            // If parent post has author
                            if($parent_post->post_author > 0 ) {
                                // if current user is post author
                                if ( $parent_post->post_author == get_current_user_id() )
                                    return null;
                            }
                            else { // if parent post's author is guest
                                if ( isset($_SESSION['hanaboard_guest_authorized_posts'][$parent_post->ID])
                                    && $_SESSION['hanaboard_guest_authorized_posts'][$parent_post->ID] ) {
                                    return null;
                                }
                                exit;
                            }

                        }

                        return 'login_form';
                    }
					else
						return 'no_permission';
				} else
					return null;
				
				break;
			case 'list' :
				if (! hanaboard_current_user_can( 'list', $term_id )) {
					if (! is_user_logged_in())
						return 'login_form';
					else
						return 'no_permission';
				}
				break;
			case 'write' :
				if (hanaboard_get_option( 'publish_hanaboard-post' ) == 'everyone')
					return null;
				elseif (! is_user_logged_in())
					return 'login_form';
				elseif (! hanaboard_current_user_can( 'publish', $term_id ))
					return 'no_permission';
					// hanaboard_wp_die( __( 'You have no permissions!', 'hanaboard' ) );
				else
					return null;
				break;
			case 'write_reply' :
				if (hanaboard_current_user_can( 'write_reply', $term_id ))
					return null;
				break;
			case 'delete' :
				if (hanaboard_current_user_can( 'delete', $post_id ))
					return null;
				else {
					if (! is_user_logged_in())
						return 'login_form';
					else
						return 'no_permission';
				}
				break;
			default :
				return 'no_permission';
				break;
		}
		/*
		 * if (is_hanaboard_page( 'view' )) {
		 * if (hanaboard_is_post_notice( $post_id ))
		 * return '';
		 * if (hanaboard_is_post_private( $post_id )) {
		 * if ((get_current_user_id() == $post->post_author) && $post->post_author > 0)
		 * return '';
		 * else if ($post->post_author == 0) {
		 * if (isset( $_POST ['guest_password'] ))
		 * return $this->check_guest_password();
		 * else {
		 * hanaboard_show_guest_password_form();
		 * return false;
		 * }
		 * }
		 * }
		 * if (hanaboard_current_user_can( 'read', $post_id ))
		 * return;
		 * return;
		 * }
		 */
	}
	function map_meta_cap($caps, $cap, $user_id, $args) {
		/*
		 * If editing, deleting, or reading a board post, get the post and post type object.
		 */

		if ('edit_' . HANA_BOARD_POST_TYPE == $cap || 'delete_' . HANA_BOARD_POST_TYPE == $cap || 'read_' . HANA_BOARD_POST_TYPE == $cap || 'read_post' == $cap) {
			if (is_array( $args ) && isset( $args [0] )) {
				$post = get_post( $args [0] );
				$post_type = get_post_type_object( $post->post_type );
				$caps = array ();
			}
			if (! isset( $post_type ) || ! is_object( $post_type )) {
				$post_type = get_post_type_object( HANA_BOARD_POST_TYPE );
			}
		} else if ('publish_' . HANA_BOARD_POST_TYPE == $cap || 'list_' . HANA_BOARD_POST_TYPE == $cap || 'write_reply' . HANA_BOARD_POST_TYPE == $cap) {
			$post_type = get_post_type_object( HANA_BOARD_POST_TYPE );
			$caps = array ();
		}
		if ( isset( $post ) && is_object( $post ) && $post->post_status == 'private' && $post->post_type == HANABOARD_POST_TYPE && $cap =='read_post' ) {
			$caps [] = 'read';
			return $caps;
		}
		if ('edit_' . HANA_BOARD_POST_TYPE == $cap) { /*
		                                               * If editing a board post, assign the required capability.
		                                               */
			if (isset( $post ) && is_object( $post )) {
				if ($user_id == $post->post_author && $post->post_author) {
					$caps [] = $post_type->cap->edit_posts;
				} elseif ($post->post_author != "0") {
					$caps [] = $post_type->cap->edit_others_posts;
				}
			} else {
				$caps [] = $post_type->cap->edit_others_posts;
			}
		} else if ('delete_' . HANA_BOARD_POST_TYPE == $cap) {
			/*
			 * If deleting a board post, assign the required capability.
			 */
			if (isset( $post ) && is_object( $post )) {
				if ($user_id == $post->post_author && $post->post_author)
					$caps [] = $post_type->cap->delete_posts;
			} else
				$caps [] = $post_type->cap->delete_others_posts;
		} elseif ('read_' . HANA_BOARD_POST_TYPE == $cap) {
			/*
			 * If reading a private board post, assign the required capability.
			 */
			if (isset( $post ) && is_object( $post ) && 'private' != $post->post_status)
				$caps [] = $post_type->cap->read;
			else if ($user_id == $post->post_author)
				$caps [] = $post_type->cap->read;
			else
				$caps [] = $post_type->cap->read_private_posts;
		} else if ('publish_' . HANA_BOARD_POST_TYPE == $cap || 'write_reply_' . HANA_BOARD_POST_TYPE == $cap) { /*
		                                                                                                          * If editing a board post, assign the required capability.
		                                                                                                          */
			$board_permission = hanaboard_get_option( $cap, $args [0] );
			switch ($board_permission) {
				case 'guest' :
				case 'member' :
					$caps [] = $cap;
					break;
				case 'board_admin' :
					$board_admin = explode(',',hanaboard_get_option( $cap, hanaboard_get_current_term_id() ));
					if (in_array( $user_id, $board_admin ) || is_admin())
						$caps [] = $cap;
					break;
				case 'site_admin' :
					if (is_admin())
						$caps [] = $cap;
					break;
			}
		} else if ('list_' . HANA_BOARD_POST_TYPE == $cap) {
			$board_permission = hanaboard_get_option( $cap, $args [0] );
			switch ($board_permission) {
				case 'guest' :
					$caps [] = $cap;
					break;
				case 'member' :
					if (is_user_logged_in()) {
						$caps [] = $cap;
					}
					break;
				case 'board_admin' :
                    $board_admin = explode(',',hanaboard_get_option( $cap, hanaboard_get_current_term_id() ));
					if (in_array( $board_admin, $user_id ))
						$caps [] = $cap;
					break;
				case 'site_admin' :
					if (is_admin())
						$caps [] = $cap;
					break;
			}
		}
		/* Return the capabilities required by the user. */
		return $caps;
	}
	
	/**
	 * Validate the post submit data
	 * 
	 * @global type $userdata
	 */
	function get_form_values($args) {
		if (isset( $_POST ))
			array_merge( $args, $_POST );
		return $args;
	}
	function display_errors($errors) {
		$error_html = '';
		if (! isset( $errors ['error'] ) || $errors ['error'] === 0)
			return false;
		
		$error_html = hanaboard_error_msg( $errors ['errors'] );
	}
	function ajax_delete_post() {
		$result = $this->delete_post_process();
		echo json_encode( $result );
		exit();
	}
	function delete_post_on_submit() {
		$result = $this->delete_post_process();
		if ($result ['error'] > 0) {
		} else if($result['error'] == 0) {
		    wp_redirect($result['redirect_to']);
            exit;
        }
	}
	function delete_post_process() {
		$is_force = true; // hanaboard_get_option( 'delete_to_trash' );
		$user_id = get_current_user_id();
		$input_password = $_POST ['hanaboard_guest_password'];
		
		$post_id = $_POST ['post_id'];
		$post = get_post( $post_id );
		if (! $post_id || ! is_object( $post )) {
			$result ['error'] = - 1;
			$result ['errors'] = array (
					'wrong_access' => __( 'Wrong access', HANA_BOARD_TEXT_DOMAIN ) 
			);
			echo json_encode( $result );
			die();
		}
		
		$author_id = $post->post_author;
		$success = false;
		
		if ($author_id) { // author > 0
			if (get_current_user_id() == $author_id || current_user_can( 'delete_others_posts' )) {
				$success = true;
			} else {
				$result ['error'] = 1;
				$result ['errors'] = array (
						'no_permission' => __( 'You have no permission', HANA_BOARD_TEXT_DOMAIN ) 
				);
			}
		} else { // author is guest
			if ($input_password == '') {
				$result ['error'] = 3;
				$result ['errors'] = array (
						'empty_password' => __( 'Empty password', HANA_BOARD_TEXT_DOMAIN ) 
				);
				echo json_encode( $result );
				die();
			}
			
			$post_password = hanaboard_get_post_meta( $post_id, 'guest_password' );
			
			if ($input_password == $post_password) {
				$success = true;
			} else {
				$result ['error'] = 2;
				$result ['errors'] = array (
						'incorrect_password' => __( 'Incorrect post password', HANA_BOARD_TEXT_DOMAIN ) 
				);
			}
		}
		if ($success) {
			wp_trash_post( $post_id, $is_force );
			$result ['error'] = 0;
			$result ['errors'] = array ();
			$result ['redirect_to'] = $_POST ['archive_list_url'];
		}
		return $result;
	}
	function submit_post() {
		if (! wp_verify_nonce( $_POST ['hanaboard-add-post-nonce'], 'hanaboard-post-form' )) {
			wp_die( __( 'Cheating?' ) );
		}
		
		$result = $this->submit_post_process();
		if (is_array( $result ) && $result ['error'] == 0) {
			wp_redirect( $result ['post_link'] );
			exit();
		} else {
			add_filter( 'hanaboard_form_values', array (
					&$this,
					'get_form_values' 
			) );
			// get_page_template();
			add_action('hanaboard_before_template', 'hanaboard_display_errors', $result);
		}
	}
	function ajax_submit_post() {
		// check_ajax_referer TBD
		$result = $this->submit_post_process();
		echo json_encode( $result );
		wp_die();
	}
	function ajax_submit_post_validate() {
		// $errors = $this->submit_post_validate();
		// echo json_encode( $errors );
		// wp_die();
		exit();
	}
	function submit_post_validate() {
		$errors_array = array ();
		$errors_array = apply_filters( 'hanaboard_submit_check_errors', $errors_array );
		$errors = new WP_Error();
		
		$response = array (
				'error' => 0 
		);
		if (is_array( $errors_array )) {
			foreach ( $errors_array as $k => $v ) {
				$errors->add( $k, $v );
			}
		}
		
		if (! wp_verify_nonce( $_POST ['hanaboard-add-post-nonce'], 'hanaboard-post-form' )) {
			$response ['error'] = 100;
			return $response;
		}
		
		// validate title
		$title = trim( strip_tags( $_POST ['post_title'] ) );
		if (empty( $title )) {
			$errors->add( 'empty_title', __( 'Empty post title', HANA_BOARD_PLUGIN_NAME ) );
		}
		
		$post = get_post( $_POST ['hanaboard_post_ID'] );
		// validate guest post password
		if ($_POST ['hanaboard_post_form_mode'] == "edit" && isset( $_POST ['hanaboard_guest_password'] )) {
			hanaboard_get_post_meta( $post->ID, 'guest_password' );
			if ($post->post_password != '')
				$post_password = $post->post_password;
			else
				$post_password = hanaboard_get_post_meta( $_POST ['hanaboard_post_ID'], 'guest_password' );
			if ($post_password != $_POST ['hanaboard_guest_password']) {
				$errors->add( 'password_incorrect', __( 'Post password incorrect.', HANA_BOARD_TEXT_DOMAIN ) );
			}
		}
		
		// validate cat
		if (hanaboard_get_option( 'allow_cats' )) {
			$cat_type = hanaboard_get_option( 'cat_type' );
			if (! isset( $_POST ['cat'] )) {
				$errors->add( 'empty_category', __( 'Please choose a category', HANA_BOARD_TEXT_DOMAIN ) );
			} else if ($cat_type == 'normal' && $_POST ['category'] [0] == '-1') {
				$errors->add( 'empty_category', __( 'Please choose a category', HANA_BOARD_TEXT_DOMAIN ) );
			} else {
				if (count( $_POST ['cat'] ) < 1) {
					$errors->add( 'empty_category', __( 'Please choose a category', HANA_BOARD_TEXT_DOMAIN ) );
				}
			}
		}
		
		// validate post content
		$post_content = apply_filters( 'hanaboard_write_content', $_POST ['post_content'] );
		if (empty( $post_content )) {
			$errors->add( 'empty_content', __( 'Empty post content', HANA_BOARD_TEXT_DOMAIN ) );
		}
		
		// $errors = apply_filters( 'hanaboard_add_post_validation', $errors );
		
		if (hanaboard_get_option( 'attachment_num' ) > 0 && hanaboard_get_option( 'attachment_num' ) < sizeof( $_POST ['hanaboard_attach_url'] )) {
			$errors->add( 'upload_attachment_num', sprintf( __( 'Too many files. You can upload files up to %s.', HANA_BOARD_TEXT_DOMAIN ), hanaboard_get_option( 'attachment_num' ) ) );
		}
		
		// if not any errors, proceed
		if (is_wp_error( $errors )) {
			foreach ( $errors->errors as $k => $error ) {
				if (is_array( $error )) {
					foreach ( $error as $error_msg ) {
						$response ['errors'] [$k] = $error_msg;
					}
				} else
					$response ['errors'] [$k] = $error;
			}
			if (isset( $response ['errors'] ) && sizeof( $response ['errors'] ))
				$response ['error'] = 1;
		}
		return $response;
	}
	function submit_post_process() {
		global $userdata;
		
		$errors = $this->submit_post_validate();
		
		// If invalid
		if (sizeof( $errors ) > 0 && $errors ['error'])
			return $errors;
		
		$post_stat = "publish";
		$post_password = null;
		if (isset( $_POST ['hanaboard_is_secret'] ) && $_POST ['hanaboard_is_secret'] == 'on') {
			if (is_user_logged_in()) {
				$post_stat = "private";
			} else {
				// set post password if user is not logged on
				$post_stat = "private";
				$post_password = $_POST ['hanaboard_guest_password'];
			}
		}
		
		// user ID
		if (is_user_logged_in()) {
			$user_id = $userdata->ID;
		} else {
			$user_id = 0;
		}
		
		$title = trim( $_POST ['post_title'] );
		$content = trim( $_POST ['post_content'] );
		$post_author = $user_id;

		$post_category = ( int ) $_POST ['cat'];
		$term = get_term( $post_category, HANA_BOARD_TAXONOMY );
		
		if (is_object( $term )) {
			HanaBoardController()->term = $term;
			HanaBoardController()->term_id = $term->term_id;
		}
		
		$my_post = array (
				'post_title' => $title,
				'post_content' => $content,
				'comment_status' => 'open',
				'ping_status' => 'open',
				'post_author' => $post_author,
				'post_status' => $post_stat,
				'post_type' => HANA_BOARD_POST_TYPE,
				'tax_input' => array (
						HANA_BOARD_TAXONOMY => $post_category 
				),
				'post_parent' => $_POST ['hanaboard_post_parent'] ? $_POST ['hanaboard_post_parent'] : 0,
				'post_password' => $post_password 
		);
		// If writing a reply, set post parent
		if ($_POST ['hanaboard_post_form_mode'] == 'write_reply') {
			$my_post ['post_parent'] = $_POST ['hanaboard_post_parent'];
			$my_post ['tax_input'] = null;
		}
		
		// plugin API to extend the functionality
		$my_post = apply_filters( 'hanaboard_add_post_args', $my_post );
		
		// insert the post
		if ($_POST ['hanaboard_post_form_mode'] == "edit" && $_POST ['hanaboard_post_ID']) {
			$my_post ['ID'] = $_POST ['hanaboard_post_ID'];
            if(! hanaboard_get_option('update_author', $post_category))
			    unset( $my_post ['post_author'] );
			$post_id = wp_update_post( $my_post );
		} else {
			$post_id = wp_insert_post( $my_post );
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'posts', array (
					'ID' => $post_id,
					'guid' => hanaboard_get_the_permalink( $post_id ) 
			), array (
					'ID' => $post_id 
			) );
		}
		if ($post_id) {
			$ret = wp_set_object_terms( $post_id, $post_category, HANA_BOARD_TAXONOMY );

			// update tags
			if (isset( $_POST ['tags'] )) {
				wp_set_post_tags( $post_id, $_POST ['tags'], $ret );
			}

			// If the post is not a child post ( not reply )
			// update post no
			if ($_POST ['hanaboard_post_ID'] == "0") {
				
				$last_post_num = hanaboard_get_last_post_no( $post_category );
				$last_post_num ++;
				
				hanaboard_update_post_meta( $post_id, 'post_no', $last_post_num );
			}
			
			// update if post is notice
			if (isset( $_POST ['hanaboard_is_notice'] ) && $_POST ['hanaboard_is_notice'] == 'on') {
				hanaboard_update_post_meta( $post_id, 'is_notice', $_POST ['hanaboard_is_notice'] );
			}

            if (isset($_POST['sub_category'])) {
                hanaboard_update_post_meta( $post_id, 'sub_category', $_POST ['sub_category'] );
            }

			// }
			
			// update writer ip address
			hanaboard_update_post_meta( $post_id, 'writer_ip', $_SERVER ['REMOTE_ADDR'] );
			
			// update author name if writer is not logged in
			if (isset( $_POST ['hanaboard_guest_author'] )) {
				hanaboard_update_post_meta( $post_id, 'guest_author', $_POST ['hanaboard_guest_author'] );
			}
			
			// update post password to wp_post_meta if guest
			if (isset( $_POST ['hanaboard_guest_password'] )) {
				hanaboard_update_post_meta( $post_id, 'guest_password', $_POST ['hanaboard_guest_password'] );
			}
			if (isset( $_POST ['hanaboard_guest_email'] )) {
				hanaboard_update_post_meta( $post_id, 'guest_email', $_POST ['hanaboard_guest_email'] );
			}
			// if skin developer assigned additional fields
			if (isset( $_POST ['skin_custom_field'] ) && is_array( $_POST ['skin_custom_field'] )) {
				foreach ( $_POST ['skin_custom_field'] as $k => $v ) {
					hanaboard_update_post_meta( $post_id, 'custom_' . $k, $v );
				}
			}
			/*
			 * // add the custom fields if ($custom_fields) { foreach ($custom_fields as $key => $val) { add_post_meta($post_id, $key, $val, true); } }
			 */
			
			// set post thumbnail if has featured img
			require_once (ABSPATH . 'wp-admin/includes/image.php');
			require_once (ABSPATH . 'wp-admin/includes/file.php');
			
			// set post thumbnail if image attached to content
			/*
			 * else if ( preg_match_all( '/<img[^>]+src\s*=\s*["\']?([^"\' ]+)[^>]*>/', stripslashes($content), $extracted_image ) ) { foreach ( $extracted_image[1] as $filename ) { $wp_upload_dir = wp_upload_dir(); $wp_filetype = wp_check_filetype(basename($filename), null ); $attachment = array( 'guid' => $wp_upload_dir['path'] . '/' . basename( $filename ), 'post_mime_type' => $wp_filetype['type'], 'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)), 'post_content' => '', 'post_status' => 'inherit' ); $attach_id = wp_insert_attachment( $attachment, $filename, $post_id ); $attach_data = wp_generate_attachment_metadata( $attach_id, $attachment['guid'] ); wp_update_attachment_metadata( $attach_id, $attach_data ); set_post_thumbnail( $post_id, $attach_id ); } }
			 */
			$featured_img_id = isset( $_POST ['hanaboard_featured_img'] ) ? intval( $_POST ['hanaboard_featured_img'] ) : 0;
			// update associatement
			if ($featured_img_id) {
				wp_update_post( array (
						'ID' => $featured_img_id,
						'post_parent' => $post_id 
				) );
			}
			// Set file attachment to post
			if (isset( $_POST ['hanaboard_attach_id'] ) && is_array( $_POST ['hanaboard_attach_id'] )) {
				$i = 0;
				
				for($i = 0; $i < sizeof( $_POST ['hanaboard_attach_id'] ); $i ++) {
					
					$attach_id = $_POST ['hanaboard_attach_id'] [$i];
					$attachment_info = hanaboard_get_attachment_info( $attach_id );
					// if ( hanaboard_get_option (
					// 'dont_show_media_on_attachment_list', true ) ) {
					if (hanaboard_is_file_image( $attachment_info->url, $attachment_info->mime )) {
						if (! $featured_img_id)
							$featured_img_id = $attach_id;
						// continue;
					}
					// }
					/*
					 * $filename = $attachment_info->filename; $wp_upload_dir = wp_upload_dir(); $wp_filetype = wp_check_filetype(basename($filename), null ); $attachment = array( 'ID' => $_POST['hanaboard_attach_id'][$i], 'guid' => $wp_upload_dir['path'] . '/' . basename( $filename ), 'post_mime_type' => $wp_filetype['type'], // 'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)), 'post_title' => basename($filename), 'post_content' => '', 'post_status' => 'inherit', 'post_parent' => $post_id, ); $attach_id = wp_insert_attachment( $attachment, $filename, $post_id ); $attach_data = wp_generate_attachment_metadata( $attach_id, $attachment['guid'] ); wp_update_attachment_metadata( $attach_id, $attach_data );
					 */
					
					$attach_data = array (
							'ID' => $_POST ['hanaboard_attach_file_url'] [$i],
							'post_parent' => $post_id 
					);
					wp_update_post( $attach_data );
				}
				$i ++;
			}
			
			if ($featured_img_id) {
				set_post_thumbnail( $post_id, $featured_img_id );
			}
			
			// Set Post expiration date if has any
			if (! empty( $_POST ['expiration-date'] ) && $post_expiry == 'on') {
				$post = get_post( $post_id );
				$post_date = strtotime( $post->post_date );
				$expiration = ( int ) $_POST ['expiration-date'];
				$expiration = $post_date + ($expiration * 60 * 60 * 24);
				
				add_post_meta( $post_id, 'expiration-date', $expiration, true );
			}
			
			if (isset( $_POST ['hana_post_meta'] ) && is_array( $_POST ['hana_post_meta'] )) {
				$post_meta = $_POST ['hana_post_meta'];
				foreach ( $post_meta as $k => $v ) {
					hanaboard_update_post_meta( $post_id, $k, $v );
				}
			}
			$post = get_post( $post_id );
			
			setup_postdata( $post );
			// plugin API to extend the func tionality
			do_action( 'hanaboard_add_post_after_insert', $post_id );
			if ($post_id) {
				$redirect = hanaboard_get_the_term_link( $post_category );
				$response ['error'] = 0;
				$response ['post_link'] = $redirect;
				$response ['message'] = __( 'Post published successfully', HANA_BOARD_TEXT_DOMAIN );
				return $response;
			}
		}
	}
}
function HanaBoardController() {
	return HanaBoard_Controller::instance();
}

$GLOBALS ['hanaboard_controller'] = HanaBoardController();
function hanaboard_get_current_term() {
	$term = HanaBoardController()->get_current_term();
	return $term;
}
function hanaboard_get_current_term_id() {
	$term = hanaboard_get_current_term();
	if (is_object( $term ))
		return $term->term_id;
	else
		return null;
}
function hanaboard_get_current_term_slug() {
	$term = hanaboard_get_current_term();
	if (is_object( $term ))
		return $term->slug;
	else
		return null;
}