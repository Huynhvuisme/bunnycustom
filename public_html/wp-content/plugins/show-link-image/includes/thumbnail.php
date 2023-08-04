<?php

add_filter('wp_head', 'fifu_add_jquery');
add_filter('wp_head', 'fifu_add_js');
add_filter('wp_head', 'fifu_add_social_tags');
add_filter('wp_head', 'fifu_video_add_social_tags');
add_filter('wp_head', 'fifu_add_lightslider');
add_filter('wp_head', 'fifu_add_video');
add_filter('wp_head', 'fifu_add_shortcode');
add_filter('wp_head', 'fifu_apply_css');

function fifu_add_js() {
    if (fifu_is_on('fifu_lazy')) {
        wp_enqueue_script('lazyload', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.1.0/jquery.lazyloadxt.min.js');
        wp_enqueue_script('lazyload-srcset', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.1.0/jquery.lazyloadxt.srcset.min.js');
        if (fifu_is_on('fifu_video'))
            wp_enqueue_script('lazyload-video', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.1.0/jquery.lazyloadxt.video.min.js');
        wp_enqueue_style('lazyload-spinner', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.1.0/jquery.lazyloadxt.spinner.min.css');
    }
    if (fifu_hover_selected()) {
        wp_register_style('fifu-hover', plugins_url('/html/css/hover.css', __FILE__));
        wp_enqueue_style('fifu-hover');
    }
    if (fifu_is_on('fifu_slider')) {
        wp_register_style('fifu-slider-style', plugins_url('/html/css/slider.css', __FILE__));
        wp_enqueue_style('fifu-slider-style');
    }
    if (class_exists('WooCommerce')) {
        wp_register_style('fifu-woo', plugins_url('/html/css/woo.css', __FILE__));
        wp_enqueue_style('fifu-woo');
    }

    if (fifu_is_on('fifu_mouse_youtube'))
        wp_enqueue_script('youtube', 'https://www.youtube.com/player_api');

    if (fifu_is_on('fifu_mouse_vimeo'))
        wp_enqueue_script('vimeo', 'https://f.vimeocdn.com/js/froogaloop2.min.js');

    include 'html/script.html';
}

function fifu_add_social_tags() {
    $post_id = get_the_ID();
    $title = get_the_title($post_id);
    $description = wp_strip_all_tags(get_post_field('post_content', $post_id));

    if (fifu_is_off('fifu_social'))
        return;

    global $wpdb;
    $arr = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s", $post_id, 'fifu_%image_url%'));

    foreach ($arr as $url)
        include 'html/og-image.html';

    include 'html/social.html';

    foreach ($arr as $url)
        include 'html/twitter-image.html';
}

function fifu_video_add_social_tags() {
    $post_id = get_the_ID();
    $url = get_post_meta($post_id, 'fifu_video_url', true);
    $title = strip_tags(get_the_title($post_id));
    $description = str_replace("'", "&#39;", str_replace('"', '&#34;', wp_strip_all_tags(get_post_field('post_content', $post_id))));
    $video_id = fifu_video_id($url);
    $video_src = fifu_video_src($url);
    $video_url = $video_id == null ? $url : fifu_video_social_url($video_id);
    $video_img = fifu_video_social_img($url);

    if ($url) {
        include 'html/social-video.html';
    }
}

function fifu_add_jquery() {
    if (fifu_is_on('fifu_jquery'))
        include 'html/jquery.html';
}

function fifu_add_lightslider() {
    if (fifu_is_on('fifu_slider'))
        include 'html/lightslider.html';
}

function fifu_add_video() {
    if (fifu_is_on('fifu_video'))
        include 'html/video.html';
}

function fifu_add_shortcode() {
    if (fifu_is_on('fifu_shortcode'))
        include 'html/shortcode.html';
}

function fifu_apply_css() {
    if (fifu_is_off('fifu_wc_lbox'))
        echo '<style>[class$="woocommerce-product-gallery__trigger"] {display:none !important;}</style>';
    else
        echo '<style>[class$="woocommerce-product-gallery__trigger"] {visibility:hidden;}</style>';
}

add_filter('woocommerce_product_get_image', 'fifu_woo_replace', 10, 5);

function fifu_woo_replace($html, $product, $woosize) {
    return fifu_replace($html, get_the_id(), null, null);
}

add_filter('post_thumbnail_html', 'fifu_replace', 10, 4);

function fifu_replace($html, $post_id, $post_thumbnail_id, $size) {
    $width = fifu_get_attribute('width', $html);
    $height = fifu_get_attribute('height', $html);

    if (fifu_is_on('fifu_lazy') && !is_admin())
        $html = str_replace("src", "data-src", $html);

    $videoUrl = get_post_meta($post_id, 'fifu_video_url', true);
    if (fifu_is_on('fifu_video') && $videoUrl)
        return fifu_video_replace($html, $videoUrl, $width, $height);

    $shortcode = get_post_meta($post_id, 'fifu_shortcode', true);
    if (fifu_is_on('fifu_shortcode') && $shortcode)
        return fifu_shortcode_replace($shortcode, $post_id, $width, $height);

    $sliderUrl = get_post_meta($post_id, 'fifu_slider_image_url_0', true);
    if (fifu_is_on('fifu_slider') && fifu_is_slider($sliderUrl)) {
        if (is_from_flickr($sliderUrl))
            return fifu_slider_get_html($post_id, fifu_flickr_size_auto($width, $height), $width, $height);
        else if (is_from_speedup($sliderUrl))
            return fifu_slider_get_html($post_id, fifu_speedup_size_auto($width, $height), $width, $height);

        return fifu_slider_get_html($post_id, fifu_flickr_size_auto(fifu_get_img_width_from_html($html), 0), $width, $height);
    }

    if (is_from_flickr(fifu_get_src_from_html($html)))
        return fifu_flickr_replace($html, $post_id);

    $url = get_post_meta($post_id, 'fifu_image_url', true);
    $alt = get_post_meta($post_id, 'fifu_image_alt', true);
    $css = get_option('fifu_css');

    // onerror
    $error_url = get_option('fifu_error_url');
    if ($error_url)
        $html = str_replace('/>', sprintf(' onerror="this.src=\'%s\'"/>', $error_url), $html);

    if ($url)
        return $css ? str_replace('/>', ' style="' . $css . '"/>', $html) : $html;

    $url = !$sliderUrl ? $url : $sliderUrl;

    return !$url ? $html : fifu_get_html($url, $alt, $width, $height);
}

function fifu_is_slider($sliderUrl) {
    return $sliderUrl && !is_ajax_call() && is_valid_slider_locale();
}

function fifu_video_replace($html, $url, $width, $height) {
    return $url ? fifu_get_html(fifu_video_img_large($url), null, $width, $height) : $html;
}

function fifu_shortcode_replace($shortcode, $post_id, $width, $height) {
    $height = get_option('fifu_shortcode_max_height') ? get_option('fifu_shortcode_max_height') : $height;
    $longcode = do_shortcode($shortcode);
    if (fifu_is_url($longcode))
        return fifu_get_html($longcode, null, $width, $height);
    if ($width < get_option('fifu_shortcode_min_width'))
        return fifu_get_shortcode_html($post_id, $width, $height);
    $longcode = fifu_replace_attribute($longcode, 'width', $width);
    $longcode = fifu_replace_attribute($longcode, 'height', $height);
    return sprintf('<!-- Powered by Show Image Link plugin -->%s', do_shortcode($longcode));
}

function fifu_is_url($var) {
    return strpos($var, 'http') === 0;
}

function is_ajax_call() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') || wp_doing_ajax();
}

function fifu_get_html($url, $alt, $width, $height) {
    $css = get_option('fifu_css');

    if (fifu_is_video($url)) {
        $cls = 'fifu-video';
        if (class_exists('WooCommerce') && is_cart())
            $cls = 'fifu';
        else
            $css = 'opacity:0';
    } else {
        $cls = 'fifu';
    }

    if (fifu_should_hide()) {
        $css = 'display:none';
        $cls = 'fifu';
    }

    // variable product
    if (fifu_is_on('fifu_variation') && class_exists('WooCommerce') && is_product() && $cls != 'fifu-video') {
        $cls = 'attachment-shop_single size-shop_single wp-post-image';
        $css = $css . ';width:100%;';
    }

    return sprintf('<!-- Powered by Show Image Link plugin --> <img class="%s" %s alt="%s" title="%s" style="%s" data-large_image="%s" data-large_image_width="%s" data-large_image_height="%s" onerror="%s" width="%s" height="%s">', $cls, fifu_lazy_url($url), $alt, $alt, $css, $url, "800", "600", "jQuery(this).hide();", $width, $height);
}

function fifu_get_shortcode_html($post_id, $width, $height) {
    $url = fifu_main_image_url($post_id);
    $css = get_option('fifu_css');
    $cls = 'fifu';
    return sprintf('<!-- Powered by Show Image Link plugin --> <img id="%s" class="%s" %s style="%s" data-large_image="%s" data-large_image_width="%s" data-large_image_height="%s" onerror="%s">', 'fifu-shortcode-' . $post_id, $cls, fifu_lazy_url($url), $css, $url, $width, $height, "jQuery(this).hide();");
}

function fifu_slider_get_html($post_id, $flickr_size, $width, $height) {
    $css = get_option('fifu_css');

    if (fifu_should_hide())
        $css = 'display:none';

    $html = sprintf('<div class="fifu-slider" style="%s">', 'max-width:' . $width . 'px');
    $html = $html . '<!-- Powered by Show Image Link plugin --> <ul id="image-gallery" class="gallery list-unstyled cS-hidden">';
    $max = get_option('fifu_spinner_slider');
    for ($i = 0; $i < $max; $i ++) {
        $url = get_post_meta($post_id, 'fifu_slider_image_url_' . $i, true);
        if ($url) {
            if (is_from_flickr($url)) {
                $html = $html . sprintf('<li data-thumb="%s" data-src="%s"><img %s style="%s" class="fifu" onerror="%s"/></li>', fifu_resize_flickr_image('t', $url), fifu_resize_flickr_image(fifu_is_lazy() ? 't' : $width, $url), fifu_lazy_url(fifu_resize_flickr_image(fifu_is_lazy() ? 't' : $flickr_size, $url)), $css, "jQuery(this).hide();");
                continue;
            } else if (is_from_speedup($url)) {
                $html = $html . sprintf('<li data-thumb="%s" data-src="%s"><img %s style="%s" class="fifu" onerror="%s"/></li>', fifu_resize_speedup_image_size('150', $url), fifu_resize_speedup_image_size(fifu_is_lazy() ? '150' : $width, $url), fifu_lazy_url(fifu_resize_speedup_image_size(fifu_is_lazy() ? '150' : $flickr_size, $url)), $css, "jQuery(this).hide();");
                continue;
            }
            $html = $html . sprintf('<li data-thumb="%s" data-src="%s"><img %s style="%s" class="fifu" onerror="%s"/></li>', $url, $url, fifu_lazy_url($url), $css, "jQuery(this).hide();");
        }
    }
    return $html . '</ul></div>';
}

function fifu_is_lazy() {
    return fifu_is_on('fifu_lazy');
}

function is_valid_slider_locale() {
    return class_exists('WooCommerce') && is_cart() ? false : (class_exists('WooCommerce') && is_shop()) || is_home() || is_single() || is_page() || is_archive();
}

function is_slider_empty($post_id) {
    for ($i = 0; $i < 5; $i ++)
        if (get_post_meta($post_id, 'fifu_slider_image_url_' . $i, true))
            return false;
    return true;
}

add_filter('the_content', 'fifu_add_to_content');

function fifu_add_to_content($content) {
    return is_singular() && has_post_thumbnail() && ((is_singular('post') && fifu_is_on('fifu_content')) or ( is_singular('page') && fifu_is_on('fifu_content_page'))) ? get_the_post_thumbnail() . $content : $content;
}

function fifu_should_hide() {
    return ((is_singular('post') && fifu_is_on('fifu_hide_post')) || (is_singular('page') && fifu_is_on('fifu_hide_page')));
}

function fifu_should_crop() {
    return fifu_is_on('fifu_same_size');
}

function fifu_crop_selectors() {
    $concat = '';
    for ($x = 0; $x <= 4; $x ++) {
        $selector = get_option('fifu_crop' . $x);
        if ($selector)
            $concat = $concat . ',' . $selector;
    }
    return $concat;
}

function fifu_hover_selected() {
    return get_option('fifu_hover');
}

function shortcode($url) {
    if(is_string($url)){ // giaiphapmmo.net sửa lỗi liên quan rankmathSEO truyền sai kiểu dữ liệu
        preg_match("/\[.*\]/", urldecode($url), $matches);
        return $matches ? $matches[0] : null;
    }else{               // giaiphapmmo.net sửa lỗi liên quan rankmathSEO truyền sai kiểu dữ liệu
        return $url;
    }
}

function fifu_main_image_url($post_id) {
    $url = get_post_meta($post_id, 'fifu_slider_image_url_0', true);

    if (!$url)
        $url = get_post_meta($post_id, 'fifu_image_url', true);

    if (!$url) {
        $video_url = get_post_meta($post_id, 'fifu_video_url', true);
        $url = fifu_video_img_large($video_url);
    }

    return $url;
}

function fifu_no_internal_image($post_id) {
    return get_post_meta($post_id, '_thumbnail_id', true) == -1 || get_post_meta($post_id, '_thumbnail_id', true) == null || get_post_meta($post_id, '_thumbnail_id', true) == get_option('fifu_default_attach_id');
}

function fifu_lazy_url($url) {
    if (fifu_is_off('fifu_lazy') || fifu_is_video($url) || is_ajax_call())
        return 'src="' . $url . '"';
    return (fifu_is_main_page() ? 'data-src="' : 'src="') . $url . '"';
}

function fifu_lazy_src_type() {
    if (fifu_is_off('fifu_lazy') || is_ajax_call())
        return 'src=';
    return (fifu_is_main_page() ? 'data-src=' : 'src=');
}

function fifu_valid_url($url) {
    // return !empty($url) && (fifu_is_off('fifu_valid') || fifu_from_instagram($url)) ? true : strpos(@get_headers(str_replace(" ", "%20", $url))[0], '200 OK') !== false; // GPM COMMENT
    // GPM START
    if(!empty($url) && (fifu_is_off('fifu_valid') || fifu_from_instagram($url)))
        return true;
    else{
        if($url == null || empty($url))
            return false;
        return strpos(@get_headers(str_replace(" ", "%20", $url))[0], '200 OK') !== false;
    }
    // GPM END
}

function fifu_is_main_page() {
    return is_home() || (class_exists('WooCommerce') && is_shop());
}

function fifu_has_internal_image($post_id) {
    $featured_image = get_post_meta($post_id, '_thumbnail_id', true);
    return $featured_image && $featured_image != -1 && $featured_image != get_option('fifu_fake_attach_id');
}

function fifu_is_in_editor() {
    return !is_admin() || get_current_screen() == null ? false : get_current_screen()->parent_base == 'edit';
}

function fifu_get_image_sizes() {
    global $_wp_additional_image_sizes;
    $sizes = array();
    foreach (get_intermediate_image_sizes() as $_size) {
        if (in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
            $sizes[$_size]['width'] = get_option("{$_size}_size_w");
            $sizes[$_size]['height'] = get_option("{$_size}_size_h");
            $sizes[$_size]['crop'] = (bool) get_option("{$_size}_crop");
        } elseif (isset($_wp_additional_image_sizes[$_size])) {
            $sizes[$_size] = array(
                'width' => $_wp_additional_image_sizes[$_size]['width'],
                'height' => $_wp_additional_image_sizes[$_size]['height'],
                'crop' => $_wp_additional_image_sizes[$_size]['crop'],
            );
        }
    }
    return $sizes;
}

function fifu_get_image_size($size) {
    $sizes = fifu_get_image_sizes();
    if (is_array($size)) {
        $arr_size = array();
        $arr_size['width'] = count($size) > 0 ? $size[0] : null;
        $arr_size['height'] = count($size) > 1 ? $size[1] : null;
        return $arr_size;
    }
    return isset($sizes[$size]) ? $sizes[$size] : false;
}

function fifu_get_default_url() {
    return wp_get_attachment_url(get_option('fifu_default_attach_id'));
}

