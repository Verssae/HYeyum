<?php
$_nwsv2 = array();

final class nws_Main_class {
    public $debug = false;
    public $metaKey = '_post_status';
    public $options;
    public $action;
    public $nosync_doc = array('', 'delete', 'skip', 'cancel');

    private $title = '네이버 웹문서 신디케이션v2';
    private $require_wp_version = '3.7.1';
    private $plugin_version = '1.1';
    private $posts_per_page = 50;
    private $mode;
    private $paged;
    private $pinglist;
    private $errorMessage;
    private $errorNaverMessage;
    private $errorNaverCode;
    private $post_args = array(
        'has_password' => false,
        'suppress_filters' => true,
        'post_status' => 'publish'
    );
    private static $post_status = array(
        '' => '비연동',
        'complete' => '연동완료',
        'ready' => '연동대기중',
        'skip' => '연동skip',
        'delete' => '연동삭제',
        'cancel' => '연동취소'
    );
    private $listType;
    private $st;
    
    public static $post_types_args = array(
        'public' => true,
        '_builtin' => false,
        'publicly_queryable' => true
    );

    /**
     * 새로운 인스턴스
     * @return nws_Main_class instance
     */
    public static function instance() {
        global $_nwsv2;
        
        if (!$_nwsv2['m'])
            $_nwsv2['m'] = new nws_Main_class();

        $_nwsv2['m']->checkParameter();
        $_nwsv2['m']->hooks();
        
        return $_nwsv2['m'];
    }

    public function __construct() {
        global $_nwsv2;
        
        if($this->debug){
            ini_set('display_errors', 'On');
            error_reporting(E_ALL ^ E_NOTICE);            
        }
        
        // template
        $_nwsv2['tpl'] = new nws_tpl_class();
        
        // directory
        $_nwsv2['dir'] = new nws_dir_class();
        
        // lib
        $_nwsv2['lib'] = new nws_lib_class();

        // init
        $this->options = get_option(NWS_PREFIX . '_setting_field');
        $this->metaKey = NWS_PREFIX . $this->metaKey;
    }

    /**
     * 파라미터 체크
     * @return void
     */
    public function checkParameter() {
        $this->action = ( $_REQUEST['action'] != -1 ) ? $_REQUEST['action'] : $_REQUEST['action2'];
        $this->pinglist = $_POST['pinglist'];
        $this->mode = $_GET['syndi_api_mode'];
        $this->paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $this->listType = trim($_GET['list']);
        $this->st = trim($_GET['st']);

        if ($this->mode)
            $this->modeProcess();
        else if ($this->action && is_admin())
            $this->actionProcess();
    }

    private function post_args($post_id = '') {
        if ($post_id)
            return array('p' => $post_id) + $this->post_args;
        else
            return $this->post_args;
    }

    private function modeProcess() {
        switch ($this->mode) {
            case 'xml':case 'delxml':case 'cancelxml':
                add_action('init', array($this, 'apiXml'), 100);
                break;
        }
    }

    private function actionProcess() {
        global $_nwsv2;
        switch ($this->action) {
            case 'ping':case 'cancel':
                if ($this->pinglist)
                    $_nwsv2['lib']->ping($this->pinglist);
                break;
        }
    }

    public function pageChk($countQuery, $perPage) {
        global $wpdb;

        $startOffset = ( $this->paged - 1 ) * $perPage;
        $totalCnt = $wpdb->get_var($countQuery);
        $totalPage = ceil($totalCnt / $perPage);

        return array($startOffset, $totalPage);
    }

    public function apiXml() {
        global $_nwsv2;
        
        $cd = $_GET['cd'];
        if (!$cd) return;

        $cd = explode(',', base64_decode($cd));
        $posts_id = array();
        
        foreach ($cd as $post_id) {
            $meta = get_post_meta($post_id, $this->metaKey, true);
            if ($meta != '')
                $posts_id[] = $post_id;
        }

        if (count($posts_id) == 0) return;

        include_once $_nwsv2['dir']->inc_dir() . '/api.php';

        die();
    }

    public function get_posts_args() {
        global $_nwsv2, $wpdb;
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS p.ID FROM {$wpdb->posts} p";
        $where = "WHERE p.post_password = ''
                    AND p.post_type in ('".(($this->listType == 'etc') ? $_nwsv2['lib']->get_posttypes() : $this->listType)."')
                    AND p.post_status = 'publish'";
        $order = "GROUP BY p.ID
                  ORDER BY p.post_type, p.post_date DESC";
        
        $metaSQL = "JOIN {$wpdb->postmeta} m ON (p.ID = m.post_id AND m.meta_key = '{$this->metaKey}')";
        if ($this->st) {
            
            $sql .= " INNER $metaSQL";
            $where .=" AND m.meta_value = '{$this->st}'";
        } else {
            $sql .= " LEFT $metaSQL";
            $where .=" AND (
                m.meta_value in ('".implode("','", $this->nosync_doc)."')
                or m.meta_value is null
            )";
        }
        
