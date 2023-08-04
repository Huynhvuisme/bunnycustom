<?php

define("FIFU_URL", "/localhost/fifu/");

add_filter('get_attached_file', 'fifu_replace_attached_file', 10, 2);

function fifu_replace_attached_file($att_url, $att_id) {
    if ($att_url) {
        $url = explode(";", $att_url);
        if (sizeof($url) > 1)
            return strpos($url[1], fifu_get_internal_image_path()) !== false ? get_post($att_id)->guid : $url[1];
    }
    return $att_url;
}

add_filter('wp_get_attachment_url', 'fifu_replace_attachment_url', 10, 2);

function fifu_replace_attachment_url($att_url, $att_id) {
    // echo $att_id . " <-> ";
    if ($att_url) {
        if (get_post($att_id)) {
            $url = get_post($att_id)->guid;
            // echo $url;
            if ($url)
                return $url;
        }

        /*
        $url = explode(";", $att_url);
        if (sizeof($url) > 1)
            return strpos($url[1], fifu_get_internal_image_path()) !== false ? get_post($att_id)->guid : $url[1];
        else {
            // external wordpress images
            if (sizeof($url) > 0 && strpos($url[0], fifu_get_internal_image_path()) !== false) {
                if (get_post($att_id)) {
                    $url = get_post($att_id)->guid;
                    if ($url)
                        return $url;
                }
            }
        }*/
    }
    return $att_url;
}

add_filter('posts_where', 'fifu_query_attachments');

function fifu_query_attachments($where) {
    if (isset($_POST['action']) && ($_POST['action'] == 'query-attachments') && fifu_is_off('fifu_media_library')) {
        global $wpdb;
        $where .= ' AND ' . $wpdb->prefix . 'posts.post_author <> 77777 ';
    }
    return $where;
}

add_filter('posts_where', function ( $where, \WP_Query $q ) {
    if (is_admin() && $q->is_main_query() && fifu_is_off('fifu_media_library')) {
        global $wpdb;
        $where .= ' AND ' . $wpdb->prefix . 'posts.post_author <> 77777 ';
    }
    return $where;
}, 10, 2);

add_filter('wp_get_attachment_image_src', 'fifu_replace_attachment_image_src', 10, 3);

function fifu_replace_attachment_image_src($image, $att_id, $size) {
    if (fifu_is_internal_image($image))
        return $image;

    $post = get_post($att_id);

    if (fifu_should_hide())
        return null;
    $image_size = fifu_get_image_size($size);
    if (is_from_flickr($image[0]) && $image_size['width']) {
        $size = fifu_flickr_size_auto($image_size['width'], $image_size['height']);
        if ($size)
            $image[0] = fifu_resize_flickr_image($size, $image[0]);
    }
    if (fifu_is_on('fifu_original')) {
        return array(
            strpos($image[0], fifu_get_internal_image_path()) !== false ? get_post($att_id)->guid : $image[0],
            null,
            null,
            null,
        );
    }
    $dimension = $post ? get_post_meta($post, 'fifu_image_dimension') : null;
    $arrFIFU = fifu_get_width_height($dimension);
    return array(
        strpos($image[0], fifu_get_internal_image_path()) !== false ? get_post($att_id)->guid : $image[0],
        !$dimension && isset($image_size['width']) && $image_size['width'] < $arrFIFU['width'] ? $image_size['width'] : $arrFIFU['width'],
        !$dimension && isset($image_size['height']) && $image_size['height'] < $arrFIFU['height'] ? $image_size['height'] : $arrFIFU['height'],
        isset($image_size['crop']) ? $image_size['crop'] : '',
    );
}

function fifu_is_internal_image($image) {
    return $image && $image[1] > 1 && $image[2] > 1;
}

function fifu_get_internal_image_path() {
    // die('huhu');
    return $_SERVER['SERVER_NAME'] . "/wp-content/uploads/";
}

add_action('template_redirect', 'fifu_action', 10);

function fifu_action() {
    ob_start("fifu_callback");
    ob_start("fifu_callback_speedup");
    ob_start("fifu_callback_flickr");
}

function fifu_callback($buffer) {
    $imgList = array();
    preg_match_all('/<img[^>]*>/', $buffer, $imgList);
    $srcArray = array(
        "src" => "src",
        "data-src" => "data-src"
    );
    foreach ($imgList[0] as $imgItem) {
        foreach ($srcArray as $srcItem) {
            preg_match('/(' . $srcItem . ')([^\'\"]*[\'\"]){2}/', $imgItem, $src);
            if (!$src || strpos($src[0], FIFU_URL) === false)
                continue;
            $delimiter = substr($src[0], - 1);
            $url = explode($delimiter, $src[0])[1];
            $id = explode(FIFU_URL, $url)[1];
            $buffer = str_replace($imgItem, fifu_replace($imgItem, $id, null, null), $buffer);
        }
    }
    return $buffer;
}

