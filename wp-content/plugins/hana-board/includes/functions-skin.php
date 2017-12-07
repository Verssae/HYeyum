<?php
if (! function_exists( 'hanaboard_get_current_skin_view' )) {
	function hanaboard_get_current_skin_view($term_id = null) {
		return 'default';
	}
}

if (! function_exists( 'hanaboard_get_current_skin_page' )) {
	function hanaboard_get_current_skin_page($page = null, $term_id = null) {
		$file = hanaboard_get_current_skin_dir( $term_id );
		switch ($page) {
			case 'view' :
			case 'form' :
			case 'list' :
				$file .= '/' . $page . '.php';
				break;
			default :
				$file .= '/list.php';
				break;
		}
		return $file;
	}
}
if (! function_exists( 'hanaboard_get_current_skin_path' )) {
    function hanaboard_get_current_skin_path($term_id = null, $is_dir = false) {
        if (! $term_id)
            $term_id = hanaboard_get_current_term_id();

        $current_skin = hanaboard_get_option( 'board_skin', $term_id );
        $skin_list = hanaboard_skin_list();
        if (! is_array( $skin_list ) || empty($current_skin) || ! array_key_exists( $current_skin, $skin_list ))
            $current_skin = 'Default';

        if($is_dir)
            return $skin_list[$current_skin]['path'] . '/';
        else
            return $skin_list[$current_skin]['url'] . '/';
    }
}

if (! function_exists( 'hanaboard_get_current_skin_dir' )) {
    function hanaboard_get_current_skin_dir($term_id = null) {
        return hanaboard_get_current_skin_path( $term_id, true );
    }
}

if (! function_exists( 'hanaboard_get_current_skin_image_url' )) {
    function hanaboard_get_current_skin_image_url($filename, $term_id = null) {
        $term_id = hanaboard_get_current_term_id();
        return hanaboard_get_current_skin_path( $term_id ) . 'images/' . $filename;
    }
}
if (! function_exists( 'hanaboard_skin_list' )) {
    function hanaboard_skin_list() {
        $default_skin_names = array ( 'Default', 'Gallery' );
        $skin_list = array ();
        $skin_names = array();

        // get directory list from hana-board/skins/
        $skins_dir = glob( hanaboard_plugins_dir() . 'skins/*', GLOB_ONLYDIR | GLOB_ERR );
        foreach ( $skins_dir as $dir ) {
            $current_dir = explode( '/', $dir );
            $skin_names[] = end( $current_dir );
        }

        $skin_names = array_merge($skin_names, $default_skin_names);
        foreach( $skin_names as $skin_name) {
            $skin_list[$skin_name] = array(
                'name' => $skin_name,
                'path' => hanaboard_plugins_dir() . 'skins/' . $skin_name,
                'url' => hanaboard_plugins_url() . 'skins/' . $skin_name
            );
        }
        return apply_filters('hanaboard_skin_list', $skin_list);
    }
}
if (! function_exists( 'hanaboard_get_skin_img' )) {
	function hanaboard_get_skin_img($filename, $args = array()) {
		// $title, $width, $height, $class
		extract( $args );
		$img = '<img src="' . hanaboard_get_current_skin_image_url( $filename ) . '" ';
		if (isset( $title ))
			$img .= ' alt="' . $title . '" title="' . $title . '"';
		if (isset( $width ))
			$img .= ' width="' . $width . '"';
		if (isset( $height ))
			$img .= ' height="' . $height . '"';
		if (isset( $class ))
			$img .= ' class="' . $class . '"';
			// if ( isset($autosize) ) $img .= " style='display:block; max-width:
			// $width; max-height:height; width: auto; height: auto; '";
		$img .= ' />';
		return $img;
	}
}

if (! function_exists( 'hanaboard_guest_password_form' )) {
	function hanaboard_guest_password_form() {
		$filename = hanaboard_get_current_skin_dir() . '/_form/guest_password_form.php';
		if ( file_exists($filename))
		include $filename;		
	}
}
