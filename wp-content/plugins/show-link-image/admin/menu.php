<?php

define('FIFU_SETTINGS', serialize(array('fifu_social', 'fifu_original', 'fifu_valid', 'fifu_lazy', 'fifu_jquery', 'fifu_media_library', 'fifu_reset', 'fifu_content', 'fifu_content_page', 'fifu_fake', 'fifu_variation', 'fifu_variation_gallery', 'fifu_hover', 'fifu_css', 'fifu_key', 'fifu_error_url', 'fifu_default_url', 'fifu_enable_default_url', 'fifu_shortcode_min_width', 'fifu_shortcode_max_height', 'fifu_cron_metadata', 'fifu_spinner_cron_metadata', 'fifu_spinner_db', 'fifu_spinner_image', 'fifu_spinner_video', 'fifu_spinner_slider', 'fifu_video_min_width', 'fifu_video_height_shop', 'fifu_video_width_shop', 'fifu_video_height_prod', 'fifu_video_width_prod', 'fifu_video_height_ctgr', 'fifu_video_width_ctgr', 'fifu_video_height_arch', 'fifu_video_width_arch', 'fifu_video_height_home', 'fifu_video_width_home', 'fifu_video_height_page', 'fifu_video_width_page', 'fifu_video_height_post', 'fifu_video_width_post', 'fifu_video_height_rtio', 'fifu_video_width_rtio', 'fifu_video_margin_bottom', 'fifu_video_vertical_margin', 'fifu_slider', 'fifu_slider_fade', 'fifu_slider_auto', 'fifu_slider_gallery', 'fifu_slider_ctrl', 'fifu_slider_stop', 'fifu_slider_speed', 'fifu_slider_pause', 'fifu_wc_lbox', 'fifu_wc_zoom', 'fifu_hide_page', 'fifu_hide_post', 'fifu_get_first', 'fifu_pop_first', 'fifu_ovw_first', 'fifu_update_all', 'fifu_update_ignore', 'fifu_column_height', 'fifu_mouse_youtube', 'fifu_mouse_vimeo', 'fifu_video', 'fifu_video_thumb', 'fifu_same_size', 'fifu_auto_category', 'fifu_grid_category', 'fifu_auto_alt', 'fifu_data_clean', 'fifu_shortcode', 'fifu_crop_ratio', 'fifu_crop0', 'fifu_crop1', 'fifu_crop2', 'fifu_crop3', 'fifu_crop4', 'fifu_flickr_post', 'fifu_flickr_page', 'fifu_flickr_arch', 'fifu_flickr_cart', 'fifu_flickr_ctgr', 'fifu_flickr_home', 'fifu_flickr_prod', 'fifu_flickr_shop', 'fifu_image_height_shop', 'fifu_image_width_shop', 'fifu_image_height_prod', 'fifu_image_width_prod', 'fifu_image_height_cart', 'fifu_image_width_cart', 'fifu_image_height_ctgr', 'fifu_image_width_ctgr', 'fifu_image_height_arch', 'fifu_image_width_arch', 'fifu_image_height_home', 'fifu_image_width_home', 'fifu_image_height_page', 'fifu_image_width_page', 'fifu_image_height_post', 'fifu_image_width_post', 'fifu_save_dimensions', 'fifu_save_dimensions_all', 'fifu_clean_dimensions_all')));

add_action('admin_menu', 'fifu_insert_menu');