function fifu_callback_speedup($buffer) {
    $imgList = array();
    preg_match_all('/<(div|li|img)[^>]*>/', $buffer, $imgList);
    $srcArray = array(
        "src" => "src",
        "data-src" => "data-src"
    );
    foreach ($imgList[0] as $imgItem) {
        foreach ($srcArray as $srcItem) {
            preg_match('/(' . $srcItem . ')([^\'\"]*[\'\"]){2}/', $imgItem, $src);
            if (!$src)
                continue;
            $delimiter = substr($src[0], - 1);
            $url = explode($delimiter, $src[0])[1];
            if (is_from_speedup($url)) {
                $maxWidth = fifu_get_img_width_from_html($imgItem);
                $newImgItem = str_replace(' ' . $srcItem . '=', ' ' . fifu_speedup_get_srcset($url, $maxWidth) . ' ' . $srcItem . '=', $imgItem);
                $buffer = str_replace($imgItem, $newImgItem, $buffer);
            }
        }
    }
    return $buffer;
}

function fifu_callback_flickr($buffer) {
    $imgList = array();
    preg_match_all('/<(div|li|img)[^>]*>/', $buffer, $imgList);
    $srcArray = array(
        "src" => "src",
        "data-src" => "data-src"
    );
    foreach ($imgList[0] as $imgItem) {
        foreach ($srcArray as $srcItem) {
            preg_match('/(' . $srcItem . ')([^\'\"]*[\'\"]){2}/', $imgItem, $src);
            if (!$src)
                continue;
            $delimiter = substr($src[0], - 1);
            $url = explode($delimiter, $src[0])[1];
            if (is_from_flickr($url)) {
                $maxWidth = fifu_get_img_width_from_html($imgItem);
                $newImgItem = str_replace(' ' . $srcItem . '=', ' ' . fifu_flickr_get_srcset($url, $maxWidth) . ' ' . $srcItem . '=', $imgItem);
                $buffer = str_replace($imgItem, $newImgItem, $buffer);
            }
        }
    }
    return $buffer;
}

// woocommerce data-thumb (the smallest image)

// add_filter('woocommerce_single_product_image_thumbnail_html', 'fifu_woocommerce_single_product_image_thumbnail_html', 10, 2); // comment để xử lý lỗi link ảnh có chữ img => bị đổi thành img-75

function fifu_woocommerce_single_product_image_thumbnail_html($html, $post_id) {
    return fifu_replace_urls($html);
}

function fifu_get_url($attr, $html) {
    if (strpos($html, ' ' . $attr . '="') !== false)
        return explode('"', explode($attr . '=', $html)[1])[1];
    if (strpos($html, ' ' . $attr . "='") !== false)
        return explode("'", explode($attr . '=', $html)[1])[1];
    return '';
}

function fifu_replace_urls($html) {
    // return $html;
    $attrs = array('data-thumb');
    $sizes = explode(',', get_option('fifu_flickr_prod'));
    $min = $sizes[0];
    $max = end($sizes);
    $sizes = array($min);
    for ($i = 0; $i < sizeof($attrs); $i++) {
        $url = fifu_get_url($attrs[$i], $html);
        // echo 'fifu_get_url = ' . $url;
        if (!fifu_is_video($url)) {
            $newUrl = str_replace('img.', 'img-' . $sizes[$i] . '.', $url);
            $html = str_replace(' ' . $attrs[$i] . '="' . $url . '"', ' ' . $attrs[$i] . '="' . $newUrl . '"', $html);
        }
    }
    return $html;
}

add_filter('wp_get_attachment_metadata', 'fifu_filter_wp_get_attachment_metadata', 10, 2);

function fifu_filter_wp_get_attachment_metadata($data, $post_id) {
    if (!$data || !is_array($data)) {
        $dimension = get_post_meta($post_id, 'fifu_image_dimension');
        return fifu_get_width_height($dimension);
    }
    return $data;
}

function fifu_get_width_height($dimension) {
    if ($dimension && fifu_is_on('fifu_save_dimensions')) {
        $dimension = $dimension[0];
        $width = explode(';', $dimension)[0];
        $height = explode(';', $dimension)[1];
    } else {
        $dimension = null;
        $width = fifu_maximum('width');
        $height = fifu_maximum('height');
    }
    return array('width' => $width, 'height' => $height);
}

// accelerated-mobile-pages plugin

function fifu_amp_url($url, $width, $height) {
    $size = get_post_meta(get_the_ID(), 'fifu_image_dimension');
    if (!empty($size)) {
        $size = explode(';', $size[0]);
        $width = $size[0];
        $height = $size[1];
    }
    return array(0 => $url, 1 => $width, 2 => $height);
}

