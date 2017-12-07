<?php ob_start(); ?>
<?php

function hide_download_path() {
	global $wpdb;
	$table_name = $wpdb->prefix . "download_settings";
	/* Check and create TICKER table if doesn't exist */
	if ($wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `allowed_referred` varchar(50) NULL,
            `base_dir` varchar(250) NOT NULL,
            `log_downloads` int(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );
		$baseDir = "Your Base path here";
		$rows_affected = $wpdb->insert( $table_name, array (
				'base_dir' => $baseDir 
		) );
		add_option( "hide_download_path", "1.0" ); // set version for table
			                                           // description
	}
}

/* Call plugin installation function */
register_activation_hook( __FILE__, 'hide_download_path' );
function pluginUninstall() {
	global $wpdb;
	$thetable = $wpdb->prefix . "download_settings";
	// Delete any options that's stored also?
	// delete_option('wp_yourplugin_version');
	$wpdb->query( "DROP TABLE IF EXISTS $thetable" );
}

register_deactivation_hook( __FILE__, 'pluginUninstall' );

/* Add Plugin Menu Start */
function ticker_menu() {
	add_options_page( 'Hide Download Link Settings', 'Hide Download Link', 'manage_options', 'hide-link-settings', 'download_settings_main' );
}
add_action( 'admin_menu', 'ticker_menu' );

