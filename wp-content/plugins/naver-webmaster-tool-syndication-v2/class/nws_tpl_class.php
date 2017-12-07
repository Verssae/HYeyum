<?php
class nws_tpl_class {
    
    public function admin_style_js_add($page){
        add_action('admin_print_styles-' . $page, array($this, 'admin_styles'), 11);
        add_action('admin_print_scripts-' . $page, array($this, 'admin_scripts'), 11);
    }
    
    public function admin_styles() {
        global $_nwsv2;
        wp_enqueue_style(NWS_PREFIX . '-admin', $_nwsv2['dir']->css_url() . '/admin.css');
    }

    public function admin_scripts($prefix) {
        global $_nwsv2;
        wp_enqueue_script(NWS_PREFIX . '-admin', $_nwsv2['dir']->js_url() . '/common.js');
    }
    
    public function btn_refresh(){
        echo "<form method='post' action='{$_SERVER["REQUEST_URI"]}'>";
        echo "<input type='submit' value='새로고침' class='button button-primary' />";
        echo "</form>";
    }
    
    public function table_bar($page_links, $st, $list_type, $id = '') {
        ?>
        <div class="tablenav bottom">
            <div class="alignleft actions">
                <select name="action<?php echo $id; ?>">
                    <option value="-1">일괄 작업</option>
                    <option value="ping">연동(핑보내기)</option>
                    <?php if ($st == 'complete'): ?>
                        <option value="cancel">연동취소</option>
                    <?php endif; ?>
                </select>
                <?php submit_button(__('Apply'), 'action', false, false, array('id' => "doaction$id")); ?>
            </div>
            <?php if (!in_array($list_type, array('post', 'page'))): ?>
                <div class="alignleft actions">
                    <select onchange="choice_post_type('<?php echo admin_url("options-general.php?page=".$_GET["page"]."&st=".$st); ?>', this.value)">
                        <option value="etc">기타전체 (문서타입 선택)</option>
                        <?php
                        $post_types = nws_lib_class::get_post_types();
                        foreach ($post_types as $post_type) {
                            ?><option value='<?php echo $post_type; ?>' <?php if ($list_type == $post_type) echo 'selected'; ?>><?php echo $post_type; ?></option><?php
                        }
                        ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($page_links) echo '<div class="tablenav-pages">' . $page_links . '</div>'; ?>
            <br class="clear" />
        </div>
        <?php
    }
}
?>