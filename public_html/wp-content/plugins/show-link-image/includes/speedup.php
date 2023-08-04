<?php

define('FIFU_SPEEDUP_SIZES', serialize(array(75, 100, 150, 240, 320, 500, 640, 800, 1024, 1600)));

function fifu_is_valid_speedup_size($size) {
    foreach (unserialize(FIFU_SPEEDUP_SIZES) as $i)
        if ($size == $i)
            return true;
    return false;
}

function is_from_speedup($url) {
    return strpos($url, "storage.googleapis.com/fifu") !== false;
}

function fifu_resize_speedup_image_size($size, $url) {
    if (!fifu_is_valid_speedup_size($size))
        $size = fifu_speedup_size_auto($size, 0);
    return preg_replace("/\/img[^.]*/", "/img-" . $size, $url);
}

function fifu_speedup_size_auto($width, $height) {
    $longest = $width >= $height ? $width : $height;

    if ($width == $height) {
        if ($longest <= 75)
            return '75';
        if ($longest <= 150)
            return '150';
    }

    if ($longest <= 100)
        return '100';
    if ($longest <= 240)
        return '240';

    $longest *= 0.9;

    if ($longest <= 320)
        return '320';
    if ($longest <= 500)
        return '500';
    if ($longest <= 640)
        return '640';
    if ($longest <= 800)
        return '800';
    if ($longest <= 1024)
        return '1024';
    return '1600';
}

add_filter('woocommerce_single_product_image_thumbnail_html', 'fifu_woo_speedup_replace', 10, 2);

function fifu_woo_speedup_replace($html, $post_thumbnail_id) {
    // echo 'test huhu';
    $width = fifu_get_img_width_from_html($html);
    $aux = explode(',', get_option('fifu_flickr_prod'));
    $maxSize = end($aux);
    $width = $maxSize > $width ? $width : $maxSize;

    $url = wp_get_attachment_url($post_thumbnail_id);
    if (is_from_speedup($url) && $width) {
        $size = fifu_speedup_size_auto($width, 0);
        if ($size) {
            $new_url = fifu_resize_speedup_image_size($size, $url);
            $html = str_replace($url, $new_url, $html);
        }
    }
    $url = fifu_get_data_large_from_html($html);
    if (is_from_speedup($url)) {
        $size = fifu_speedup_size_auto($width, 0);
        if ($size) {
            $new_url = fifu_resize_speedup_image_size($size, $url);
            $html = str_replace('data-large_image="' . $url, 'data-large_image="' . $new_url, $html);
        }
    }
    return $html;
}

function fifu_speedup_get_srcset($url, $maxWidth) {
    $srcset = fifu_is_on('fifu_lazy') ? 'data-srcset="' : 'srcset="';
    $count = 0;
    $arr_sizes_int = explode(',', fifu_flickr_thumbnail_sizes());
    foreach ($arr_sizes_int as $i) {
        $srcset .= (($count++ != 0) ? ', ' : '') . fifu_resize_speedup_image_size($i, $url) . ' ' . $i . 'w';
        if ($maxWidth && $i >= $maxWidth)
            break;
    }
    return $srcset . '"';
}

