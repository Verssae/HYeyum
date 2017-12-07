<?php

// outputs the like it link
add_action( 'hanaboard_add_post_form_guest', 'hanaboard_secure_image_form' );
function hanaboard_secure_image_form($args = array()) {
	if(! hanaboard_get_option('captcha_for_guest'))
		return;
	// only show the secure image when user is not logged in
	
	// our wrapper DIV
	?>
<div class="row form-group hanaboard_secure_image">
	<label for="ct_captcha" class="col-xs-3 control-label nopadding-right">
		<a href="#" id="refresh_captcha" title="Click to Refresh Image" onclick="" style="text-decoration: none; font-size: 1.3em;">
			<img id="siimage" style="border: 1px solid #cccccc;" src="<?php echo plugin_dir_url(__FILE__);?>securimage_show.php?sid=<?php echo md5(uniqid()) ?>" alt="CAPTCHA Image" align="left" />
		</a>
	</label>
	<div class="col-xs-9 ">
		<input type="text" name="ct_captcha" id="hanaboard_secure_image_input" class="form-control requiredField ajax_validate captcha" placeholder="<?php _e('Enter captcha', HANA_BOARD_TEXT_DOMAIN);?>" />
		<div id="hanaboard_captcha_message"></div>
	</div>
</div>
<?php
}
add_action( 'wp_enqueue_scripts', 'hanaboard_secure_image_enqueue_script' );
function hanaboard_secure_image_enqueue_script() {
	global $hana_random;
	wp_enqueue_script( 'hanaboard_secure_image', plugin_dir_url( __FILE__ ) . 'js/secure_image.js', array (
			'jquery' 
	) );
	wp_localize_script( 'hanaboard_secure_image', 'hanaboard_secure_image', array (
			'path' => plugin_dir_url( __FILE__ ),
			'nonce' => wp_create_nonce( 'hanaboard_secure_image_check_nonce' ),
			'action' => 'hanaboard_secure_image_check',
			'refresh_captcha_link' => plugin_dir_url( __FILE__ ) . 'securimage_show.php?sid=',
			'messages' => array (
					'empty_captcha' => __( 'Empty captcha.', HANA_BOARD_TEXT_DOMAIN ),
					'wrong_captcha' => __( 'Wrong captcha.', HANA_BOARD_TEXT_DOMAIN ),
					'success_captcha' => __( 'Captcha is correct.', HANA_BOARD_TEXT_DOMAIN ) 
			) 
	) );
}
function hanaboard_secure_image_process($errors) {
	$errors = [];
	if (isset( $_POST ['ct_captcha'] ) && $_POST ['ct_captcha']) {
		// if the form has been submitted
		
		$captcha = $_POST ['ct_captcha']; // the user's entry for the captcha code
		                                  
		// $errors = array(); // initialize empty error array
		                                  
		// Only try to validate the captcha if the form has no errors
		require_once dirname( __FILE__ ) . '/securimage.php';
		$securimage = new Securimage();
		
		if ($securimage->check( $captcha ) == false) {
			// $errors->add( 'invalid_captcha', __('Incorrect security code
			// entered', HANA_BOARD_TEXT_DOMAIN) );
			$errors ['invalid_captcha'] = __( 'Incorrect security code entered', HANA_BOARD_TEXT_DOMAIN );
		}
	} // POST
	return $errors;
}

// add_filter( 'hanaboard_submit_check_errors', 'hanaboard_secure_image_process',
// 10, 1 );
function hanaboard_secure_image_check() {
	check_ajax_referer( 'hanaboard_secure_image_check_nonce', 'nonce' );
	
	$errors = array ();
	$errors = hanaboard_secure_image_process( $errors );
	
	$response = array ();
	$response ['error'] = sizeof( $errors );
	$response ['errors'] = $errors;
	die( json_encode( $response ) );
}
add_action( 'wp_ajax_hanaboard_secure_image_check', 'hanaboard_secure_image_check' );
add_action( 'wp_ajax_nopriv_hanaboard_secure_image_check', 'hanaboard_secure_image_check' );