/* Add Plugin Menu Ends */
function updateSettings() {
	global $wpdb;
	if (rtrim( $_POST ["txtBaseDir"] == "" )) {
		return "0";
	}
	
	if (isset( $_POST ['chk_log'] )) {
		$log_download = 1;
	} else {
		$log_download = 0;
	}
	$result = $wpdb->query( "UPDATE $wpdb->prefix" . "download_settings SET `allowed_referred`='" . mysql_real_escape_string( $_POST ["txtReferred"] ) . "', `base_dir`='" . $_POST ["txtBaseDir"] . "', `log_downloads`='" . $log_download . "'" );
	return $result;
}
function download_settings_main() {
	global $wpdb;
	define( 'PLUGINNAME_URL', plugin_dir_url( __FILE__ ) );
	if ($_POST) {
		$result = updateSettings();
		if ($result) {
			$message = "<div class='updated'>Settings are saved. You must create a new download page now and add shortcode.</div>";
		} else {
			$message = "<div class='error'>Something went wrong while saving data, please try again.</div>"; // "";
		}
	}
	
	echo "<div class='wrap'>";
	echo "<h2>" . __( 'Download Settings', HANA_BOARD_TEXT_DOMAIN ) . "</h2>";
	
	$qry_settings = "SELECT * FROM " . $wpdb->prefix . "download_settings";
	$existing_settings = $wpdb->get_results( $qry_settings );
	?>
<div class="message">
            <?php echo $message; ?>
        </div>
<div id="main-container" class="postbox-container metabox-holder" style="width: 75%;">
	<div style="margin-right: 16px;">
		<div class="postbox">
			<h3 style="cursor: default;">
				<span>Download Settings</span>
			</h3>
			<div class="inside">
				<p></p>
				<form method="post" id="frm_settings">
                    <?php if (!empty($existing_settings)) { ?>
                        <table style="border: solid 0px red; width: 100%;">
						<tr style="height: 35px;">
							<td colspan="4" class="table-heading">Download Settings</td>
						</tr>
						<tr>
							<td class="td-label">Allowed Referred:</td>
							<td class="td-text">
								<input name="txtReferred" type="text" id="txtReferred" class="download-text" width="300" value="<?php echo $existing_settings[0]->allowed_referred; ?>" />
							</td>
							<td class="td-label">Base Dir:</td>
							<td class="td-text">
								<input name="txtBaseDir" type="text" id="txtBaseDir" class="download-text" value="<?php echo $existing_settings[0]->base_dir; ?>" />
							</td>
						</tr>
						<tr>
							<td class="td-label">Log downloads:</td>
							<td class="td-text" colspan="3" style="text-align: left;">
								<input type="checkbox" name="chk_log" id="chk_log" <?php if($existing_settings[0]->log_downloads == "1") {echo " Checked ";} ; ?> />
							</td>
						</tr>
						<tr>
							<td colspan="4" style="text-align: right;">
								<input type="submit" name="btn_saveSettings" id="btn_saveSettings" class="button-primary" value="Click To Save Settings" />
							</td>
						</tr>
					</table>
                    <?php
	} else {
		"Something went wrong while plugin activation. Make sure you have database modification rights.";
	}
	?>
                </form>
				<p>Please follow these instructions to configure settings:</p>
				<ol>
					<li>Create a directory on your server</li>
					<li>
						'Base Path' is root path of your download directory where all files are hosted. Root path on your server is
						<code><?php echo get_home_path(); ?></code>
						followed by path to directory created in step 1<br /> For example if your WordPress installation is in public_html directory and your have created directory named 'files' in 'wp-content' then your base path is
						<code><?php echo WP_CONTENT_DIR; ?>/files/</code>
					</li>
					<li>
						If you want to restrict download from any specific domain only, enter domain name (without http://www) in 'Allowed Referred' else leave it blank.<br />If you add
						<code><?php echo $_SERVER['SERVER_NAME']; ?></code>
						in 'Allowed Referred' download will be only allowed from this website.
					</li>
					<li>Uncheck 'Log downloads' if you don't want to keep a track of files downloaded. I would recommend to kep it checked, this will help you to track download of your files.</li>
					<li>Save settings</li>
				</ol>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->
	</div>
</div>
<div id="side-container" class="postbox-container metabox-holder" style="width: 25%;">
	<div class="postbox">
		<h3 style="cursor: default;">
			<span>Do you like this Plugin?</span>
		</h3>
		<div class="inside">
			<p>Please consider a donation.</p>
			<div style="text-align: center">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_donations">
					<input type="hidden" name="business" value="deepak@xlab.co.in">
					<input type="hidden" name="lc" value="US">
					<input type="hidden" name="item_name" value="Hide File download path plugin">
					<input type="hidden" name="no_note" value="0">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
			<p>
				If you wish to help then contact
				<a href="https://twitter.com/deepaksihag">@deepaksihag</a>
				on Twitter or use that
				<a href="http://xlab.co.in/get-in-touch/">contact form</a>
				.
			</p>
		</div>
		<!-- .inside -->
	</div>
	<!-- .postbox -->
</div>
<link rel='stylesheet' href='<?php echo PLUGINNAME_URL; ?>/css/download-style.css' type='text/css' media='all' />
<br />
<br />
<div id="div_addTicker" style="display: ''; width: 1024px; border: solid 1px; margin-bottom: 30px; background-color: lightYellow; border-color: #E6DB55; margin: 5px 70px 30px; border-radius: 3px; padding: 5px;"></div>
<?php
	echo "</div>";
}

/* Function to display performance page starts */
function download_link_page() {
	global $wpdb;
	define( 'PLUGINNAME_URL', plugin_dir_url( __FILE__ ) );
	$querystr = "SELECT * FROM " . $wpdb->prefix . "download_settings";
	$settings = $wpdb->get_results( $querystr );
	$allowed_referred = "";
	$base_dir = "";
	$log_downloads = true;
	
	if (! empty( $settings )) {
		foreach ( $settings as $setting ) {
			if (rtrim( $setting->allowed_referred ) != "") {
				$allowed_referred = $setting->allowed_referred;
			}
			if (rtrim( $setting->base_dir ) != "") {
				$base_dir = $setting->base_dir;
			}
			if ($setting->log_downloads == "0") {
				$base_dir = false;
			}
		}
	}
	
	// Allow direct file download (hotlinking)?
	// Empty - allow hotlinking
	// If set to nonempty value (Example: example.com) will only allow downloads
	// when referrer contains this text
	define( 'ALLOWED_REFERRER', $allowed_referred );
	// Download folder, i.e. folder where you keep all files for download.
	// MUST end with slash (i.e. "/" )
	define( 'BASE_DIR', $base_dir );
	
	// log downloads? true/false
	define( 'LOG_DOWNLOADS', $log_downloads );
	// log file name
	define( 'LOG_FILE', 'downloads.log' );
	// Allowed extensions list in format 'extension' => 'mime type'
	// If myme type is set to empty string then script will try to detect mime
	// type
	// itself, which would only work if you have Mimetype or Fileinfo extensions
	// installed on server.
	$allowed_ext = array (
			// archives
			'zip' => 'application/zip',
			// documents
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'xlsx' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			// executables
			'exe' => 'application/octet-stream',
			// images
			'gif' => 'image/gif',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			// audio
			'mp3' => 'audio/mpeg',
			'wav' => 'audio/x-wav',
			// video
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo' 
	);
	
	// ###################################################################
	// ## DO NOT CHANGE BELOW
	// ###################################################################
	// If hotlinking not allowed then make hackers think there are some server
	// problems
	if (ALLOWED_REFERRER !== '' && (! isset( $_SERVER ['HTTP_REFERER'] ) || strpos( strtoupper( $_SERVER ['HTTP_REFERER'] ), strtoupper( ALLOWED_REFERRER ) ) === false)) {
		if (strtoupper( $_SERVER ['HTTP_REFERER'] ) != home_url()) {
			$referredBy = strtoupper( $_SERVER ['HTTP_REFERER'] );
			$parent = strtoupper( home_url() );
			$pos = strpos( $referredBy, $parent );
			if ($pos === false) {
				die( "Direct link to download file is disabled from your url." );
			} else {
			}
		}
	}
	// Make sure program execution doesn't time out
	// Set maximum script execution time in seconds (0 means no limit)
	set_time_limit( 0 );
	
	if (! isset( $_GET ['f'] ) || empty( $_GET ['f'] )) {
		die( "Sorry No File is specified to download." );
	}
	
	// Nullbyte hack fix
	if (strpos( $_GET ['f'], "\0" ) !== FALSE)
		die( '' );
		// Get real file name.
		// Remove any path info to avoid hacking by adding relative path, etc.
	$fname = basename( $_GET ['f'] );
	// get full file path (including subfolders)
	$file_path = '';
	$file_path = find_file( BASE_DIR, $fname, $file_path );
	if (! is_file( $file_path )) {
		die( "File does not exist. Make sure you specified correct file name." );
	} else {
	}
	// file size in bytes
	$fsize = filesize( $file_path );
	// file extension
	$fext = strtolower( substr( strrchr( $fname, "." ), 1 ) );
	// check if allowed extension
	if (! array_key_exists( $fext, $allowed_ext )) {
		die( "Not allowed file type." );
	}
	// get mime type
	if ($allowed_ext [$fext] == '') {
		$mtype = '';
		// mime type is not set, get from server settings
		if (function_exists( 'mime_content_type' )) {
			$mtype = mime_content_type( $file_path );
		} else if (function_exists( 'finfo_file' )) {
			$finfo = finfo_open( FILEINFO_MIME ); // return mime type
			$mtype = finfo_file( $finfo, $file_path );
			finfo_close( $finfo );
		}
		if ($mtype == '') {
			$mtype = "application/force-download";
		}
	} else {
		// get mime type defined by admin
		$mtype = $allowed_ext [$fext];
	}
	// Browser will try to save file with this filename, regardless original
	// filename.
	// You can override it if needed.
	
	if (! isset( $_GET ['f'] ) || empty( $_GET ['f'] )) {
		$asfname = $fname;
	} else {
		// remove some bad chars
		$asfname = str_replace( array (
				'"',
				"'",
				'\\',
				'/' 
		), '', $_GET ['f'] );
		if ($asfname === '')
			$asfname = 'NoName';
	}
	
	if ($fd = fopen( $file_path, "r" )) {
		$fsize = filesize( $file_path );
		$path_parts = pathinfo( $file_path );
		$ext = strtolower( $path_parts ["extension"] );
		
		header( "Content-type: $mtype" ); // add here more headers for diff.
		                                  // extensions
		header( "Content-Disposition: attachment; filename=\"" . $path_parts ["basename"] . "\"" ); // use
		                                                                                            // 'attachment'
		                                                                                            // to
		                                                                                            // force
		                                                                                            // a
		                                                                                            // download
		
		header( "Content-length: $fsize" );
		header( "Cache-control: private" ); // use this to open files directly
		
		while ( ob_get_level() > 0 ) {
			@ob_end_clean();
		}
		
		while ( ! feof( $fd ) ) {
			$buffer = fread( $fd, 2048 );
			echo $buffer;
		}
	}
	fclose( $fd );
	
	// log downloads
	if (! LOG_DOWNLOADS)
		die();
	$f = @fopen( LOG_FILE, 'a+' );
	if ($f) {
		@fputs( $f, date( "m.d.Y g:ia" ) . "  " . $_SERVER ['REMOTE_ADDR'] . "  " . $fname . "\n" );
		@fclose( $f );
	}
}

// Check if the file exists
// Check in subfolders too
function find_file($dirname, $fname, $file_path) {
	// echo $dirname .'<br>'. $fname .'<br>'. $file_path;
	$dir = opendir( $dirname );
	while ( $file = readdir( $dir ) ) {
		if (empty( $file_path ) && $file != '.' && $file != '..') {
			if (is_dir( $dirname . '/' . $file )) {
				find_file( $dirname . '/' . $file, $fname, $file_path );
			} else {
				if (file_exists( $dirname . '/' . $fname )) {
					$file_path = $dirname . '/' . $fname;
					// echo 'OK FOUND LETS RETURN '.$file_path."<br>";
					return $file_path;
				}
			}
		}
	}
} // find_file

add_shortcode( 'download_page', 'download_link_page' );
