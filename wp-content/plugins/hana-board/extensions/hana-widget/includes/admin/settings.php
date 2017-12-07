<?php
/**
 * WordPress settings API class
 *
 * @author HanaWordpress
 */
if (!defined('HANAWORDPRESS_MENU'))
    define('HANAWORDPRESS_MENU', 'hanawordpress-main');


class HanaWidget_Settings
{
    private $settings_api;
    private $options_page;
    private $page_title;
    private $page;
    private $version;
    private $settings_key;
    private $available_sns;

    function __construct()
    {
        $this->page = "hana-widget";
        $this->page_title = __('Hana Posts & Comments Widget', HANA_WIDGET_TEXT_DOMAIN);
        $this->settings_key = "hana_widget";
        $this->version = "0.1";
        $this->available_sns = array(
            'facebook' => __('Facebook', HANA_WIDGET_TEXT_DOMAIN),
            'twitter' => __('Twitter', HANA_WIDGET_TEXT_DOMAIN),
            'google' => __('Google+', HANA_WIDGET_TEXT_DOMAIN),
            'kakaostory' => __('Kakao Story', HANA_WIDGET_TEXT_DOMAIN),
            'kakaotalk' => __('Kakao Talk', HANA_WIDGET_TEXT_DOMAIN),
            'naverline' => __('Line', HANA_WIDGET_TEXT_DOMAIN),
            'naverband' => __('Naver Band', HANA_WIDGET_TEXT_DOMAIN),
            'naverblog' => __('Naver Blog', HANA_WIDGET_TEXT_DOMAIN)
        );

        add_action('wp_loaded', array(
            &$this,
            'admin_init'
        ));
        add_action('admin_menu', array(
            &$this,
            'admin_menu'
        ));
    }

    function admin_init()
    {
        // initialize settings
        if (isset($_POST ['hana_widget_options_submit'])) {
            if (!wp_verify_nonce($_POST ['hana_widget-settings-nonce'], 'hana_widget-settings-form')) {
                wp_die(__('Cheating?', HANA_BOARD_TEXT_DOMAIN));
            }
            add_action('wp_loaded', array(
                $this,
                'settings_submit'
            ));
            $this->settings_submit();
        }
    }

    /**
     * Register the admin menu
     *
     * @since 0.1
     */
    function admin_menu()
    {
        if (!empty($GLOBALS ['admin_page_hooks'] [HANAWORDPRESS_MENU])) {
            add_submenu_page(HANAWORDPRESS_MENU, __('Hana Post Widget', HANA_WIDGET_TEXT_DOMAIN), __('Hana Post Widget', HANA_WIDGET_TEXT_DOMAIN), 'activate_plugins', $this->page, array(
                &$this,
                'settings_page'
            ));
        } else {
            add_submenu_page('themes.php', __('Hana Post Widget', HANA_WIDGET_TEXT_DOMAIN), __('Hana Post Widget', HANA_WIDGET_TEXT_DOMAIN), 'activate_plugins', $this->page, array(
                &$this,
                'settings_page'
            ));
        }
    }

    function admin_enqueue_scripts()
    {
        global $wp_scripts;
        //wp_enqueue_style( 'hanaboard-admin', hanaboard_plugins_url( 'includes/admin/css/hanaboard_admin.css' ) );
        wp_enqueue_style('hanaboard_bootstrap_grid', hanaboard_plugins_url('css/bootstrap-grid12.css'));
        wp_register_style('jquery-ui-bootstrap', hanaboard_plugins_url('css/jquery-ui-1.10.3.custom.css'));
        //wp_register_style( 'hana-like-admin', plugins_url( 'css/admin.css', __FILE__ ) );
        //wp_enqueue_style( 'hana-like-admin' );
        wp_enqueue_style('jquery-ui-bootstrap');
    }

    function settings_submit()
    {
        $settings = array(
            'widget_skin' => $_POST ['widget_skin'],
        );
        $this->update_plugin_settings($settings);
    }

    function update_plugin_settings($values = array())
    {
        $settings = $values;
        update_option($this->settings_key, $settings);
    }

    function get_plugin_settings($key = '', $default = '')
    {
        $settings = get_option($this->settings_key);
        if (FALSE === $settings) {
        } elseif (!$key) {
            return $settings;
        } elseif (array_key_exists($key, $settings)) {
            return $settings [$key];
        } else {
            return $default;
        }

        return false;
    }

    function get_available_post_types()
    {
        $default_post_types = array(
            'page',
            'post'
        );
        $post_types = get_post_types(array(
            'has_archive' => true
        ));
        $post_types = array_merge($default_post_types, $post_types);
        return $post_types;
    }

    function skin_list($type = 'post')
    {
        $default_skins = array(
            'default' => 'default'
        );
        if ($type != 'post' && $type != 'comment') $type = 'post';
        $skins_dir = glob(HANA_WIDGET_BASE_DIR . '/layouts/' . $type . '/*', GLOB_ONLYDIR | GLOB_ERR);
        $skins = array();
        foreach ($skins_dir as $dir) {
            $skin_name = end(explode('/', $dir));
            $skins [$skin_name] = $skin_name;
        }
        if (sizeof($skins) > 0)
            return $skins;
        else
            return $default_skins;
    }

    function settings_page()
    {
        global $options, $current;
        $title = $this->page_title;

        $messages = array();

        if (isset($_POST) && isset($_POST ['hana_widget_options_submit'])) {
            $messages [] = array(
                'type' => 'message',
                'message' => __("Hana Widget settings saved.", HANA_WIDGET_TEXT_DOMAIN)
            );
        }
        $predfined_shortcodes = array();
        $skin_list_post = $this->skin_list('post');
        $skin_list_comment = $this->skin_list('comment');

        $post_types = $this->get_available_post_types();
        $hanaboard_post_name = __('Hana Board Post', HANA_WIDGET_TEXT_DOMAIN);
        $settings = $this->get_plugin_settings();
        $current = explode(',', $current ['post_types']);

        include_once("settings-page.php");
    }

}

$hana_widget_settings = new HanaWidget_Settings();