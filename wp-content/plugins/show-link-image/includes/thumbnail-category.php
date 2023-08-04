<?php

add_filter('woocommerce_before_main_content', 'fifu_ctgr_show_image', 30);
add_filter('woocommerce_before_main_content', 'fifu_ctgr_show_video', 30);
add_filter('woocommerce_before_main_content', 'fifu_ctgr_show_shortcode', 30);

function fifu_ctgr_show_image() {
    if (fifu_is_off('fifu_grid_category'))
        return;

    $url = fifu_ctgr_get_url();
    $alt = fifu_ctgr_get_alt();

    if ($url)
        echo fifu_get_html($url, $alt, null, null);
}

function fifu_ctgr_show_video() {
    if (fifu_is_off('fifu_grid_category'))
        return;

    $url = fifu_video_ctgr_get_url();

    if ($url)
        echo fifu_get_html(fifu_video_img_large($url), null, null, null);
}

function fifu_ctgr_show_shortcode() {
    if (fifu_is_off('fifu_grid_category'))
        return;

    $url = fifu_shortcode_ctgr();

    if ($url)
        echo do_shortcode($url);
}

add_filter('wp_head', 'fifu_ctgr_add_social_tags');
add_filter('wp_head', 'fifu_video_ctgr_add_social_tags');

function fifu_ctgr_add_social_tags() {
    $url = fifu_ctgr_get_url();
    $title = single_cat_title('', false);

    $term_id = fifu_ctgr_get_term_id();
    if ($term_id)
        $description = wp_strip_all_tags(category_description($term_id));

    if ($url && fifu_is_on('fifu_social'))
        include 'html/social.html';
}

function fifu_video_ctgr_add_social_tags() {
    if (fifu_is_off('fifu_video'))
        return;

    $url = fifu_video_ctgr_get_url();
    $title = single_cat_title('', false);

    $term_id = fifu_ctgr_get_term_id();
    if ($term_id)
        $description = wp_strip_all_tags(category_description($term_id));

    $video_id = fifu_video_id($url);
    $video_src = fifu_video_src($url);
    $video_img = fifu_video_img_large($url);
    $video_url = $url;

    if ($url) {
        include 'html/social-video.html';
    }
}

function fifu_ctgr_get_url() {
    $term_id = fifu_ctgr_get_term_id();

    $url = get_term_meta($term_id, 'fifu_image_url', true);

    if (is_from_flickr($url) && class_exists('WooCommerce') && is_product_category())
        return fifu_resize_flickr_image('c', $url);

    return get_term_meta($term_id, 'fifu_image_url', true);
}

function fifu_video_ctgr_get_url() {
    $term_id = fifu_ctgr_get_term_id();
    return get_term_meta($term_id, 'fifu_video_url', true);
}

function fifu_shortcode_ctgr() {
    $term_id = fifu_ctgr_get_term_id();
    return get_term_meta($term_id, 'fifu_shortcode', true);
}

function fifu_ctgr_get_alt() {
    $term_id = fifu_ctgr_get_term_id();
    return get_term_meta($term_id, 'fifu_image_alt', true);
}

function fifu_ctgr_get_term_id() {
    global $wp_query;
    return $wp_query->get_queried_object_id();
}

