<?php

add_action('product_cat_edit_form_fields', 'fifu_ctgr_edit_box');
add_action('product_cat_add_form_fields', 'fifu_ctgr_add_box');

function fifu_ctgr_edit_box($term) {
    $margin = 'margin-top:10px;';
    $width = 'width:100%;';
    $height = 'height:200px;';
    $align = 'text-align:left;';
    $url = $alt = null;

    if (is_object($term)) {
        $url = get_term_meta($term->term_id, 'fifu_image_url', true);
        $alt = get_term_meta($term->term_id, 'fifu_image_alt', true);
    }

    if ($url) {
        $show_button = 'display:none;';
        $show_alt = $show_image = $show_link = '';
    } else {
        $show_button = '';
        $show_alt = $show_image = $show_link = 'display:none;';
    }

    include 'html/category.html';
}

function fifu_ctgr_add_box() {
    $margin = 'margin-top:10px;';
    $width = 'width:100%;';
    $height = 'height:200px;';
    $align = 'text-align:left;';

    $show_button = $url = $alt = '';
    $show_alt = $show_image = $show_link = 'display:none;';

    include 'html/category.html';
}

add_action('product_cat_edit_form_fields', 'fifu_video_ctgr_edit_box');
add_action('product_cat_add_form_fields', 'fifu_video_ctgr_add_box');

function fifu_video_ctgr_edit_box($term) {
    if (fifu_is_off('fifu_video'))
        return;

    $margin = 'margin-top:10px;';
    $width = 'width:100%;';
    $height = 'height:200px;';
    $align = 'text-align:left;';

    $url = get_term_meta($term->term_id, 'fifu_video_url', true);

    if ($url) {
        $show_button = 'display:none;';
        $show_video = $show_link = '';
    } else {
        $show_button = '';
        $show_video = $show_link = 'display:none;';
    }

    include 'html/category-video.html';
}

function fifu_video_ctgr_add_box() {
    if (fifu_is_off('fifu_video'))
        return;

    $margin = 'margin-top:10px;';
    $width = 'width:100%;';
    $height = 'height:200px;';
    $align = 'text-align:left;';

    $show_button = $url = '';
    $show_video = $show_link = 'display:none;';

    include 'html/category-video.html';
}

add_action('product_cat_edit_form_fields', 'fifu_shortcode_ctgr_edit_box');
add_action('product_cat_add_form_fields', 'fifu_shortcode_ctgr_add_box');

function fifu_shortcode_ctgr_edit_box($term) {
    if (fifu_is_off('fifu_shortcode'))
        return;

    $margin = 'margin-top:10px;';
    $width = 'width:100%;max-width:512px;';
    $height = 'height:200px;';
    $align = 'text-align:left;';

    $url = get_term_meta($term->term_id, 'fifu_shortcode', true);

    if ($url) {
        $show_button = 'display:none;';
        $show_shortcode = $show_link = '';
    } else {
        $show_button = '';
        $show_shortcode = $show_link = 'display:none;';
    }

    include 'html/category-shortcode.html';
}

function fifu_shortcode_ctgr_add_box() {
    if (fifu_is_off('fifu_shortcode'))
        return;

    $margin = 'margin-top:10px;';
    $width = 'width:100%;max-width:512px;';
    $height = 'height:200px;';
    $align = 'text-align:left;';

    $show_button = $url = '';
    $show_shortcode = $show_link = 'display:none;';

    include 'html/category-shortcode.html';
}

add_action('edited_product_cat', 'fifu_ctgr_save_properties', 10, 1);
add_action('created_product_cat', 'fifu_ctgr_save_properties', 10, 1);

function fifu_ctgr_save_properties($term_id) {
    if (isset($_POST['fifu_input_alt']))
        update_term_meta($term_id, 'fifu_image_alt', wp_strip_all_tags($_POST['fifu_input_alt']));

    if (isset($_POST['fifu_input_url'])) {
        $url = esc_url_raw($_POST['fifu_input_url']);
        update_term_meta($term_id, 'fifu_image_url', fifu_convert($url));
        if (empty($url) && fifu_is_on('fifu_auto_category'))
            fifu_db_insert_auto_category_image();
        else
            fifu_db_ctgr_update_fake_attach_id($term_id);
    }
}

add_action('edited_product_cat', 'fifu_video_ctgr_save_properties', 10, 1);
add_action('created_product_cat', 'fifu_video_ctgr_save_properties', 10, 1);

function fifu_video_ctgr_save_properties($term_id) {
    if (fifu_is_off('fifu_video'))
        return;

    if (isset($_POST['fifu_video_input_url']))
        update_term_meta($term_id, 'fifu_video_url', esc_url_raw($_POST['fifu_video_input_url']));

    fifu_db_ctgr_update_fake_attach_id($term_id);
}

add_action('edited_product_cat', 'fifu_shortcode_ctgr_save_properties', 10, 1);
add_action('created_product_cat', 'fifu_shortcode_ctgr_save_properties', 10, 1);

function fifu_shortcode_ctgr_save_properties($term_id) {
    if (fifu_is_off('fifu_shortcode'))
        return;

    if (isset($_POST['fifu_shortcode_input'])) {
        $url = preg_replace("/[\"]/", "'", $_POST['fifu_shortcode_input']);
        update_term_meta($term_id, 'fifu_shortcode', $url);
    }
}

