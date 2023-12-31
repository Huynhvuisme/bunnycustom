<?php

add_action('admin_init', 'fifu_column');
add_filter('admin_head', 'fifu_admin_add_css');

function fifu_column() {
    add_filter('manage_posts_columns', 'fifu_column_head');
    add_filter('manage_pages_columns', 'fifu_column_head');
    add_filter('manage_edit-product_cat_columns', 'fifu_column_head');
    fifu_column_custom_post_type();
    add_action('manage_posts_custom_column', 'fifu_column_content', 10, 2);
    add_action('manage_pages_custom_column', 'fifu_column_content', 10, 2);
    add_action('manage_product_cat_custom_column', 'fifu_ctgr_column_content', 10, 3);
}

function fifu_admin_add_css() {
    include 'html/script.html';
}

function fifu_column_head($default) {
    $default['featured_image'] = 'FIFU';
    return $default;
}

function fifu_ctgr_column_content($internal_image, $column, $term_id) {
    $height = get_option('fifu_column_height');
    if ($column == 'featured_image') {
        $url = get_term_meta($term_id, 'fifu_shortcode', true);
        if ($url == '') {
            $url = get_term_meta($term_id, 'fifu_video_url', true);
            if ($url == '') {
                $url = get_term_meta($term_id, 'fifu_image_url', true);
                if ($url != '')
                    echo sprintf('<div style="height:%spx; width:%spx; background:url(\'%s\') no-repeat center center; background-size:cover;"/>', $height, $height * 1.5, $url);
            } else
                echo sprintf('<div style="height:%spx; width:%spx; background:url(\'%s\') no-repeat center center; background-size:cover;"/>', $height, $height * 1.5, fifu_video_img_small($url));
        } else
            echo sprintf('<div style="height:%spx; width:%spx;">%s</div>', $height, $height * 1.5, do_shortcode($url));
    } else
        echo $internal_image;
}

function fifu_column_content($column, $post_id) {
    $height = get_option('fifu_column_height');
    if ($column == 'featured_image') {
        $url = get_post_meta($post_id, 'fifu_shortcode', true);
        if ($url == '') {
            $url = get_post_meta($post_id, 'fifu_video_url', true);
            if ($url == '') {
                $url = fifu_main_image_url($post_id);
                if ($url == '') {
                    $url = wp_get_attachment_url(get_post_thumbnail_id());
                }
                echo sprintf('<div style="height:%spx; width:%spx; background:url(\'%s\') no-repeat center center; background-size:cover;"/>', $height, $height * 1.5, $url);
            } else
                echo sprintf('<div style="height:%spx; width:%spx; background:url(\'%s\') no-repeat center center; background-size:cover;"/>', $height, $height * 1.5, fifu_video_img_small($url));
        } else
            echo sprintf('<div style="height:%spx; width:%spx;">%s</div>', $height, $height * 1.5, do_shortcode($url));
    }
}

function fifu_column_custom_post_type() {
    foreach (fifu_get_post_types() as $post_type)
        add_filter('manage_edit-' . $post_type . '_columns', 'fifu_column_head');
}