        return "$sql $where $order";
    }

    public function checking_update() {
        if($this->get_update_checktime() == date('Ymd')){
            if (!version_compare($this->get_new_version(), $this->plugin_version, '>')) return;
        }
        $this->set_update_checktime();

        $http_args = array(
            'headers'          => array(
                'Referer'      => home_url(),
                'User-Agent'   => 'nws_class'
            ),
            'httpversion'      => '1.0',
            'timeout'          => 5
        );
        $res = wp_remote_get('http://update.iamgood.co.kr/syndi', $http_args);
        if($res['body']){
            $body = explode('||', $res['body']);
            $this->set_new_version($body[0]);
            if (version_compare($body[0], $this->plugin_version, '>')){
                $this->message($body[1]);
            }
        }
    }
    
    public function message($msg) {
        $this->Msg = $msg;
    }
    
    public function error($errorMsg, $returnErrorMsg = '', $returnErrorCode = 0) {
        $this->errorMessage = $errorMsg;
        if ($returnErrorMsg)
            $this->errorNaverMessage = $returnErrorMsg;
        if ($returnErrorCode)
            $this->errorNaverCode = $returnErrorCode;
    }

    public function get_update_checktime(){
        return date('Ymd', get_option(NWS_PREFIX . '_update_checktime'));
    }

    public function set_update_checktime(){
        update_option(NWS_PREFIX . '_update_checktime', time());
    }
    
    public function set_new_version($ver){
        update_option(NWS_PREFIX . '_update_new_version', $ver);
    }
    
    public function get_new_version(){
        return get_option(NWS_PREFIX . '_update_new_version');
    }
    
    public function post_status($post_id) {
        $status = get_post_meta($post_id, $this->metaKey, true);
        return self::$post_status[$status];
    }
    
    public function trash_ping( $post_id ){
        global $_nwsv2;
        
        if($this->options['type'] != 'yes') return;

        $meta = get_post_meta($post_id, $this->metaKey, true);
        if (!in_array($meta, array('complete'))) return;
        
        $this->action = 'delete';
        $_nwsv2['lib']->ping($post_id);
    }

    public function publish_ping( $post_id ){
        global $_nwsv2;
        
        if($this->options['type'] != 'yes') return;
        
        $meta = get_post_meta($post_id, $this->metaKey, true);
        if (!in_array($meta, array('complete', 'delete'))) return;
        
        $this->action = '';
        $_nwsv2['lib']->set_meta($post_id, 'ready');
        $_nwsv2['lib']->ping($post_id);
    }
    
    public function hooks() {
        if (is_admin()) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'register_admin_menu'));
        }
        
        add_action('trashed_post', array( $this, 'trash_ping'), 10, 1);
        add_action('publish_post', array( $this, 'publish_ping'), 10, 1);
    }

    public function admin_init() {
        register_setting(NWS_PREFIX . '-settings-group', NWS_PREFIX . '_setting_field', array($this, 'sanitize_op'));  // 옵션체크
        if (version_compare(get_bloginfo('version'), $this->require_wp_version, '<')){
            $this->error('워드프레스 버전 3.7.1 이상 필요로 합니다.');
        }
        $this->checking_update();
    }

    public function register_admin_menu() {
        global $_nwsv2;
        $page = add_options_page($this->title, $this->title, 'manage_options', 'naver-web-syndi-v2', array($this, 'admin_menu'));
        $_nwsv2['tpl']->admin_style_js_add($page);
    }
    
    public function sanitize_op($input) {
        $new_input = array();
        if (isset($input['token']))
            $new_input['token'] = sanitize_text_field($input['token']);
        
        if (isset($input['seo']))
            $new_input['seo'] = sanitize_text_field($input['seo']);

        if($input['type']!='yes') $new_input['type'] = 'no';
        else $new_input['type'] = 'yes';

        return $new_input;
    }

    public function admin_menu() {
        global $wpdb, $_nwsv2;
        include_once $_nwsv2['dir']->inc_dir() . '/message.php';
        include_once $_nwsv2['dir']->inc_dir() . '/error_message.php';
        include_once $_nwsv2['dir']->inc_dir() . '/admin_page.php';
    }

    public function print_list_title() {
        if ($this->listType == 'etc')
            return '기타';
        return strtoupper($this->listType);
    }
}
?>