<?php
final class nws_dir_class {
    private $plugin_dir;
    private $inc_dir;
    private $plugin_url;
    private $css_url;
    private $js_url;
    
    function __construct(){
        $this->plugin_dir = dirname(dirname(__FILE__));
        $this->inc_dir = $this->plugin_dir . '/include';

        $this->plugin_url = plugins_url('', dirname(__FILE__));
        $this->css_url = $this->plugin_url . '/css';
        $this->js_url = $this->plugin_url . '/js';
    }
    
    public function plugin_dir(){
        return $this->plugin_dir;
    }
    public function inc_dir(){
        return $this->inc_dir;
    }
    public function plugin_url(){
        return $this->plugin_url;
    }
    public function css_url(){
        return $this->css_url;
    }
    public function js_url(){
        return $this->js_url;
    }
}
?>