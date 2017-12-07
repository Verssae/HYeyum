<?php
if (! function_exists ( 'hanawordpress_display_support_center' )) {
	add_action ( 'hanawordpress_display_support_center', 'hanawordpress_display_support_center' );
	function hanawordpress_display_support_center() {
		?>
<div class="description">
	<ul>
		<li><a href="http://hanawordpress.com/"><?php _e('HanaWordpress Home', HANA_BOARD_TEXT_DOMAIN)?></a></li>
		<li>
			<div class="hana_support_center_title">
				<a href="http://hanawordpress.com/커뮤니티"><?php _e('Community', HANA_BOARD_TEXT_DOMAIN)?></a>
			</div>
			<p class="description"></p>
		</li>
		<li><a href="http://hanawordpress.com/hana-board/demo"><?php _e('Demo', HANA_BOARD_TEXT_DOMAIN)?></a></li>
		<li><a href="http://hanawordpress.com/%EA%B2%AC%EC%A0%81%EB%AC%B8%EC%9D%98/"><?php _e('Technical Support', HANA_BOARD_TEXT_DOMAIN)?></a></li>
	</ul>
</div>
<?php
	}
}
if (! function_exists ( 'hanawordpress_display_donate' )) {
	add_action ( 'hanawordpress_display_donate', 'hanawordpress_display_donate' );
	function hanawordpress_display_donate() {
		?>
<h4 class="description">
	<i class="fa fa-beer"></i> <?php _e('Buy me a beer',HANA_BOARD_TEXT_DOMAIN);?> :)
					</h4>
<p class="description">
					
					<?php _e('Your donation will make power to better plugins to developer. Thank you.',HANA_BOARD_TEXT_DOMAIN);?></p>
<div class="text-center">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post"
		target="_top">
		<input type="hidden" name="cmd" value="_s-xclick"> <input
			type="hidden" name="hosted_button_id" value="XZNRZMAFTFWPY"> <input
			type="image"
			src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
			name="submit" alt="PayPal - The safer, easier way to pay online!"> <img
			alt="" border="0"
			src="https://www.paypalobjects.com/ko_KR/i/scr/pixel.gif" width="1"
			height="1">
	</form>
</div>
<?php
	}
}

if (! function_exists ( 'hanawordpress_display_feeds' )) {
	add_action ( 'hanawordpress_display_feeds', 'hanawordpress_display_feeds' );
	add_action ( 'hanawordpress_display_product_feeds', 'hanawordpress_display_feeds', 10, 1 );
	function hanawordpress_display_feeds($item = '') {
		$hanawordpress_home = HANAWORDPRESS_HOME;
		if ($item)
			$rss_param = $item . '/feed/';
		else
			$rss_param = 'feed/';
		$rss = fetch_feed ( $hanawordpress_home . $rss_param );
		if (is_wp_error ( $rss )) {
			if (is_admin () || current_user_can ( 'manage_options' )) {
				echo '<p>';
				printf ( __ ( '<strong>RSS Error</strong>: %s' ), $rss->get_error_message () );
				echo '</p>';
			}
			return;
		}
		
		if (! $rss->get_item_quantity ()) {
			echo '<p>' . __ ( 'Apparently, there are no updates to show!', HANA_BOARD_TEXT_DOMAIN ) . '</p>';
			$rss->__destruct ();
			unset ( $rss );
			return;
		}
		
		echo "<div>\n";
		
		if (! isset ( $items ))
			$items = 5;
		
		foreach ( $rss->get_items ( 0, $items ) as $item ) {
			$publisher = '';
			$site_link = '';
			$link = '';
			$content = '';
			$date = '';
			$link = esc_url ( strip_tags ( $item->get_link () ) );
			$title = esc_html ( $item->get_title () );
			$content = $item->get_content ();
			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $img);
			$post_thumbnail =  $img[1];
			$content = wp_html_excerpt ( wp_strip_all_tags ( $content ), 250 );
			$display_image='';
			if( $post_thumbnail ) {
				$display_image = '<img src="'.$post_thumbnail.'" style="max-width:100%;" />';
			}
			echo "<div class='row clear'><div class='col-xs-3'></div><div class='col-xs-12'><a class='rsswidget' href='$link'>$title</a>\n<div style='display:none;' class='rssSummary'>$content</div></div></div>\n";
		}
		
		echo "</div>\n";
		$rss->__destruct ();
		unset ( $rss );
	}
}
if ( !function_exists('hanawordpress_display_dashboard')) {
	add_action ( 'hanawordpress_display_dashboard', 'hanawordpress_display_dashboard' );
	function hanawordpress_display_dashboard() {
		?>
	<div class="hana_dashboard">
		<div class="dashboard_wrapper">
			<div class="dashboard_section col-md-4">
				<h3><?php _e('Hana Wordpress News', HANA_BOARD_TEXT_DOMAIN); ?></h3>
				<div class="description">
					<?php do_action('hanawordpress_display_feeds'); ?>
				</div>
			</div>
		</div>
		<div class="dashboard_wrapper">
			<div class="dashboard_section col-md-4">
				<h3><?php _e('Hana Wordpress Customer Support', HANA_BOARD_TEXT_DOMAIN);?></h3>
				<div class="description">
				<?php do_action('hanawordpress_display_support_center'); ?>
				</div>
			</div>
			<div class="dashboard_section col-md-4">
				<h3><?php _e('Donate', HANA_BOARD_TEXT_DOMAIN);?></h3>
				<div class="description">
					<?php do_action('hanawordpress_display_donate'); ?>
				</div>
			</div>
		</div>
	
	</div>
	<?php
	}
}
?>
