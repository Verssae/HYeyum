<?php

/**
 * Settings Sections
 *
 * @since 0.1
 * @return array
 */
function hanaboard_settings_sections($page)
{
    $sections = array(
        'hanaboard_labels' => array(
            array(
                'id' => 'hanaboard_labels',
                'title' => __('Labels', HANA_BOARD_TEXT_DOMAIN)
            )
        ),
        'hanaboard_tax_options' => apply_filters('hanaboard_tax_options_sections', array(
            array(
                'id' => 'general',
                'title' => __('General', HANA_BOARD_TEXT_DOMAIN)
            ),
            array(
                'id' => 'skin',
                'title' => __('Skin', HANA_BOARD_TEXT_DOMAIN)
            ),
            array(
                'id' => 'permission',
                'title' => __('Permission', HANA_BOARD_TEXT_DOMAIN)
            ),
            array(
                'id' => 'list',
                'title' => __('List', HANA_BOARD_TEXT_DOMAIN)
            ),
            array(
                'id' => 'write',
                'title' => __('Write', HANA_BOARD_TEXT_DOMAIN)
            ),
            array(
                'id' => 'view',
                'title' => __('View', HANA_BOARD_TEXT_DOMAIN)
            ),
            array(
                'id' => 'attachment',
                'title' => __('Attachment', HANA_BOARD_TEXT_DOMAIN)
            ),
            /*
            array(
                'id' => 'sub_cat',
                'title' => __('Sub Categories', HANA_BOARD_TEXT_DOMAIN)
            )
            */
        )),
        'hanaboard_common_settings' => array(
            array(
                'id' => 'hanaboard_common_settings',
                'title' => __('Common Settings', HANA_BOARD_TEXT_DOMAIN)
            )
        )
    );
    if ($page)
        return $sections [$page];
    else
        return apply_filters('hanaboard_settings_sections', $sections);
}

function hanaboard_num_array($min, $max)
{
    $arr = array();
    for ($i = 1; $i <= 40; $i++) {
        $arr [$i] = $i;
    }
    return $arr;
}

function hanaboard_thumbnail_sizes()
{
    $return = array();
    foreach (get_intermediate_image_sizes() as $v) {
        $return [$v] = $v;
    }
    return $return;
}

