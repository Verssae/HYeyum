<?php
$_footer_help = '<i>모든 연동은 수동으로 이루어집니다. 단 연동방식을 체크하실 경우 <b>한번 연동완료된 문서는 수정 및 삭제시 자동</b>으로 연동이 이루어집니다.</i>';
?>
<div class='wrap'>
    <h2><?= $this->title ?> 관리</h2>
    <h2 class="nav-tab-wrapper">
        <a id='syndi_setting-tab' href="#syndi_setting" class="nav-tab">기본설정</a>
        <a id='syndi_sync-tab' href="#syndi_sync" class="nav-tab">연동하기</a>
    </h2>
    <div id="syndi_setting" class="group">
        <form method="post" action="options.php">
            <?php settings_fields(NWS_PREFIX.'-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">연동키(token) 설정</th>
                    <td>
                        <input type="text" class='token' name="<?php echo NWS_PREFIX; ?>_setting_field[token]"
                               value ="<?php echo htmlspecialchars($this->options['token']) ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">연동방식</th>
                    <td class="token_input">
                        <input type="checkbox" name="<?php echo NWS_PREFIX; ?>_setting_field[type]"
                               value ="yes" <?php if($this->options['type'] != 'no') echo 'checked'; ?> /> 한번 연동한 문서는 수정/삭제시 자동으로 갱신
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">SEO 사용</th>
                    <td class="token_input">
                        <input type="checkbox" name="<?php echo NWS_PREFIX; ?>_setting_field[seo]"
                               value ="allinone" <?php if($this->options['seo'] == 'allinone') echo 'checked'; ?> /> All in One SEO 적용
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
            <hr>
            <?php echo $_footer_help; ?>
        </form>
    </div>
    <div id="syndi_sync" class="group">
        <table class="form-table">
            <colgroup>
                <col width="22%" />
                <col width="26%" />
                <col width="26%" />
                <col width="26%" />
            </colgroup>
            <thead>
                <tr>
                    <td><?php $_nwsv2['tpl']->btn_refresh(); ?></td>
                    <td>비연동</td>
                    <td>연동대기중</td>
                    <td>연동완료</td>
                </tr>
            </thead>
            <tr valign="top">
                <th scope="row">글 수 (post)</th>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=post"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('post', ''); ?></a></td>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=post&st=ready"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('post', 'ready'); ?></a></td>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=post&st=complete"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('post', 'complete'); ?></a></td>
            </tr>
            <tr valign="top">
                <th scope="row">페이지 수 (page)</th>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=page"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('page', ''); ?></a></td>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=page&st=ready"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('page', 'ready'); ?></a></td>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=page&st=complete"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('page', 'complete'); ?></a></td>
            </tr>
            <tr valign="top">
                <th scope="row">기타 문서 수</th>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=etc"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('etc', ''); ?></a></td>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=etc&st=ready"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('etc', 'ready'); ?></a></td>
                <td><a href="<?php echo admin_url("options-general.php?page=" . $_GET["page"] . "&list=etc&st=complete"); ?>"><?php echo $_nwsv2['lib']->get_post_cnt('etc', 'complete'); ?></a></td>
            </tr>
        </table>
        <hr>
        <?php $header_table = '
            <tr>
                <th class="check-column">
                    <input type="checkbox">
                </th>
                <th class="column-title">
                        <a><span>제목</span></a>
                </th>
                <th class="column-date">
                        <a><span>발행일자</span></a>
                </th>
                <th class="column-author">
                        <a><span>상태</span></a>
                </th>
            </tr>
        '; ?>
        <?php if ($this->listType): ?>
            <?php
            // Paging
            $page = absint($this->paged);
            if ( !$page ) $page = 1;
            $offset = ($page - 1) * $this->posts_per_page;
            $posts = $wpdb->get_results( $this->get_posts_args() . $wpdb->prepare(" limit %d, %d ", $offset, $this->posts_per_page ));

            $total_count = (int)$wpdb->get_var('SELECT FOUND_ROWS()');
                           
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => ceil($total_count/$this->posts_per_page),
                'current' => $this->paged
            ));
            ?>
            <form method="post">
                <h3>"<?php echo $this->print_list_title() ?>" 목록 <span> - <?php echo self::$post_status[$this->st]; ?> 문서 - </span></h3>
                <?php $_nwsv2['tpl']->table_bar($page_links, $this->st, $this->listType); ?>
                <table class="wp-list-table widefat fixed pages">
                    <thead>
                        <?php echo $header_table; ?>
                    </thead>
                    <tfoot>
                        <?php echo $header_table; ?>
                    </tfoot>
                    <tbody>
                        <?php
                        foreach($posts as $post){
                            $alternate = 'alternate' == $alternate ? '' : 'alternate';
                            ?>            
                            <tr class="<?php echo $alternate; ?>">
                                <th scope="row" class="check-column"><input type="checkbox" name="pinglist[]" value="<?php echo $post->ID; ?>"></th>
                                <td><a href="<?php echo esc_url(apply_filters('preview_post_link', set_url_scheme(add_query_arg('preview', 'true', get_permalink($post->ID))))) ?>" target="_blank"><?php
                                    if (!in_array($this->listType, array('post', 'page')))
                                            echo "<b>[ ".get_post_type($post->ID)." ]</b> ";
                                    echo get_the_title($post->ID);
                                ?></a></td>
                                <td><?php echo $_nwsv2['lib']->get_the_date($post->ID) ?></td>
                                <td><?php echo $this->post_status($post->ID) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php $_nwsv2['tpl']->table_bar($page_links, $this->st, $this->listType, 2); ?>
            </form>
        <?php else: ?>
            <?php echo $_footer_help; ?>
        <?php endif ?>
    </div>
</div>