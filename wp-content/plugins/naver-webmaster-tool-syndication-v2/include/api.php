<?php
global $wpdb;

header('Content-type: text/xml', true);
echo '<?xml version="1.0" encoding="utf-8"?' . '>';
?>
<feed xmlns="http://webmastertool.naver.com">
    <id><?php bloginfo_rss('url'); ?></id>
    <title><?php $_nwsv2['lib']->syndi_cdata(bloginfo_rss('name')); ?></title>
    <author>
        <name><?php $_nwsv2['lib']->syndi_cdata(bloginfo_rss('name')); ?></name>
        <email><?php $_nwsv2['lib']->syndi_cdata(bloginfo_rss('admin_email')); ?></email>
    </author>
    <updated><?php echo date(DATE_ATOM); ?></updated>
    <link rel="site"
          href="<?php echo $_nwsv2['lib']->syndi_site_url(); ?>"
          title="<?php htmlspecialchars(bloginfo_rss('name')); ?>" />

    <?php
    if ($posts_id) :
        function remove_hellip($more){return '';}
        add_filter('excerpt_more', 'remove_hellip');
        
        foreach ($posts_id as $post_id) {
            $entry = get_post($post_id);
            
            if($this->mode=='cancelxml' || $this->mode=='delxml'){
                ?>
                <deleted-entry
                ref="<?php echo $_nwsv2['lib']->get_perma_link($post_id); ?>"
                when="<?php echo mysql2date(DATE_ATOM, $entry->post_modified); ?>" />
                <?php
                if($this->mode=='delxml')
                    $_nwsv2['lib']->set_meta($post_id, 'delete');
                else
                    $_nwsv2['lib']->set_meta($post_id, 'cancel');
            }
            else {
                if (count($entry) == 0 || $entry->post_status != 'publish'){
                    $_nwsv2['lib']->set_meta($post_id, 'skip');
                    continue;
                }
                setup_postdata($entry);
                
                $content_html = $_nwsv2['lib']->get_the_content();
                
                if (seems_utf8($content_html) == false)
                    $content_html = utf8_encode($content_html);
                if ($entry->post_parent) {
                    $viaPost = $wpdb->get_row("select post_title from {$wpdb->posts} where ID = {$entry->post_parent}");
                }
                ?>
                <entry>
                    <id><?php echo $_nwsv2['lib']->get_perma_link($post_id); ?></id>
                    <title><?php
                        if($this->options['seo']=='allinone')
                            $title = get_post_meta( $post_id, "_aioseop_title", TRUE );
                        else
                            $title = '';
                        
                        if($title) echo $_nwsv2['lib']->syndi_cdata(htmlspecialchars(stripslashes($title)));
                        else echo $_nwsv2['lib']->syndi_cdata(apply_filters('the_title_rss', $entry->post_title));
                    ?></title>
                    <author>
                        <name><?php echo $_nwsv2['lib']->syndi_cdata(get_the_author_meta('display_name')); ?></name>
                    </author>
                    <updated><?php echo mysql2date(DATE_ATOM, $entry->post_modified); ?></updated>
                    <published><?php echo mysql2date(DATE_ATOM, $entry->post_date_gmt); ?></published>

                    <?php if ($entry->post_parent): ?>
                        <link rel="via" href="<?php echo $_nwsv2['lib']->get_perma_link($entry->post_parent) ?>"
                              title="<?php echo htmlspecialchars(apply_filters('the_title_rss', $viaPost->post_title)) ?>" />
                    <?php endif ?>

                    <link rel="mobile" href="<?php echo htmlspecialchars($_nwsv2['lib']->get_perma_link($post_id)); ?>" />
                    <content type="html"><?php echo $_nwsv2['lib']->syndi_cdata($content_html) ?></content>
                    <summary type="text"><?php echo $_nwsv2['lib']->syndi_cdata($_nwsv2['lib']->strip_html($content_html)) ?></summary>
                </entry>
                <?php
                $_nwsv2['lib']->set_meta($post_id, 'complete');
            }
        }
    endif;
    ?>
</feed>