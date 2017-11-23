<?php

global $LIONSCRIPTS, $objLionTemp;

if(!defined('LIONSCRIPTS_SITE_NAME_SHORT'))
	define('LIONSCRIPTS_SITE_NAME_SHORT', 'LionScripts');

if(!defined('LIONSCRIPTS_SITE_NAME'))
	define('LIONSCRIPTS_SITE_NAME', LIONSCRIPTS_SITE_NAME_SHORT.'.com');

if(!defined('LIONSCRIPTS_HOME_PAGE_URL'))
	define('LIONSCRIPTS_HOME_PAGE_URL', 'http://www.'.strtolower(LIONSCRIPTS_SITE_NAME).'/');

if(!defined('LIONSCRIPTS_SUPPORT_PAGE_URL'))
	define('LIONSCRIPTS_SUPPORT_PAGE_URL', 'http://support.'.strtolower(LIONSCRIPTS_SITE_NAME).'/');

if(!defined('LIONSCRIPTS_FACEBOOK_LINK'))
	define('LIONSCRIPTS_FACEBOOK_LINK', "http://www.facebook.com/".LIONSCRIPTS_SITE_NAME_SHORT);

if(!defined('LIONSCRIPTS_TWITTER_LINK'))
	define('LIONSCRIPTS_TWITTER_LINK', 'http://twitter.com/'.LIONSCRIPTS_SITE_NAME_SHORT);

if(!defined('LIONSCRIPTS_GOOGLE_PLUS_LINK'))
	define('LIONSCRIPTS_GOOGLE_PLUS_LINK', 'http://plus.google.com/+'.LIONSCRIPTS_SITE_NAME_SHORT);

if(!defined('LIONSCRIPTS_YOUTUBE_LINK'))
	define('LIONSCRIPTS_YOUTUBE_LINK', 'http://www.youtube.com/user/'.LIONSCRIPTS_SITE_NAME_SHORT);

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'lionscripts_plg.class.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'lionscripts_plg_wt.class.php');

$objLionTemp = new lionscripts_plg_wt(basename(dirname(__FILE__)));
$LIONSCRIPTS[$objLionTemp->plg_identifier]['OBJ'] = $objLionTemp;

$LIONSCRIPTS['WP_PRODUCTS'][$objLionTemp->plg_hook_version] = array(
																		'WIB'=>array(
																					'name'=>'IP Address Blocker', 
																					'wp_url_var'=>'ip-address-blocker',
																					'url'=>LIONSCRIPTS_HOME_PAGE_URL.'product/wordpress-ip-address-blocker-pro/'
																				),
																		'MNN'=>array(
																					'name'=>'Site Maintenance and Noindex-Nofollow', 
																					'wp_url_var'=>'maintenance-and-noindex-nofollow',
																					'url'=>LIONSCRIPTS_HOME_PAGE_URL.'product/maintenance-and-noindex-nofollow/'
																				),
																		'WT'=>array(
																					'name'=>'Webmaster Tools', 
																					'wp_url_var'=>'webmaster-tools',
																					'url'=>LIONSCRIPTS_HOME_PAGE_URL.'product/webmaster-tools/'
																				)
																	);

$LIONSCRIPTS['ABOUT_US'][$objLionTemp->plg_hook_version] = '<p>
																<a href="'.LIONSCRIPTS_HOME_PAGE_URL.'" target="_blank"><img src="'.$objLionTemp->plg_images['www'].'logo.png" class="left ls_logo_about" /></a>
																'.LIONSCRIPTS_SITE_NAME.' is an organization with mission to extend WordPress Possibilities so that every person can get maximum benefit by using Wordpress as their site\'s CMS platform, no matter whether he/she is Novice or Professional.
															</p>
															<p>
																You can spread our mission by sharing and following us on the social sites.
															</p>
															<p>
																<ul class="socialicons color">
																	<li><a href="'.LIONSCRIPTS_FACEBOOK_LINK.'" target="_blank" class="facebook"></a></li>
																	<li><a href="'.LIONSCRIPTS_TWITTER_LINK.'" target="_blank" class="twitter"></a></li>
																	<li><a href="'.LIONSCRIPTS_GOOGLE_PLUS_LINK.'" target="_blank" class="gplusdark"></a></li>
																	<li><a href="'.LIONSCRIPTS_YOUTUBE_LINK.'" target="_blank" class="youtube"></a></li>
																	<li><a href="'.LIONSCRIPTS_HOME_PAGE_URL.'shop/feed" target="_blank" class="rss"></a></li>
																</ul>
																<div class="cl"></div>
															</p>';

if(!is_admin())
{
	global $LIONSCRIPTS;
	
	$objLionTemp->get_configuration();
	
}

if(!function_exists('lionscripts_wt_uninstall'))
{
	function lionscripts_wt_uninstall()
	{
		global $wpdb, $objLionTemp;
		$objLionTemp->deactivate();
	} 
}

unset($objLionTemp);

?>