function hanaboard_settings_fields($page)
{
    if (!$page)
        return;

    $users = hanaboard_list_users();
    $pages = hanaboard_get_pages();

    // For 'Parent' Field
    $terms = get_terms(HANA_BOARD_TAXONOMY, array(
        'hide_empty' => 0
    ));
    $all_terms = array(
        0 => __('Root Category', HANA_BOARD_TEXT_DOMAIN)
    );
    foreach ($terms as $term) {
        $all_terms [$term->term_id] = $term->name;
    }
    $parent_options = $all_terms;
    if (isset($_GET ['tag_ID'])) {
        unset($parent_options [$_GET ['tag_ID']]);
        $current_term = get_term_by('id', $_GET ['tag_ID'], HANA_BOARD_TAXONOMY);
        $parent_default = $current_term->parent;
    }
    $get_pages_args = array(
        'sort_order' => 'ASC',
        'sort_column' => 'post_title',
        'hierarchical' => 1,
        'exclude' => '',
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'authors' => '',
        'child_of' => 0,
        'parent' => -1,
        'exclude_tree' => '',
        'number' => '',
        'offset' => 0,
        'post_type' => 'page',
        'post_status' => 'publish'
    );
    $get_pages = get_pages($get_pages_args);
    $pages = array();
    $pages [''] = __('Create a new page and connect automatically', HANA_BOARD_TEXT_DOMAIN);

    foreach ($get_pages as $a_page) {
        $pages [$a_page->ID] = $a_page->post_title;
    }
    $settings_fields = array();
    $settings_fields ['hanaboard_tax_options'] = apply_filters('hanaboard_options_tax_settings', array(
        'general' => array(
            array(
                'name' => 'name',
                'id' => 'name',
                'label' => __('Name', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('The name is how it appears on your site.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'required' => 'required'
            ),
            array(
                'name' => 'slug',
                'id' => 'slug',
                'label' => __('Slug', HANA_BOARD_TEXT_DOMAIN),
                'desc' => '',
                'type' => 'text',
                'default' => ''
            ),
            array(
                'name' => 'parent',
                'id' => 'parent',
                'label' => __('Parent', HANA_BOARD_TEXT_DOMAIN),
                'desc' => '',
                'type' => 'select',
                'default' => '',
                'options' => $parent_options
            ),
            array(
                'name' => 'description',
                'id' => 'description',
                'label' => __('Description', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('The description is not prominent by default; however, some themes may show it.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'textarea',
                'default' => '',
                'rows' => 3
            ),
            array(
                'name' => 'connect_page',
                'id' => 'connect_page',
                'title' => __('Connect a Page', HANA_BOARD_TEXT_DOMAIN),
                'label' => __('Connect a Page', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('The page must be selected. The board shortcode will be added to the content automatically.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'options' => $pages
            ),
            array(
                'name' => 'show_date',
                'label' => __('Show published date', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show / Hide published date.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'show_like',
                'label' => __('Show number of Likes', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Allow \'Like\' on board article.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'show_dislike',
                'label' => __('Show number of Dislikes', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Please use \'Show number of Likes\' together.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'show_social_share',
                'label' => __('Show Social Shares', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Allow Social Share on view page.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'allow_tags',
                'label' => __('Allow post tags', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Users will be able to add post tags', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'email_on_new_post',
                'label' => __('Email notification(Post)', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Send an email notification to administrator on new post', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'offer_to_developer',
                'label' => __('Offer to developer', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('I will display \'Powered by Hana Board\' at the bottom of the board.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'css_head',
                'label' => __('Load css before content', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Loading css before board contents.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            )
        ),
        'permission' => array(
            array(
                'name' => 'publish_' . HANA_BOARD_POST_TYPE,
                'label' => __('Write Post', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Set write permission', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'subscriber',
                'options' => array(
                    'everyone' => __('Everyone', HANA_BOARD_TEXT_DOMAIN),
                    'subscriber' => __('Subscriber', HANA_BOARD_TEXT_DOMAIN),
                    'board_admin' => __('Board admin', HANA_BOARD_TEXT_DOMAIN),
                    'administrator' => __('Administrator', HANA_BOARD_TEXT_DOMAIN)
                )
            ),
            array(
                'name' => 'read_' . HANA_BOARD_POST_TYPE,
                'label' => __('Read Post', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Set read permission', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'everyone',
                'options' => array(
                    'everyone' => __('Everyone', HANA_BOARD_TEXT_DOMAIN),
                    'subscriber' => __('Subscriber', HANA_BOARD_TEXT_DOMAIN),
                    'board_admin' => __('Board admin', HANA_BOARD_TEXT_DOMAIN),
                    'administrator' => __('Administrator', HANA_BOARD_TEXT_DOMAIN)
                )
            ),
            array(
                'name' => 'list_' . HANA_BOARD_POST_TYPE,
                'label' => __('Access List', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Set list access permission', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'everyone',
                'options' => array(
                    'everyone' => __('Everyone', HANA_BOARD_TEXT_DOMAIN),
                    'subscriber' => __('Subscriber', HANA_BOARD_TEXT_DOMAIN),
                    'board_admin' => __('Board admin', HANA_BOARD_TEXT_DOMAIN),
                    'administrator' => __('Administrator', HANA_BOARD_TEXT_DOMAIN)
                )
            ),
            array(
                'name' => 'moderate_comments_' . HANA_BOARD_POST_TYPE,
                'label' => __('Write comment', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Set write comment permission', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'subscriber',
                'options' => array(
                    'everyone' => __('Everyone', HANA_BOARD_TEXT_DOMAIN),
                    'subscriber' => __('Subscriber', HANA_BOARD_TEXT_DOMAIN),
                    'board_admin' => __('Board admin', HANA_BOARD_TEXT_DOMAIN),
                    'administrator' => __('Administrator', HANA_BOARD_TEXT_DOMAIN)
                )
            ),
            array(
                'name' => 'write_reply_' . HANA_BOARD_POST_TYPE,
                'label' => __('Write reply', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Set write reply permission. The reply is being child post.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'subscriber',
                'options' => array(
                    'everyone' => __('Everyone', HANA_BOARD_TEXT_DOMAIN),
                    'subscriber' => __('Subscriber', HANA_BOARD_TEXT_DOMAIN),
                    'board_admin' => __('Board admin', HANA_BOARD_TEXT_DOMAIN),
                    'administrator' => __('Administrator', HANA_BOARD_TEXT_DOMAIN)
                )
            ),
            array(
                'name' => 'allow_replies',
                'label' => __('Allow replies', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Users will be able to add replies.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'allow_comments',
                'label' => __('Allow comments', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Users will be able to comment to posts.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'board_admin_users',
                'label' => __('Board admin', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Board admin can manage this board. Fill login ID(e.g. admin). Separate by comma(,).', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => ''
            ),
            array(
                'name' => 'show_write_button_with_permission',
                'label' => __('Display write button', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Display write button to user who is permitted.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => ''
            )

        ),
        'skin' => array(
            array(
                'section' => 'skin',
                'name' => 'board_skin',
                'label' => __('Board Skin', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Set the board skin ', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'skin_selector',
                'default' => 'Default',
                'options' => hanaboard_skin_list()
            ),
            array(
                'section' => 'skin',
                'name' => 'thumbnail_size',
                'label' => __('Thumbnail Size', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Select what thumbnail size will be displayed on archive list.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'hana_wide_thumb',
                'options' => hanaboard_thumbnail_sizes()
            ),
            array(
                'section' => 'skin',
                'name' => 'list_excerpt_length',
                'label' => __('Excerpt Length', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Wegine skin only. Set excerpt length. Default 120, 0 for hide.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '120'
            ),
            array(
                'section' => 'skin',
                'name' => 'list_excerpt_length_mobile',
                'label' => __('Excerpt Length(Mobile)', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Wegine skin only. Set excerpt length on mobile devices. Default 60, 0 for hide.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '60'
            ),
            array(
                'section' => 'skin',
                'name' => 'link_color',
                'label' => __('Link color', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Link color in rgb. Default: theme link color. ex) #666666', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '#666666'
            ),
            array(
                'section' => 'skin',
                'name' => 'link_hover_color',
                'label' => __('Link hover color', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Link hover color and visited color in rgb. Default: Default: theme link hover color.  ex) #333333', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '#333333'
            ),
        ),
        'view' => array(
            array(
                'section' => 'view',
                'name' => 'show_list_on_view',
                'label' => __('Show List on View', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show list on post view page.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'section' => 'view',
                'name' => 'show_author_profile',
                'label' => __('Show Author Profile', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show Author Profile Box after content.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'section' => 'view',
                'name' => 'readcount_update_interval',
                'label' => __('Read Count Update Interval', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Readcount will be updated after user read again after seconds:', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => 120
            )
        ),

        'list' => array(
            array(
                'section' => 'list',
                'name' => 'posts_per_page',
                'label' => __('Posts per page', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Select the number of posts to show on archive page.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 12,
                'options' => hanaboard_num_array(1, 40)
            ),
            array(
                'section' => 'list',
                'name' => 'show_post_no',
                'label' => __('Show Post Number', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show post number on board list and post view.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'section' => 'list',
                'name' => 'show_readcount',
                'label' => __('Show Read Count', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show read count on board list and post view.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'section' => 'list',
                'name' => 'title_display_length',
                'label' => __('Title display length', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('The post title is cut with the length on archive list page. Put <b>999</b> for unlimit.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => 999
            ),
            array(
                'section' => 'list',
                'name' => 'title_ellipsis',
                'label' => __('Title Ellipsis', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show post title in a line with `...`.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'section' => 'list',
                'name' => 'new_item',
                'label' => __('New Item', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show \'New\' on archive list. \'New\' is displayed for ..seconds after published article. Put <b>0</b> for hide.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '3600'
            ),
            array(
                'section' => 'list',
                'name' => 'popular_item',
                'label' => __('Hot Item', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Show \'Hot\' on archive list. \'Hot\' is displayed from .. read count. Put <b>0</b> for hide.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '500'
            )
        ),

        'write' => array(
            array(
                'section' => 'write',
                'name' => 'editor_type',
                'label' => __('Content editor type', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'rich',
                'options' => array(
                    'rich' => __('Rich Text', HANA_BOARD_TEXT_DOMAIN),
                    'plain' => __('Plain Text', HANA_BOARD_TEXT_DOMAIN)
                )
            ),
            array(
                'section' => 'write',
                'name' => 'allow_notice',
                'label' => __('Allow notice', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'section' => 'write',
                'name' => 'allow_secret_post',
                'label' => __('Allow secret post', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            )
        ,
            array(
                'section' => 'write',
                'name' => 'default_secret_post',
                'label' => __('Default to secret post', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'cat_selectable',
                'label' => __('User can select category', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Allow user can select or change category on writing page.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'include_cats',
                'id' => 'include_cats',
                'label' => __('Include category ID\'s', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Include categories shown on dropdown for writing page', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => null
            ),
            array(
                'name' => 'sub_category',
                'label' => __('Sub category', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Allow user can select sub category on writing page. Separate by comma.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => null
            ),
            array(
                'name' => 'update_author',
                'id' => 'update_author',
                'label' => __('Update author on updating post', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Update author to last modified user.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'name' => 'default_content',
                'id' => 'default_content',
                'label' => __('Default Content on Writing', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Default content on writing post.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'textarea',
                'default' => ''
            ),
            array(
                'name' => 'captcha_for_guest',
                'id' => 'captcha_for_guest',
                'label' => __('Image Captcha for guest', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Use image captcha for guest writer.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
        ),
        'attachment' => array(
            array(
                'section' => 'attachment',
                'name' => 'allow_upload_media',
                'label' => __('Allow media upload', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Users can upload images on posting.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'section' => 'attachment',
                'name' => 'allow_attachment',
                'label' => __('Allow attachments', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Will the users be able to add attachments on posting?', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => null
            ),
            array(
                'section' => 'attachment',
                'name' => 'block_file_extensions',
                'label' => __('Block file extensions', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('When users upload files, these file extensions will be blocked.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => 'php;html;htm;exe;com;bat;vbs;bin;'
            ),
            array(
                'section' => 'attachment',
                'name' => 'attachment_num',
                'label' => __('Number of attachments', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('How many attachments can be attached on a post. Put <b>0</b> for unlimited attachment', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '0'
            ),
            array(
                'section' => 'attachment',
                'name' => 'attachment_max_size',
                'label' => __('Attachment max size', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Enter the maximum file size in <b>KILOBYTE</b> that is allowed to attach', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => '8192'
            )
        ),
        /*
        'sub_cat' => array(
            array(
                'section' => 'sub_cat',
                'name' => 'sub_categories',
                'id' => 'sub_categories',
                'label' => __('Sub categories', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Add sub categories.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'text',
                'default' => ''
            ),
        )
        */
    ));
    $settings_fields ['hanaboard_common_settings'] = apply_filters('hanaboard_common_settings', array(
        'board' => array(
        ),
        'extensions' => array(
            array(
                'section' => 'extensions',
                'name' => 'hana_comments',
                'label' => __('Hana Comments', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Check if you want to use Hana comments instead of using Wordpress default comment system.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'on',
            ),
            array(
                'section' => 'extensions',
                'name' => 'secure_image',
                'label' => __('Captcha', HANA_BOARD_TEXT_DOMAIN),
                'desc' => __('Choose captcha system for guest user publishing.', HANA_BOARD_TEXT_DOMAIN),
                'type' => 'select',
                'default' => 'hana_captcha',
                'options' => array(
                    'hana_captcha' => __('Hana Board embeded captcha', HANA_BOARD_TEXT_DOMAIN),
                    'really_simple' => __('Really Simple Recaptcha', HANA_BOARD_TEXT_DOMAIN)
                )
            ),
        )
    ));
    if (is_array($settings_fields [$page]))
        return $settings_fields [$page];
    else
        return apply_filters('hanaboard_settings_fields', $settings_fields);
}

?>