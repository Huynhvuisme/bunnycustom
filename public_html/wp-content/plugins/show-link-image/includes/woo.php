<?php

function fifu_woo_zoom() {
    return fifu_is_on('fifu_wc_zoom') ? 'inline' : 'none';
}

function fifu_woo_lbox() {
    return fifu_is_on('fifu_wc_lbox');
}

function fifu_woo_theme() {
    return file_exists(get_template_directory() . '/woocommerce');
}

add_action('woocommerce_product_thumbnails', 'fifu_add_product_variation_gallery');

function fifu_add_product_variation_gallery() {
    if (fifu_is_off('fifu_variation_gallery'))
        return;
    global $product;
    $uri = $_SERVER['REQUEST_URI'];
    $attr = explode('?', $uri);
    if (count($attr) < 2)
        return;
    $attr = $attr[1];
    $attr = explode('&', $attr);
    if (count($attr) < 2)
        return;
    $ids = fifu_db_get_variation_gallery($product->get_id(), $attr);
    $product->set_gallery_image_ids($ids);
}

