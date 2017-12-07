<?php

// Process the form, if it was submitted
process_si_contact_form();
?>
<div id="success_message" style="display: none">
	Your message has been sent!<br />We will contact you as soon as possible.
</div>
<form method="post" action="" id="contact_form" onsubmit="return processForm()">
	<input type="hidden" name="do" value="contact" />
	<img id="siimage" style="border: 1px solid #000; margin-right: 15px" src="./securimage_show.php?sid=<?php echo md5(uniqid()) ?>" alt="CAPTCHA Image" align="left" />
	<a tabindex="-1" style="border-style: none;" href="#" title="Refresh Image" onclick="document.getElementById('siimage').src = './securimage_show.php?sid=' + Math.random(); this.blur(); return false">
		<img src="./images/refresh.png" alt="Reload Image" height="32" width="32" onclick="this.blur()" align="bottom" border="0" />
	</a>
	<br /> <strong>Enter Code*:</strong><br />
	<input type="text" name="ct_captcha" size="12" maxlength="8" />
	<input type="submit" value="Submit Message" />
</form>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script type="text/javascript">
    $.noConflict();

    function reloadCaptcha()
    {
        jQuery('#siimage').prop('src', './securimage_show.php?sid=' + Math.random());
    }

    function processForm()
    {
		jQuery.ajax({
			url: '<?php echo $_SERVER['PHP_SELF'] ?>',
			type: 'POST',
			data: jQuery('#contact_form').serialize(),
			dataType: 'json',
		}).done(function(data) {
			if (data.error === 0) {
				jQuery('#success_message').show();
				jQuery('#contact_form')[0].reset();
				reloadCaptcha();
				setTimeout("jQuery('#success_message').fadeOut()", 12000);
			} else {
				alert("There was an error with your submission.\n\n" + data.message);
			}
		});

        return false;
    }
</script>
<?php

// The form processor PHP code
function process_si_contact_form() {
	if ($_SERVER ['REQUEST_METHOD'] == 'POST' && @$_POST ['ct_captcha']) {
		// if the form has been submitted
		
		$captcha = @$_POST ['ct_captcha']; // the user's entry for the captcha
		                                   // code
		
		$errors = array (); // initialize empty error array
		                    
		// Only try to validate the captcha if the form has no errors
		                    // This is especially important for ajax calls
		if (sizeof( $errors ) == 0) {
			require_once dirname( __FILE__ ) . '/securimage.php';
			$securimage = new Securimage();
			
			if ($securimage->check( $captcha ) == false) {
				$errors ['captcha_error'] = 'Incorrect security code entered';
			}
		}
		
		if (sizeof( $errors ) == 0) {
			// no errors, send the form
			$time = date( 'r' );
			$message = "A message was submitted from the contact form.  The following information was provided.<br /><br />" . "<pre>$message</pre>" . "Browser: {$_SERVER['HTTP_USER_AGENT']}<br />";
			
			$return = array (
					'error' => 0,
					'message' => 'OK' 
			);
			die( json_encode( $return ) );
		} else {
			$errmsg = '';
			foreach ( $errors as $key => $error ) {
				// set up error messages to display with each field
				$errmsg .= " - {$error}\n";
			}
			
			$return = array (
					'error' => 1,
					'message' => $errmsg 
			);
			die( json_encode( $return ) );
		}
	} // POST
} // function process_si_contact_form()