function fifu_insert_menu() {
    if (strpos($_SERVER['REQUEST_URI'], 'show-image-link') !== false) {
        wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.min.css');
        wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.7.0/css/all.css');
        wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js');
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.11.3.min.js');
        wp_enqueue_script('jquery-block-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js');

        wp_enqueue_style('datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css');
        wp_enqueue_style('datatable-select-css', '//cdn.datatables.net/select/1.3.0/css/select.dataTables.min.css');
        wp_enqueue_script('datatable-js', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js');
        wp_enqueue_script('datatable-select', '//cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js');
        wp_enqueue_script('datatable-buttons', '//cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js');

        wp_enqueue_script('lazyload', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.1.0/jquery.lazyloadxt.min.js');
        wp_enqueue_style('lazyload-spinner', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.1.0/jquery.lazyloadxt.spinner.min.css');

        wp_enqueue_style('fifu-menu-su-css', plugins_url('/html/css/menu-su.css', __FILE__));
        wp_enqueue_script('fifu-menu-su-js', plugins_url('/html/js/menu-su.js', __FILE__));
    }

    add_menu_page('Show Image Link', 'Show Image Link', 'administrator', 'show-image-link', 'fifu_get_menu_html', plugins_url() . '/show-image-link/admin/images/favicon.png', 57);

    add_action('admin_init', 'fifu_get_menu_settings');
}

function fifu_get_menu_html() {
    flush();

    // css and js
    wp_enqueue_style('fifu-menu-css', plugins_url('/html/css/menu.css', __FILE__));
    wp_enqueue_script('fifu-menu-js', plugins_url('/html/js/menu.js', __FILE__));

    $enable_social = get_option('fifu_social');
    $enable_original = get_option('fifu_original');
    $enable_valid = get_option('fifu_valid');
    $enable_lazy = get_option('fifu_lazy');
    $enable_jquery = get_option('fifu_jquery');
    $enable_media_library = get_option('fifu_media_library');
    $enable_reset = get_option('fifu_reset');
    $enable_content = get_option('fifu_content');
    $enable_content_page = get_option('fifu_content_page');
    $enable_fake = get_option('fifu_fake');
    $enable_variation = get_option('fifu_variation');
    $enable_variation_gallery = get_option('fifu_variation_gallery');
    $hover_option = get_option('fifu_hover');
    $css_style = get_option('fifu_css');
    $license_key = get_option('fifu_key');
    $error_url = get_option('fifu_error_url');
    $default_url = get_option('fifu_default_url');
    $enable_default_url = get_option('fifu_enable_default_url');
    $shortcode_min_width = get_option('fifu_shortcode_min_width');
    $shortcode_max_height = get_option('fifu_shortcode_max_height');
    $interval_cron_metadata = get_option('fifu_spinner_cron_metadata');
    $enable_cron_metadata = get_option('fifu_cron_metadata');
    $max_db = get_option('fifu_spinner_db');
    $max_image = get_option('fifu_spinner_image');
    $max_video = get_option('fifu_spinner_video');
    $max_slider = get_option('fifu_spinner_slider');
    $min_video_width = get_option('fifu_video_min_width');
    $max_video_height_shop = get_option('fifu_video_height_shop');
    $max_video_width_shop = get_option('fifu_video_width_shop');
    $max_video_height_prod = get_option('fifu_video_height_prod');
    $max_video_width_prod = get_option('fifu_video_width_prod');
    $max_video_height_ctgr = get_option('fifu_video_height_ctgr');
    $max_video_width_ctgr = get_option('fifu_video_width_ctgr');
    $max_video_height_arch = get_option('fifu_video_height_arch');
    $max_video_width_arch = get_option('fifu_video_width_arch');
    $max_video_height_home = get_option('fifu_video_height_home');
    $max_video_width_home = get_option('fifu_video_width_home');
    $max_video_height_page = get_option('fifu_video_height_page');
    $max_video_width_page = get_option('fifu_video_width_page');
    $max_video_height_post = get_option('fifu_video_height_post');
    $max_video_width_post = get_option('fifu_video_width_post');
    $ratio_video_height = get_option('fifu_video_height_rtio');
    $ratio_video_width = get_option('fifu_video_width_rtio');
    $video_margin_bottom = get_option('fifu_video_margin_bottom');
    $video_vertical_margin = get_option('fifu_video_vertical_margin');
    $enable_slider = get_option('fifu_slider');
    $enable_slider_fade = get_option('fifu_slider_fade');
    $enable_slider_auto = get_option('fifu_slider_auto');
    $enable_slider_gallery = get_option('fifu_slider_gallery');
    $enable_slider_ctrl = get_option('fifu_slider_ctrl');
    $enable_slider_stop = get_option('fifu_slider_stop');
    $slider_speed = get_option('fifu_slider_speed');
    $slider_pause = get_option('fifu_slider_pause');
    $enable_wc_lbox = get_option('fifu_wc_lbox');
    $enable_wc_zoom = get_option('fifu_wc_zoom');
    $enable_hide_page = get_option('fifu_hide_page');
    $enable_hide_post = get_option('fifu_hide_post');
    $enable_get_first = get_option('fifu_get_first');
    $enable_pop_first = get_option('fifu_pop_first');
    $enable_ovw_first = get_option('fifu_ovw_first');
    $enable_update_all = 'toggleoff';
    $enable_update_ignore = get_option('fifu_update_ignore');
    $column_height = get_option('fifu_column_height');
    $enable_mouse_youtube = get_option('fifu_mouse_youtube');
    $enable_mouse_vimeo = get_option('fifu_mouse_vimeo');
    $enable_video = get_option('fifu_video');
    $enable_video_thumb = get_option('fifu_video_thumb');
    $enable_same_size = get_option('fifu_same_size');
    $enable_auto_category = get_option('fifu_auto_category');
    $enable_grid_category = get_option('fifu_grid_category');
    $enable_auto_alt = get_option('fifu_auto_alt');
    $enable_data_clean = 'toggleoff';
    $enable_shortcode = get_option('fifu_shortcode');
    $crop_ratio = get_option('fifu_crop_ratio');
    $flickr_post = get_option('fifu_flickr_post');
    $flickr_page = get_option('fifu_flickr_page');
    $flickr_arch = get_option('fifu_flickr_arch');
    $flickr_cart = get_option('fifu_flickr_cart');
    $flickr_ctgr = get_option('fifu_flickr_ctgr');
    $flickr_home = get_option('fifu_flickr_home');
    $flickr_shop = get_option('fifu_flickr_shop');
    $flickr_prod = get_option('fifu_flickr_prod');
    $max_image_height_shop = get_option('fifu_image_height_shop');
    $max_image_width_shop = get_option('fifu_image_width_shop');
    $max_image_height_prod = get_option('fifu_image_height_prod');
    $max_image_width_prod = get_option('fifu_image_width_prod');
    $max_image_height_cart = get_option('fifu_image_height_cart');
    $max_image_width_cart = get_option('fifu_image_width_cart');
    $max_image_height_ctgr = get_option('fifu_image_height_ctgr');
    $max_image_width_ctgr = get_option('fifu_image_width_ctgr');
    $max_image_height_arch = get_option('fifu_image_height_arch');
    $max_image_width_arch = get_option('fifu_image_width_arch');
    $max_image_height_home = get_option('fifu_image_height_home');
    $max_image_width_home = get_option('fifu_image_width_home');
    $max_image_height_page = get_option('fifu_image_height_page');
    $max_image_width_page = get_option('fifu_image_width_page');
    $max_image_height_post = get_option('fifu_image_height_post');
    $max_image_width_post = get_option('fifu_image_width_post');
    $enable_save_dimensions = get_option('fifu_save_dimensions');
    $enable_save_dimensions_all = 'toggleoff';
    $enable_clean_dimensions_all = 'toggleoff';

    $array_crop = array();
    for ($x = 0; $x <= 4; $x ++)
        $array_crop[$x] = get_option('fifu_crop' . $x);

    include 'html/menu.html';

    fifu_update_menu_options();

    // category
    if (fifu_is_on('fifu_auto_category')) {
        if (!get_option('fifu_auto_category_created')) {
            fifu_db_insert_auto_category_image();
            update_option('fifu_auto_category_created', true, 'no');
        }
    } else
        update_option('fifu_auto_category_created', false, 'no');

    // default
    if (!empty($default_url) && fifu_is_on('fifu_enable_default_url') && fifu_is_on('fifu_fake')) {
        if (!wp_get_attachment_url(get_option('fifu_default_attach_id'))) {
            $att_id = fifu_db_create_attachment($default_url);
            update_option('fifu_default_attach_id', $att_id);
            fifu_db_set_default_url();
        } else
            fifu_db_update_default_url($default_url);
    } else
        fifu_db_delete_default_url();

    // reset
    if (fifu_is_on('fifu_reset')) {
        fifu_reset_settings();
        update_option('fifu_reset', 'toggleoff', 'no');
    }

    // schedule
    if (fifu_is_on('fifu_cron_metadata')) {
        if (!wp_next_scheduled('fifu_create_metadata_event'))
            wp_schedule_event(time(), 'fifu_schedule_metadata', 'fifu_create_metadata_event');
    } else
        wp_clear_scheduled_hook('fifu_create_metadata_event');
}

function fifu_get_menu_settings() {
    foreach (unserialize(FIFU_SETTINGS) as $i)
        fifu_get_setting($i);
}

function fifu_reset_settings() {
    foreach (unserialize(FIFU_SETTINGS) as $i)
        delete_option($i);
}

function fifu_get_setting($type) {
    register_setting('settings-group', $type);

    $arrFlickr = array('fifu_flickr_post', 'fifu_flickr_page', 'fifu_flickr_arch', 'fifu_flickr_cart', 'fifu_flickr_ctgr', 'fifu_flickr_home', 'fifu_flickr_prod', 'fifu_flickr_shop');
    $arrRatio = array('fifu_crop_ratio');
    $arr100 = array('fifu_spinner_db');
    $arr325 = array('fifu_video_min_width');
    $arr1 = array('fifu_spinner_cron_metadata');
    $arr3 = array('fifu_spinner_image', 'fifu_spinner_video', 'fifu_spinner_slider');
    $arr9 = array('fifu_video_height_rtio');
    $arr16 = array('fifu_video_width_rtio');
    $arr22 = array('fifu_video_margin_bottom');
    $arrEmpty = array('fifu_default_url', 'fifu_shortcode_max_height', 'fifu_shortcode_min_width', 'fifu_video_vertical_margin', 'fifu_video_width_rtio', 'fifu_video_height_rtio', 'fifu_video_width_post', 'fifu_video_height_post', 'fifu_video_width_page', 'fifu_video_height_page', 'fifu_video_width_home', 'fifu_video_height_home', 'fifu_video_width_arch', 'fifu_video_height_arch', 'fifu_video_width_ctgr', 'fifu_video_height_ctgr', 'fifu_video_width_prod', 'fifu_video_height_prod', 'fifu_video_width_shop', 'fifu_video_height_shop', 'fifu_css', 'fifu_hover', 'fifu_crop4', 'fifu_crop3', 'fifu_crop2', 'fifu_crop1', 'fifu_crop0');
    $arrEmptyNo = array('fifu_error_url', 'fifu_key', 'fifu_image_height_shop', 'fifu_image_width_shop', 'fifu_image_height_prod', 'fifu_image_width_prod', 'fifu_image_height_cart', 'fifu_image_width_cart', 'fifu_image_height_ctgr', 'fifu_image_width_ctgr', 'fifu_image_height_arch', 'fifu_image_width_arch', 'fifu_image_height_home', 'fifu_image_width_home', 'fifu_image_height_page', 'fifu_image_width_page', 'fifu_image_height_post', 'fifu_image_width_post');
    $arr64 = array('fifu_column_height');
    $arr1000 = array('fifu_slider_speed');
    $arr2000 = array('fifu_slider_pause');
    $arrOn = array('fifu_auto_alt', 'fifu_wc_zoom', 'fifu_wc_lbox');
    $arrOnNo = array('fifu_fake');
    $arrOffNo = array('fifu_auto_category_created', 'fifu_data_clean', 'fifu_update_all', 'fifu_update_ignore', 'fifu_reset', 'fifu_enable_cron_metadata');

    if (!get_option($type)) {
        if (in_array($type, $arrFlickr))
            update_option($type, '75,100,150,240,320,500,640,800', 'no');
        else if (in_array($type, $arrRatio))
            update_option($type, '4:3', 'no');
        else if (in_array($type, $arr100))
            update_option($type, 100, 'no');
        else if (in_array($type, $arr325))
            update_option($type, 325, 'no');
        else if (in_array($type, $arr1))
            update_option($type, 1);
        else if (in_array($type, $arr3))
            update_option($type, 3);
        else if (in_array($type, $arr9))
            update_option($type, 9);
        else if (in_array($type, $arr16))
            update_option($type, 16);
        else if (in_array($type, $arr22))
            update_option($type, '22px', 'no');
        else if (in_array($type, $arrEmpty))
            update_option($type, '');
        else if (in_array($type, $arrEmptyNo))
            update_option($type, '', 'no');
        else if (in_array($type, $arr64))
            update_option($type, "64", 'no');
        else if (in_array($type, $arr1000))
            update_option($type, 1000);
        else if (in_array($type, $arr2000))
            update_option($type, 2000);
        else if (in_array($type, $arrOn))
            update_option($type, 'toggleon');
        else if (in_array($type, $arrOnNo))
            update_option($type, 'toggleon', 'no');
        else if (in_array($type, $arrOffNo))
            update_option($type, 'toggleoff', 'no');
        else
            update_option($type, 'toggleoff');
    }
}

function fifu_update_menu_options() {
    fifu_update_option('fifu_input_social', 'fifu_social');
    fifu_update_option('fifu_input_original', 'fifu_original');
    fifu_update_option('fifu_input_valid', 'fifu_valid');
    fifu_update_option('fifu_input_lazy', 'fifu_lazy');
    fifu_update_option('fifu_input_jquery', 'fifu_jquery');
    fifu_update_option('fifu_input_media_library', 'fifu_media_library');
    fifu_update_option('fifu_input_reset', 'fifu_reset');
    fifu_update_option('fifu_input_content', 'fifu_content');
    fifu_update_option('fifu_input_content_page', 'fifu_content_page');
    fifu_update_option('fifu_input_fake', 'fifu_fake');
    fifu_update_option('fifu_input_variation', 'fifu_variation');
    fifu_update_option('fifu_input_variation_gallery', 'fifu_variation_gallery');
    fifu_update_option('fifu_input_hover', 'fifu_hover');
    fifu_update_option('fifu_input_css', 'fifu_css');
    fifu_update_option('fifu_input_key', 'fifu_key');
    fifu_update_option('fifu_input_error_url', 'fifu_error_url');
    fifu_update_option('fifu_input_default_url', 'fifu_default_url');
    fifu_update_option('fifu_input_enable_default_url', 'fifu_enable_default_url');
    fifu_update_option('fifu_input_shortcode_min_width', 'fifu_shortcode_min_width');
    fifu_update_option('fifu_input_shortcode_max_height', 'fifu_shortcode_max_height');
    fifu_update_option('fifu_input_cron_metadata', 'fifu_cron_metadata');
    fifu_update_option('fifu_input_spinner_cron_metadata', 'fifu_spinner_cron_metadata');
    fifu_update_option('fifu_input_spinner_db', 'fifu_spinner_db');
    fifu_update_option('fifu_input_spinner_image', 'fifu_spinner_image');
    fifu_update_option('fifu_input_spinner_video', 'fifu_spinner_video');
    fifu_update_option('fifu_input_spinner_slider', 'fifu_spinner_slider');
    fifu_update_option('fifu_input_video_min_width', 'fifu_video_min_width');
    fifu_update_option('fifu_input_video_height_shop', 'fifu_video_height_shop');
    fifu_update_option('fifu_input_video_width_shop', 'fifu_video_width_shop');
    fifu_update_option('fifu_input_video_height_prod', 'fifu_video_height_prod');
    fifu_update_option('fifu_input_video_width_prod', 'fifu_video_width_prod');
    fifu_update_option('fifu_input_video_height_ctgr', 'fifu_video_height_ctgr');
    fifu_update_option('fifu_input_video_width_ctgr', 'fifu_video_width_ctgr');
    fifu_update_option('fifu_input_video_height_arch', 'fifu_video_height_arch');
    fifu_update_option('fifu_input_video_width_arch', 'fifu_video_width_arch');
    fifu_update_option('fifu_input_video_height_home', 'fifu_video_height_home');
    fifu_update_option('fifu_input_video_width_home', 'fifu_video_width_home');
    fifu_update_option('fifu_input_video_height_page', 'fifu_video_height_page');
    fifu_update_option('fifu_input_video_width_page', 'fifu_video_width_page');
    fifu_update_option('fifu_input_video_height_post', 'fifu_video_height_post');
    fifu_update_option('fifu_input_video_width_post', 'fifu_video_width_post');
    fifu_update_option('fifu_input_video_height_rtio', 'fifu_video_height_rtio');
    fifu_update_option('fifu_input_video_width_rtio', 'fifu_video_width_rtio');
    fifu_update_option('fifu_input_video_margin_bottom', 'fifu_video_margin_bottom');
    fifu_update_option('fifu_input_video_vertical_margin', 'fifu_video_vertical_margin');
    fifu_update_option('fifu_input_slider', 'fifu_slider');
    fifu_update_option('fifu_input_slider_fade', 'fifu_slider_fade');
    fifu_update_option('fifu_input_slider_auto', 'fifu_slider_auto');
    fifu_update_option('fifu_input_slider_gallery', 'fifu_slider_gallery');
    fifu_update_option('fifu_input_slider_ctrl', 'fifu_slider_ctrl');
    fifu_update_option('fifu_input_slider_stop', 'fifu_slider_stop');
    fifu_update_option('fifu_input_slider_speed', 'fifu_slider_speed');
    fifu_update_option('fifu_input_slider_pause', 'fifu_slider_pause');
    fifu_update_option('fifu_input_wc_lbox', 'fifu_wc_lbox');
    fifu_update_option('fifu_input_wc_zoom', 'fifu_wc_zoom');
    fifu_update_option('fifu_input_hide_page', 'fifu_hide_page');
    fifu_update_option('fifu_input_hide_post', 'fifu_hide_post');
    fifu_update_option('fifu_input_get_first', 'fifu_get_first');
    fifu_update_option('fifu_input_pop_first', 'fifu_pop_first');
    fifu_update_option('fifu_input_ovw_first', 'fifu_ovw_first');
    fifu_update_option('fifu_input_update_all', 'fifu_update_all');
    fifu_update_option('fifu_input_update_ignore', 'fifu_update_ignore');
    fifu_update_option('fifu_input_column_height', 'fifu_column_height');
    fifu_update_option('fifu_input_mouse_youtube', 'fifu_mouse_youtube');
    fifu_update_option('fifu_input_mouse_vimeo', 'fifu_mouse_vimeo');
    fifu_update_option('fifu_input_video', 'fifu_video');
    fifu_update_option('fifu_input_video_thumb', 'fifu_video_thumb');
    fifu_update_option('fifu_input_same_size', 'fifu_same_size');
    fifu_update_option('fifu_input_auto_category', 'fifu_auto_category');
    fifu_update_option('fifu_input_grid_category', 'fifu_grid_category');
    fifu_update_option('fifu_input_auto_alt', 'fifu_auto_alt');
    fifu_update_option('fifu_input_data_clean', 'fifu_data_clean');
    fifu_update_option('fifu_input_shortcode', 'fifu_shortcode');
    fifu_update_option('fifu_input_crop_ratio', 'fifu_crop_ratio');
    fifu_update_option('fifu_input_flickr_post', 'fifu_flickr_post');
    fifu_update_option('fifu_input_flickr_page', 'fifu_flickr_page');
    fifu_update_option('fifu_input_flickr_arch', 'fifu_flickr_arch');
    fifu_update_option('fifu_input_flickr_cart', 'fifu_flickr_cart');
    fifu_update_option('fifu_input_flickr_ctgr', 'fifu_flickr_ctgr');
    fifu_update_option('fifu_input_flickr_home', 'fifu_flickr_home');
    fifu_update_option('fifu_input_flickr_prod', 'fifu_flickr_prod');
    fifu_update_option('fifu_input_flickr_shop', 'fifu_flickr_shop');
    fifu_update_option('fifu_input_image_height_shop', 'fifu_image_height_shop');
    fifu_update_option('fifu_input_image_width_shop', 'fifu_image_width_shop');
    fifu_update_option('fifu_input_image_height_prod', 'fifu_image_height_prod');
    fifu_update_option('fifu_input_image_width_prod', 'fifu_image_width_prod');
    fifu_update_option('fifu_input_image_height_cart', 'fifu_image_height_cart');
    fifu_update_option('fifu_input_image_width_cart', 'fifu_image_width_cart');
    fifu_update_option('fifu_input_image_height_ctgr', 'fifu_image_height_ctgr');
    fifu_update_option('fifu_input_image_width_ctgr', 'fifu_image_width_ctgr');
    fifu_update_option('fifu_input_image_height_arch', 'fifu_image_height_arch');
    fifu_update_option('fifu_input_image_width_arch', 'fifu_image_width_arch');
    fifu_update_option('fifu_input_image_height_home', 'fifu_image_height_home');
    fifu_update_option('fifu_input_image_width_home', 'fifu_image_width_home');
    fifu_update_option('fifu_input_image_height_page', 'fifu_image_height_page');
    fifu_update_option('fifu_input_image_width_page', 'fifu_image_width_page');
    fifu_update_option('fifu_input_image_height_post', 'fifu_image_height_post');
    fifu_update_option('fifu_input_image_width_post', 'fifu_image_width_post');
    fifu_update_option('fifu_input_save_dimensions', 'fifu_save_dimensions');
    fifu_update_option('fifu_input_save_dimensions_all', 'fifu_save_dimensions_all');
    fifu_update_option('fifu_input_clean_dimensions_all', 'fifu_clean_dimensions_all');

    for ($x = 0; $x <= 4; $x ++)
        fifu_update_option('fifu_input_crop' . $x, 'fifu_crop' . $x);
}

function fifu_update_option($input, $type) {
    if (isset($_POST[$input])) {
        if ($_POST[$input] == 'on')
            update_option($type, 'toggleon');
        else if ($_POST[$input] == 'off')
            update_option($type, 'toggleoff');
        else
            update_option($type, wp_strip_all_tags($_POST[$input]));
    }
}

function fifu_enable_fake() {
    if (get_option('fifu_fake_created') && get_option('fifu_fake_created') != null)
        return;
    update_option('fifu_fake_created', true, 'no');

    fifu_db_insert_attachment();
    fifu_db_insert_attachment_gallery();
    fifu_db_insert_attachment_category();
}

function fifu_disable_fake() {
    if (!get_option('fifu_fake_created') && get_option('fifu_fake_created') != null)
        return;
    update_option('fifu_fake_created', false, 'no');

    fifu_db_delete_attachment();
    fifu_db_delete_attachment_category();
}

function fifu_version() {
    $plugin_data = get_plugin_data(FIFU_PLUGIN_DIR . 'vudon-link-image-premium.php');
    return $plugin_data ? $plugin_data['Name'] . ':' . $plugin_data['Version'] : '';
}

function fifu_su_sign_up_complete() {
    return get_option('fifu_su_privkey') ? true : false;
}

function fifu_su_get_email() {
    return base64_decode(get_option('fifu_su_email'));
}

function fifu_get_plugins_list() {
    $list = '';
    foreach (get_plugins() as $key => $domain) {
        $name = $domain['Name'] . ' (' . $domain['TextDomain'] . ')';
        $list .= '&#10; - ' . $name;
    }
    return $list;
}

function fifu_get_active_plugins_list() {
    $list = '';
    foreach (get_option('active_plugins') as $key) {
        $name = explode('/', $key)[0];
        $list .= '&#10; - ' . $name;
    }
    return $list;
}

function fifu_has_curl() {
    return function_exists('curl_version');
}

