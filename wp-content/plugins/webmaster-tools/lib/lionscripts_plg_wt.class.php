<?php

if(!class_exists('lionscripts_plg_wt'))
{
	class lionscripts_plg_wt extends lionscripts_plg
	{
		public function __construct($plg_dir)
		{
			global $LIONSCRIPTS, $wpdb;
			
			$this->plg_name 				= 'Webmaster Tools';
			$this->plg_description 			= '';
			$this->plg_version 				= '1.1';
			$this->plg_hook_version 		= '2';
			$this->plg_identifier 			= 'WT';
			$this->plg_db_var_prefix		= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.str_replace(' ', '_', strtolower($this->plg_name)).'_';
			
			$plg_var_arr_set 				= array('google', 'bing', 'yandex', 'alexa', 'pinterest', 'ga', 'custom_al', 'display_attr');
			
			foreach($plg_var_arr_set as $plg_var_arr_name)
				$this->plg_db_var[$plg_var_arr_name] = $this->plg_db_var_prefix.$plg_var_arr_name;
			
			$this->plg_name_2 				= $this->plg_name;
			$this->plg_url_val 				= str_replace(' ', '-', strtolower($this->plg_name));
			$this->plg_product_url 			= LIONSCRIPTS_HOME_PAGE_URL.'product/'.$this->plg_url_val;
			$this->plg_name_pro 			= $this->plg_name.' Pro';
			$this->plg_heading 				= $this->plg_name;
			$this->plg_short_name 			= $this->plg_name;
	
			$this->site_admin_url_val 		= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'-'.$plg_dir;
			$this->site_admin_url 			= get_admin_url().'admin.php?page='.$this->site_admin_url_val;
			$this->site_admin_dashboard_url = get_admin_url().'admin.php?page='.strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'-dashboard';
			$this->site_base				= array('dir'=>ABSPATH, 'www'=>get_bloginfo('wpurl'));
			$this->plg_base 				= array('dir'=>$this->site_base['dir'].'wp-content'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plg_dir.DIRECTORY_SEPARATOR, 'www'=>$this->site_base['www']."/wp-content/plugins/".$plg_dir.'/');
			$this->plg_assets 				= array('dir'=>$this->plg_base['dir'].'assets'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_base['www'].'assets/');
			$this->plg_css 					= array('dir'=>$this->plg_assets['dir'].'css'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'css/');
			$this->plg_images 				= array('dir'=>$this->plg_assets['dir'].'images'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'images/');
			$this->plg_javascript 			= array('dir'=>$this->plg_assets['dir'].'js'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'js/');
			$this->plg_others 				= array('dir'=>$this->plg_assets['dir'].'others'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'others/');
			$this->plg_attr					= '<font style="font-size:12px;"><center>Webmaster tool activated by <a href="'.$this->plg_product_url.'" target="_blank">'.$this->plg_name.'</a> Plugin from <a href="'.LIONSCRIPTS_HOME_PAGE_URL.'" target="_blank">'.LIONSCRIPTS_SITE_NAME.'</a>.</center></font>';
			
			$this->plg_redirect_const 		= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.strtolower($this->plg_identifier)."_activate_redirect";
			$this->plg_db_version_const 	= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.strtolower($this->plg_identifier)."_db_version";
	
			add_action( 'admin_menu', array($this, strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_admin_menu') );
	
			register_activation_hook($this->plg_base['dir'].$plg_dir.'.php', array($this, 'install'));
			register_deactivation_hook($this->plg_base['dir'].$plg_dir.'.php', array($this, 'deactivate'));
			register_uninstall_hook($this->plg_base['dir'].$plg_dir.'.php', strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.strtolower($this->plg_identifier).'_uninstall');
			
			add_action('admin_init', array($this, 'admin_settings_page'));
			add_action('wp_head', array($this, 'apply_front_head_action'));
			add_action('wp_footer', array($this, 'apply_front_foot_action'));
			
			$plugin_file = $this->plg_url_val.'/'.$this->plg_url_val.'.php';
			add_filter("plugin_action_links_".$plugin_file, array($this, 'settings_link'), 10, 2);
		}
		
		public function print_admin_styles()
		{
			echo '<link rel="stylesheet" href="'.$this->plg_css['www'].'style.css" />';
		}
		
		public function get_configuration()
		{
			global $LIONSCRIPTS;
			
			$LIONSCRIPTS[$this->plg_identifier]['meta_tags_data']['google'] = array('url'=>'https://www.google.com/webmasters/tools/', 'name'=>'google-site-verification', 'sample_content'=>'dBw5CvburTd7sIosneAxi537Rp9qi5uG217Hbs8f4p14Vb6JwH');
			$LIONSCRIPTS[$this->plg_identifier]['meta_tags_data']['bing'] = array('url'=>'http://www.bing.com/webmaster', 'name'=>'msvalidate.01', 'sample_content'=>'6G12C8F1209E3B5086AECE794EB3A3DH98309B21E');
			$LIONSCRIPTS[$this->plg_identifier]['meta_tags_data']['yandex'] = array('url'=>'http://webmaster.yandex.com/', 'name'=>'yandex-verification', 'sample_content'=>'79d54e4266011e90');
			$LIONSCRIPTS[$this->plg_identifier]['meta_tags_data']['pinterest'] = array('url'=>'http://www.pinterest.com/', 'name'=>'p:domain_verify', 'sample_content'=>'2h0207296dc58ze5e74db2623bf0aa49');
			
			foreach($this->plg_db_var as $plg_db_var_name=>$plg_db_var_value)
				$LIONSCRIPTS[$this->plg_identifier][$plg_db_var_name] = stripslashes(get_option($this->plg_db_var[$plg_db_var_name]));
 		}
		
		public function save_configuration($data)
		{
			global $LIONSCRIPTS;
			
			foreach($this->plg_db_var as $plg_db_var_name=>$plg_db_var_value)
				update_option( $this->plg_db_var[$plg_db_var_name], (($data[$plg_db_var_name] != '') ? $data[$plg_db_var_name] : '') );

			$this->get_configuration();
		}
		
		public function install()
		{
			global $wpdb;
			add_option($this->plg_db_version_const, $this->plg_version);
			register_setting($this->plg_redirect_const, strtolower($this->plg_identifier).'_activate_redirect');
			add_option($this->plg_redirect_const, true);
		} 
		
		public function deactivate()
		{
			delete_option($this->plg_db_version_const);
			delete_option($this->plg_redirect_const);
		} 
		
		public function settings_link($links)
		{
			$settings_link = '<a href="'.$this->site_admin_url.'">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		public function admin_settings_page()
		{
			if (get_option($this->plg_redirect_const, false)) 
			{
				delete_option($this->plg_redirect_const);
				wp_redirect($this->site_admin_url);
			}
		}
				
		public function lionscripts_admin_menu()
		{
			$this->show_lionscripts_menu();
			add_submenu_page( strtolower(LIONSCRIPTS_SITE_NAME_SHORT), $this->plg_short_name, $this->plg_name, 'level_8', $this->site_admin_url_val, array($this, 'lionscripts_plg_f') );
		}
		
		public function show_lionscripts_menu()
		{
			global $menu;
			$lionscripts_menu_available = false;
			
			foreach($menu as $item)
			{
				if( strtolower($item[0]) == strtolower(LIONSCRIPTS_SITE_NAME_SHORT))
					return $lionscripts_menu_available = true;
			}
			
			if($lionscripts_menu_available == false)
			{
				add_menu_page(LIONSCRIPTS_SITE_NAME_SHORT, LIONSCRIPTS_SITE_NAME_SHORT, 'level_8', strtolower(LIONSCRIPTS_SITE_NAME_SHORT), strtolower(LIONSCRIPTS_SITE_NAME_SHORT), $this->plg_images['www'].'ls-icon-16.png');
	
				add_submenu_page( 
					strtolower(LIONSCRIPTS_SITE_NAME_SHORT) 
					, LIONSCRIPTS_SITE_NAME_SHORT.' Dashboard' 
					, 'Dashboard'
					, 'level_8'
					, strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'-dashboard'
					, array($this, strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_dashboard')
				);
			
				remove_submenu_page( strtolower(LIONSCRIPTS_SITE_NAME_SHORT), strtolower(LIONSCRIPTS_SITE_NAME_SHORT) );
			}
		}
	
		public function lionscripts_dashboard()
		{
			global $LIONSCRIPTS;
			$this->print_admin_styles();
			$this->use_thickbox();
			?>
			<div class="wrap">
				<div class="ls-icon-32">
					<br />
				</div>
				<h2 class="nav-tab-wrapper">
					<a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>" target="_blank"><?php echo LIONSCRIPTS_SITE_NAME; ?></a>
					<a href="<?php echo $this->site_admin_dashboard_url; ?>" class="nav-tab <?php echo ( (!isset($_GET['tab']) || (trim($_GET['tab']) == '')) ? 'nav-tab-active' : '' ); ?>">Dashboard</a>
					<a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>" target="_blank" class="nav-tab">Official Website</a>
					<a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>" target="_blank" class="nav-tab">Technical Support</a>
				</h2>
				<div class="tab_container">
					<div style="width:49%;" class="fluid_widget_container">
						<div class="postbox" id="about_lionscripts">
							<h3><span>About Us</span></h3>
							<div class="inside">
								<div class="">
									<?php
									ksort($LIONSCRIPTS['ABOUT_US']);
									$LIONSCRIPTS['N_ABOUT_US'] = end($LIONSCRIPTS['ABOUT_US']);
									echo $LIONSCRIPTS['N_ABOUT_US'];
									?>
								</div>
							</div>
						</div>
					</div>
					<div style="width:49%;margin-left:1%;" class="fluid_widget_container">
						<div class="postbox" id="more_from_lionscripts">
							<h3><span>Products from our house</span></h3>
							<div class="inside">
								<div class="">
									<p>
										<?php
										ksort($LIONSCRIPTS['WP_PRODUCTS']);
										$LIONSCRIPTS['ALL_WP_PRODUCTS'] = end($LIONSCRIPTS['WP_PRODUCTS']);
										?>
										<ul class="bullet inside">
											<?php
											foreach($LIONSCRIPTS['ALL_WP_PRODUCTS'] as $product_data)
											{
												?>
												<!--<li><a class="thickbox" title="<?php echo $product_data['name']; ?>" href="plugin-install.php?tab=plugin-information&plugin=<?php echo $product_data['wp_url_var']; ?>&TB_iframe=true&width=640&height=500"><?php echo $product_data['name']; ?></a></li>-->
												<li><a target="_blank" title="<?php echo $product_data['name']; ?>" href="<?php echo $product_data['url']; ?>"><?php echo $product_data['name']; ?></a></li>
												<?php
											}
											?>
										</ul>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="cl"></div>
					<div style="width:49%;" class="fluid_widget_container">
						<div class="postbox" id="more_from_lionscripts">
							<h3><span>Questions and Support</span></h3>
							<div class="inside">
								<div class="">
									<p>
										<?php echo LIONSCRIPTS_SITE_NAME; ?> provides 24x7 support for all its products and services. So in terms of service, you don't need to worry about the techincal support. 
									</p>
									<p>
										If you have any concern or issue regarding any of our software, please visit <a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>ask" target="_blank"><?php echo preg_replace('/\/|http\:/i', '', LIONSCRIPTS_SUPPORT_PAGE_URL); ?>/ask</a> and provide complete details of your issue.
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		
		public function lionscripts_plg_f()
		{
			global $LIONSCRIPTS;
			$this->print_admin_styles();
			$this->use_thickbox();

			if($_POST)
			{
				if(isset($_GET['save_type']) && ($_GET['save_type'] == 'configuration'))
					$this->save_configuration($_POST);

				$response = '<center><b><font class="success">Settings has been successfully updated</font></b></center>';
			}
			
			$this->get_configuration();
			
			?>
			<div class="wrap">
				<div class="icon-32">
					<br />
				</div>
				<h2><?php echo $this->plg_heading; ?> - Settings</h2>
				<div class="content_left">
					<div id="lionscripts_plg_settings">
                        Plugin Version: <b><font class="version"><?php echo $this->plg_version; ?></font> <font class="lite_version"></font></b>
                        &nbsp; | &nbsp;
                        <b><a href="<?php echo $this->plg_product_url; ?>" target="_blank" title="Buy the <?php echo $this->plg_name_pro; ?>">Visit Plugin Page</a></b>
                        &nbsp; | &nbsp;
                        <b><a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>" target="_blank" title="Visit Official Website">Official Website</a></b>
                        &nbsp; | &nbsp;
                        <b><a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>" target="_blank" title="Buy the <?php echo $this->plg_name_pro; ?>">Technical Support</a></b>
						<br /><br />

						<?php 
						if(isset($response))
						{
							echo $response.'<br />';
						}
						?>
						<form action="admin.php?page=<?php echo $this->site_admin_url_val; ?>&save_type=configuration" method="post">
                            <table class="form-table">
								<tbody>
                                	<?php
									
									foreach($this->plg_db_var as $plg_db_var_name=>$plg_db_var_value)
									{
										if(!isset($LIONSCRIPTS[$this->plg_identifier]['meta_tags_data'][$plg_db_var_name]))
											continue;
										?>
                                        <tr valign="top">
                                            <th scope="row">
                                                <label for="<?php echo $plg_db_var_name; ?>" title="<?php echo ucwords($plg_db_var_name); ?> Verification Meta Tag Content ID"><?php echo ucwords($plg_db_var_name); ?> Verification Meta ID</label>
                                                <p class="description">
                                                	<a href="<?php echo $LIONSCRIPTS[$this->plg_identifier]['meta_tags_data'][$plg_db_var_name]['url']; ?>" title="Visit <?php echo ucwords($plg_db_var_name); ?> Webmaster Tools" target="_blank">Visit <?php echo ucwords($plg_db_var_name); ?> Webmaster Tools</a>
                                                </p>
                                            </th>
                                            <td>
                                                <input type="text" class="large-text webmaster_tools_meta_input" id="<?php echo $plg_db_var_name; ?>" name="<?php echo $plg_db_var_name; ?>" placeholder="For example: <?php echo $LIONSCRIPTS[$this->plg_identifier]['meta_tags_data'][$plg_db_var_name]['sample_content']; ?>"value="<?php echo (($LIONSCRIPTS[$this->plg_identifier][$plg_db_var_name] != '') ? $LIONSCRIPTS[$this->plg_identifier][$plg_db_var_name] : ''); ?>" />
                                                <p class="description">
                                                    &lt;meta name='<?php echo $LIONSCRIPTS[$this->plg_identifier]['meta_tags_data'][$plg_db_var_name]['name']; ?>' content='<b><?php echo $LIONSCRIPTS[$this->plg_identifier]['meta_tags_data'][$plg_db_var_name]['sample_content']; ?></b>'/&gt;
                                                </p>
                                            </td>
                                        </tr>
                                        <?php
									}
									?>
									
                                	<tr valign="top">
                                        <th scope="row">
                                        	<label for="alexa">Alexa Verification ID</label>
                                            <p class="description">
                                            	<a href="https://www.alexa.com/" title="Visit Alexa Webmaster Tools">Visit Alexa Webmaster Tools</a>
                                            </p>
                                        </th>
                                        <td>
                                        	<input type="text" class="large-text webmaster_tools_meta_input" id="alexa" name="alexa" placeholder="For example: ak3h9HpGxv9JFPmN3Pp8N9gH9ER" value="<?php echo (($LIONSCRIPTS[$this->plg_identifier]['alexa'] != '') ? $LIONSCRIPTS[$this->plg_identifier]['alexa'] : ''); ?>" />
                                        </td>
                                    </tr>
                                    
                                	<tr valign="top">
                                        <th scope="row">
                                        	<label for="ga">Google Analytics Tracking ID</label>
                                            <p class="description">
	                                            <a href="https://www.google.com/analytics/" title="Visit Google Analytics">Visit Google Analytics</a>
                                            </p>
                                        </th>
                                        <td>
                                        	<input type="text" class="large-text webmaster_tools_meta_input" id="ga" name="ga" placeholder="For example: UA-864839-1" value="<?php echo (($LIONSCRIPTS[$this->plg_identifier]['ga'] != '') ? $LIONSCRIPTS[$this->plg_identifier]['ga'] : ''); ?>" />
                                        </td>
                                    </tr>
                                    
                                	<tr valign="top">
                                        <th scope="row">
                                        	<label for="custom_al">Custom Analytics Tracking Code</label>
                                        </th>
                                        <td>
                                        	<textarea class="large-text" id="custom_al" name="custom_al" placeholder="Please enter your custom analytics code, whether JavaScript or HTML to insert into your wordpress website pages into footer (Please don't put PHP code here, as it might harm your website)"><?php echo (($LIONSCRIPTS[$this->plg_identifier]['custom_al'] != '') ? $LIONSCRIPTS[$this->plg_identifier]['custom_al'] : ''); ?></textarea>
                                        </td>
                                    </tr>


                                	<tr valign="top">
                                        <th scope="row">
                                        	<label for="display_attr_yes">Proudly Display that you are using <?php echo LIONSCRIPTS_SITE_NAME_SHORT; ?> Webmaster Tools</label>
                                        </th>
                                        <td>
                                            <label><input type="radio" name="display_attr" id="display_attr_yes" value="1" <?php echo (($LIONSCRIPTS[$this->plg_identifier]['display_attr'] == 1) ? 'checked'  : ''); ?> /> Yes</label>
                                            &nbsp;&nbsp;
                                            <label><input type="radio" name="display_attr" id="display_attr_no" value="0" <?php echo ( !isset($LIONSCRIPTS[$this->plg_identifier]['display_attr']) || ($LIONSCRIPTS[$this->plg_identifier]['display_attr'] == 0) ? 'checked'  : ''); ?> /> No</label>
                                        </td>
                                    </tr>
                                    
							</table>
							<input type="hidden" name="submit_form" value="submit_form" />
                            <p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
						</form>
		
						<br />
						<div class="lionscripts_plg_footer">
							<p>
								<small>For all kind of Inquiries and Support, please visit at <a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>ask" target="_blank"><?php echo preg_replace('/\/|http\:/i', '', LIONSCRIPTS_SUPPORT_PAGE_URL); ?>/ask</a>.</small>
							</p>
							<p>
								<ul class="socialicons color">
									<li><a href="<?php echo LIONSCRIPTS_FACEBOOK_LINK; ?>" target="_blank" class="facebook"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_TWITTER_LINK; ?>" target="_blank" class="twitter"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_GOOGLE_PLUS_LINK; ?>" target="_blank" class="gplusdark"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_YOUTUBE_LINK; ?>" target="_blank" class="youtube"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>shop/feed" target="_blank" class="rss"></a></li>
								</ul>
								<div class="cl"></div>
							</p>
						</div>
					</div>
				</div>
				
				<div id="<?php echo str_replace(' ', '_', strtolower($this->plg_name)); ?>_right_container" class="content_right">
					<a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL.'product/wordpress-ip-address-blocker-pro/'; ?>" target="_blank"><img src="<?php echo $this->plg_images['www']."pro.png"; ?>" border="0" /></a>
				</div>
			</div>
			<script type="text/javascript">
			(function($)
			{
				$(document).ready(function(e) {
					$('.webmaster_tools_meta_input').bind('input', function(e){
						this.value = this.value
											.trim()
											.replace(/"/g, '\'')
											.replace(/google-site-verification/g, '')
											.replace(/msvalidate.01/g, '')
											.replace(/yandex-verification/g, '')
											.replace(/p:domain_verify/g, '')
											.replace(/<meta name='' content='/i, '')
											.replace(/'\/>/g, '')
											.replace(/ '\/>/g, '')
											.replace(/'/g, '')
											.replace(/>/g, '')
											.trim()
											;
					});
				});
			}
			)(jQuery);
			</script>
			<?php
		}

		public function apply_front_head_action()
		{
			global $LIONSCRIPTS;

			$meta_tags = '';
			$meta_tags .= "\n<!-- LionScripts: Webmaster Tools Head Start -->\n";

			if($LIONSCRIPTS[$this->plg_identifier]['google'] != '')
				$meta_tags .= '<meta name="google-site-verification" content="'.$LIONSCRIPTS[$this->plg_identifier]['google'].'" />';

			if($LIONSCRIPTS[$this->plg_identifier]['bing'] != '')
				$meta_tags .= "<meta name='msvalidate.01' content='".$LIONSCRIPTS[$this->plg_identifier]['bing']."'>";

			if($LIONSCRIPTS[$this->plg_identifier]['yandex'] != '')
				$meta_tags .= "<meta name='yandex-verification' content='".$LIONSCRIPTS[$this->plg_identifier]['yandex']."'>";

			if($LIONSCRIPTS[$this->plg_identifier]['pinterest'] != '')
				$meta_tags .= "<meta name='p:domain_verify' content='".$LIONSCRIPTS[$this->plg_identifier]['pinterest']."'>";

			if($LIONSCRIPTS[$this->plg_identifier]['alexa'] != '')
				$meta_tags .= '<meta name="alexaVerifyID" content="'.$LIONSCRIPTS[$this->plg_identifier]['alexa'].'" />';

			$meta_tags .= "\n<!-- LionScripts: Webmaster Tools Head End -->\n";

			echo $meta_tags;
		}
		
		public function apply_front_foot_action($return=false)
		{
			global $LIONSCRIPTS;
			$this->get_configuration();
			
			$d_n = $this->get_domain_name($this->site_base['www'], PHP_URL_PATH);

			$footer_text = '';
			$footer_text .= "\n<!-- LionScripts: Webmaster Tools Foot Start -->\n";
			$footer_text .= (($LIONSCRIPTS[$this->plg_identifier]['ga'] != '') ? "
							<script>
							  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
							  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
							  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
							  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
					
							  ga('create', '".$LIONSCRIPTS[$this->plg_identifier]['ga']."', '".$d_n."');
							  ga('send', 'pageview');
							</script>
							"
 							: '');
			$footer_text .= (($LIONSCRIPTS[$this->plg_identifier]['custom_al'] != '') ? $LIONSCRIPTS[$this->plg_identifier]['custom_al'] : '');
			$footer_text .= (($LIONSCRIPTS[$this->plg_identifier]['display_attr'] == 1) ? $this->plg_attr : '');
			$footer_text .= "\n<!-- LionScripts: Webmaster Tools Foot End -->\n";
			
			if($return == true)
				return $footer_text;
			else
				echo $footer_text;
		}
		
		public function plugin_is_active($plugin_var)
		{
			return in_array( $plugin_var. '/' .$plugin_var. '.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
		}
		
		public function get_domain_name($url)
		{
			$host = @parse_url($url, PHP_URL_HOST);
			
			if (!$host)
				$host = $url;

			if (substr($host, 0, 4) == "www.")
				$host = substr($host, 4);
			
			if (strlen($host) > 50)
				$host = substr($host, 0, 47) . '...';
			
			return $host;
		}
		
	}
}


?>