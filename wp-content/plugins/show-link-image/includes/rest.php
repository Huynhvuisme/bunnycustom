<?php

add_filter('rest_prepare_post', 'fifu_rest_get', 10, 3);

function fifu_rest_get($data, $post, $request) {
    $_data = $data->data;

    $url = get_post_meta($post->ID, 'fifu_image_url', true);
    if ($url)
        $_data['fifu_image_url'] = $url;

    $url = get_post_meta($post->ID, 'fifu_video_url', true);
    if ($url)
        $_data['fifu_video_url'] = $url;

    for ($i = 0; $i < get_option('fifu_spinner_slider'); $i ++) {
        $url = get_post_meta($post->ID, 'fifu_slider_image_url_' . $i, true);
        if ($url)
            $_data['fifu_slider_image_url_' . $i] = $url;
    }

    $alt = get_post_meta($post->ID, 'fifu_image_alt', true);
    if ($alt)
        $_data['fifu_image_alt'] = $alt;

    $data->data = $_data;
    return $data;
}

add_filter('rest_insert_post', 'fifu_rest_post', 10, 3);

function fifu_rest_post($post, $request, $creating) {
    $shortcode = null;

    $url = esc_url_raw($request['fifu_image_url']);
    if ($url)
        update_post_meta($post->ID, 'fifu_image_url', fifu_convert($url));

    $url = esc_url_raw($request['fifu_video_url']);
    if ($url)
        update_post_meta($post->ID, 'fifu_video_url', $url);

    for ($i = 0; $i < get_option('fifu_spinner_slider'); $i ++) {
        $url = esc_url_raw($request['fifu_slider_image_url_' . $i]);
        if ($url)
            update_post_meta($post->ID, 'fifu_slider_image_url_' . $i, fifu_convert($url));
    }

    $alt = $request['fifu_image_alt'];
    if ($alt)
        update_post_meta($post->ID, 'fifu_image_alt', $alt);

    fifu_save($post->ID);
}

add_filter('rest_api_init', 'fifu_rest_api_init');

function fifu_rest_api_init() {
    foreach (fifu_get_post_types() as $cpt) {
        if ($cpt != 'post' && $cpt != 'page' && $cpt != 'product')
            add_filter('rest_insert_' . $cpt, 'fifu_rest_post', 10, 3);
    }
}

