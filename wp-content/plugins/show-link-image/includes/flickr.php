<?php

define('FIFU_FLICKR_SIZES', serialize(array('s', 't', 'q', 'm', 'n', '-', 'z', 'c', 'b')));

function fifu_is_valid_flickr_size($size) {
    foreach (unserialize(FIFU_FLICKR_SIZES) as $i)
        if ($size == $i)
            return true;
    return false;
}

function is_from_flickr($url) {
    return strpos($url, "staticflickr.com") !== false;
}

function fifu_resize_flickr_image($size, $url) {
    if (!fifu_is_valid_flickr_size($size))
        $size = fifu_flickr_size_auto($size, 0);

    if ($size == '-')
        $size = '';

    $pattern = '/((_[\w])+)*\.[\w]+$/';
    $extension = null;
    preg_match($pattern, $url, $aux);
    if ($aux)
        $extension = explode('.', $aux[0])[1];

    $replacement = $size . '.';

    if ($size)
        $replacement = '_' . $replacement;

    if ($extension)
        return preg_replace($pattern, $replacement, $url) . $extension;
    return preg_replace($pattern, $replacement, $url);
}

function fifu_flickr_size_auto($width, $height) {
    $longest = $width >= $height ? $width : $height;

    if ($width == $height) {
        if ($longest <= 75)
            return 's';
        if ($longest <= 150)
            return 'q';
    }

    if ($longest <= 100)
        return 't';
    if ($longest <= 240)
        return 'm';

    $longest *= 0.9;

    if ($longest <= 320)
        return 'n';
    if ($longest <= 500)
        return '-';
    if ($longest <= 640)
        return 'z';
    if ($longest <= 800)
        return 'c';
    return 'b';
}

add_filter('woocommerce_single_product_image_thumbnail_html', 'fifu_woo_flickr_replace', 10, 2);

function fifu_woo_flickr_replace($html, $post_thumbnail_id) {
    $width = fifu_get_img_width_from_html($html);
    $aux = explode(',', get_option('fifu_flickr_prod'));
    $maxSize = end($aux);
    $width = $maxSize > $width ? $width : $maxSize;

    $url = wp_get_attachment_url($post_thumbnail_id);
    if (is_from_flickr($url) && $width) {
        $size = fifu_flickr_size_auto($width, 0);
        if ($size) {
            $new_url = fifu_resize_flickr_image($size, $url);
            $html = str_replace($url, $new_url, $html);
        }
    }
    $url = fifu_get_data_large_from_html($html);
    if (is_from_flickr($url)) {
        $size = fifu_flickr_size_auto($width, 0);
        if ($size) {
            $new_url = fifu_resize_flickr_image($size, $url);
            $html = str_replace('data-large_image="' . $url, 'data-large_image="' . $new_url, $html);
        }
    }
    return $html;
}

function fifu_flickr_replace($html, $post_thumbnail_id) {
    $width = fifu_get_img_width_from_html($html);
    $url = fifu_get_src_from_html($html);
    if (is_from_flickr($url) && $width) {
        $size = fifu_flickr_size_auto($width, 0);
        if ($size) {
            $new_url = fifu_resize_flickr_image($size, $url);
            $html = str_replace($url, $new_url, $html);
        }
    }
    return $html;
}

function fifu_flickr_get_srcset($url, $maxWidth) {
    $srcset = fifu_is_on('fifu_lazy') ? 'data-srcset="' : 'srcset="';
    $count = 0;
    $arr_sizes_int = explode(',', fifu_flickr_thumbnail_sizes());
    foreach (fifu_flickr_convert_to_char($arr_sizes_int) as $i) {
        $srcset .= (($count != 0) ? ', ' : '') . fifu_resize_flickr_image($i, $url) . ' ' . $arr_sizes_int[$count] . 'w';
        if ($maxWidth && $arr_sizes_int[$count] >= $maxWidth)
            break;
        $count++;
    }
    return $srcset . '"';
}

function fifu_flickr_thumbnail_sizes() {
    if (is_home())
        return get_option('fifu_flickr_home');

    if (class_exists('WooCommerce') && is_shop())
        return get_option('fifu_flickr_shop');

    if (class_exists('WooCommerce') && is_product_category())
        return get_option('fifu_flickr_ctgr');

    if (is_singular('post') || is_author() || is_search())
        return get_option('fifu_flickr_post');

    if (is_singular('page'))
        return class_exists('WooCommerce') && is_cart() ? get_option('fifu_flickr_cart') : get_option('fifu_flickr_page');

    if (is_singular('product'))
        return get_option('fifu_flickr_prod');

    if (is_archive())
        return get_option('fifu_flickr_arch');
}

function fifu_flickr_convert_to_char($arr_sizes_int) {
    $aux = '';
    $count = 0;
    foreach ($arr_sizes_int as $i) {
        if ($count++ > 0)
            $aux .= ',';

        switch ($i) {
            case '75':
                $aux .= 's';
                break;
            case '100':
                $aux .= 't';
                break;
            case '150':
                $aux .= 'q';
                break;
            case '240':
                $aux .= 'm';
                break;
            case '320':
                $aux .= 'n';
                break;
            case '500':
                $aux .= '-';
                break;
            case '640':
                $aux .= 'z';
                break;
            case '800':
                $aux .= 'c';
                break;
            case '1024':
                $aux .= 'b';
                break;
            default:
                $aux .= 'b';
        }
    }
    return explode(',', $aux);
}

