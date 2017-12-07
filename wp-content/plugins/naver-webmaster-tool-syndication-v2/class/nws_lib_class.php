<?php
class nws_lib_class {
    public function get_post_types() {
        return get_post_types(nws_Main_class::$post_types_args, 'names');
    }

    public function get_posttypes() {
        return implode("','", self::get_post_types());
    }
    
    public function get_post_cnt($post_type = 'post', $value = '') {
        global $wpdb, $_nwsv2;

        if ($post_type == 'etc')
            $post_type = $_nwsv2['lib']->get_posttypes();

        if ($value) {
            $value = "m.meta_value = '{$value}'";
        } else {
            $value = "(m.meta_value in ('".implode("','", $_nwsv2['m']->nosync_doc)."') or m.meta_value is null)";
        }

        $num = $wpdb->get_var(""
               . "SELECT count(p.id) FROM {$wpdb->posts} p"
                . " LEFT JOIN {$wpdb->postmeta} m ON (m.post_id = p.id AND m.meta_key = '{$_nwsv2['m']->metaKey}')"
                . " WHERE p.post_type in ( '{$post_type}' )"
                . " AND p.post_status = 'publish'"
                . " AND p.post_password = ''"
                . " AND {$value}"
                . "");
                
        return number_format($num);
    }

    public function ping($id) {
        global $_nwsv2;
        
        if (!$id)
            return $_nwsv2['m']->error('요청할 대싱이 없습니다.');
        
        if(!is_array($id))
            $id = array($id);

        if($_nwsv2['m']->action == 'cancel'){
            $param = 'cancelxml';
            $newid = array();
            foreach ($id as $post_id){
                $meta = get_post_meta($post_id, $_nwsv2['m']->metaKey, true);
                if ($meta == 'complete') $newid[] = $post_id;
            }
            if(count($newid) == 0) return;
            $id = $newid;
        }
        else if($_nwsv2['m']->action == 'delete'){
            $param = 'delxml';
        }
        else{
            $param = 'xml';
        }
        
        $ping_url = home_url() . '/?syndi_api_mode='.$param.'&cd=' . urlencode(base64_encode(implode(',', $id)));
        
        $ping_auth_header = "Authorization: Bearer ".$_nwsv2['m']->options['token'];
        $ping_client_opt = array(
            CURLOPT_URL => "https://apis.naver.com/crawl/nsyndi/v2",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => "ping_url=" . urlencode($ping_url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => array("Host: apis.naver.com", "Pragma: no-cache", "Accept: */*", $ping_auth_header)
        );
        $ping = curl_init();
        curl_setopt_array($ping, $ping_client_opt);
        $xml = curl_exec($ping);
        curl_close($ping);

        $xml = @simplexml_load_string($xml);
        if ($xml->error_code != '000') {
            if ($xml->message)
                return $_nwsv2['m']->error('네이버 서버에 핑요청 실패입니다.', $xml->message, $xml->error_code);
            else
                return $_nwsv2['m']->error('네이버 서버에 핑요청 실패입니다. 다시 시도해 주세요.');
        }
        
        if($_nwsv2['m']->action == 'ping'){
            foreach ($id as $post_id)
                $this->set_meta($post_id, 'ready');
        }
    }


    public function syndi_site_url() {
        if (is_multisite())
            return network_home_url();
        else
            return get_bloginfo_rss('url');
    }

    public function syndi_cdata($str) {
        return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $str) . ']]>';
    }

    /**
     * Removes invalid XML
     *
     * @access public
     * @param string $value
     * @return string
     */
    public function stripInvalidXML($value) {
        $ret = "";
        $current;

        if (empty($value))
            return $ret;

        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $current = ord($value{$i});
            if (($current == 0x9) ||
                    ($current == 0xA) ||
                    ($current == 0xD) ||
                    (($current >= 0x20) && ($current <= 0xD7FF)) ||
                    (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                    (($current >= 0x10000) && ($current <= 0x10FFFF))) {
                $ret .= chr($current);
            } else {
                $ret .= " ";
            }
        }
        return $ret;
    }

    public function mb_convert_encode($m){
        return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
    }
    
    public function get_the_content() {
        $content = apply_filters('the_content', get_the_content());
        $content = str_replace(']]>', ']]&gt;', $content);
        $content = preg_replace_callback("/(&#[0-9]+;)/", array($this, 'mb_convert_encode'), $content);
        $content = $this->stripInvalidXML(htmlspecialchars_decode($content));
        return $content;
    }

    public function strip_html($content) {
        $content = wp_strip_all_tags($content, true);
        $content = str_replace('&nbsp;', '', $content);
        return trim($content);
    }

    public function get_perma_link($id) {
        return esc_url(apply_filters('the_permalink_rss', get_permalink($id)));
    }

    public function set_meta($post_id, $value) {
        global $_nwsv2;
        if (!update_post_meta($post_id, $_nwsv2['m']->metaKey, $value))
            add_post_meta($post_id, $_nwsv2['m']->metaKey, $value, true);
    }
    
    public function get_the_date( $post ) {
            $post = get_post( $post );
            $the_date = mysql2date( get_option( 'date_format' ), $post->post_date );
            return apply_filters( 'get_the_date', $the_date, '', $post );
    }
}
?>