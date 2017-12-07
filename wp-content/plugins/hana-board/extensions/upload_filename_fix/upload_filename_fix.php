<?php
final class HANA_BOARD_Upload_Filename_Fix {
	public static function register_hooks() {
		// self::_media();
	}
	private function _media() {
		if (! class_exists( 'CYM_Hooks' )) {
			// UTF-8 filename upload support
			add_filter( 'sanitize_file_name', array (
					__CLASS__,
					'sanitize_file_name' 
			) );
			add_filter( 'wp_handle_upload_prefilter', array (
					__CLASS__,
					'sanitize_file_name' 
			) );
			add_filter( 'sanitize_file_name_chars', array (
					__CLASS__,
					'sanitize_file_name_chars' 
			) );
		}
	}
	public static function sanitize_file_name($name) {
		if (is_array( $name )) {
			$file = $name;
			$name = $file ['name'];
		}
		if (! empty( $name ) && seems_utf8( $name )) {
			// Split the filename into a base and extension[s]
			$parts = explode( '.', $name );
			$ext = array_pop( $parts );
			if (empty( $parts )) {
				$fname = $ext;
				$ext = NULL;
			} else {
				$fname = implode( '.', $parts );
			}
			$fname = sanitize_title_with_dashes( $fname );
			// $fname = str_replace('%', '', $fname);
			$name = $fname . ($ext ? ".{$ext}" : '');
		}
		if (isset( $file ) && is_array( $file )) {
			$file ['name'] = $name;
			$name = $file;
		}
		return $name;
	}
	public static function sanitize_file_name_chars($chars) {
		if (! in_array( "%", $chars ))
			$chars [] = "%";
		if (! in_array( "+", $chars ))
			$chars [] = "+";
		return $chars;
	}
}

add_action( 'plugins_loaded', array (
		'HANA_BOARD_Upload_Filename_Fix',
		'register_hooks' 
) );
