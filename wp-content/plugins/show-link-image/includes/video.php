<?php

/*
 * thumbnail_small 100 x 75
 * thumbnail_medium 200 x 150
 * thumbnail_large 640 x 476
 */

function fifu_vimeo_img($url, $size) {
    $img = unserialize(file_get_contents("https://vimeo.com/api/v2/video/" . fifu_vimeo_id($url) . ".php"));
    return $img != null ? $img[0][$size] . '?' . fifu_vimeo_id($url) : null;
}

function fifu_vimeo_id($url) {
    preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $url, $matches);
    return sizeof($matches) > 4 ? $matches[5] : null;
}

function fifu_vimeo_src($url) {
    return 'https://player.vimeo.com/video/' . fifu_vimeo_id($url);
}

function fifu_is_vimeo_video($url) {
    return strpos($url, 'vimeo') !== false;
}

function fifu_vimeo_social_url($id) {
    return 'https://player.vimeo.com/video/' . $id . '?autoplay=1';
}

function fifu_vimeo_social_img($url) {
    return fifu_vimeo_img($url, 'thumbnail_large');
}

/*
 * default 120 x 90
 * mqdefault 320 x 180
 * hqdefault 480 x 360
 * sddefault 640 x 480
 * maxresdefault
 */

function fifu_youtube_img($url, $size) {
    return 'https://img.youtube.com/vi/' . fifu_video_id($url) . '/' . $size . '.jpg' . '?' . fifu_youtube_parameter($url);
}

function fifu_youtube_id($url) {
    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
    return sizeof($matches) > 0 ? $matches[1] : null;
}

function fifu_youtube_src($url) {
    return 'https://www.youtube.com/embed/' . fifu_video_id($url);
}

function fifu_is_youtube_video($url) {
    return strpos($url, 'youtu') !== false;
}

function fifu_youtube_social_url($id) {
    return 'https://www.youtube.com/v/' . $id . '?version=3&amp;autohide=1';
}

function fifu_youtube_social_img($url) {
    return 'https://i.ytimg.com/vi/' . fifu_youtube_id($url) . '/hqdefault.jpg';
}

function fifu_youtube_parameter($url) {
    if (strpos($url, '?') === false)
        return null;
    return explode('?', $url)[1];
}

/*
 * cloudinary
 */

function fifu_cloudinary_src($url) {
    return $url;
}

function fifu_is_cloudinary_video($url) {
    return strpos($url, 'cloudinary.com') !== false && strpos($url, '/video/') !== false;
}

function fifu_cloudinary_img($url) {
    return str_replace('mp4', 'jpg', $url);
}

function fifu_cloudinary_social_img($url) {
    return fifu_cloudinary_img($url);
}

/*
 * tumblr
 */

function fifu_tumblr_src($url) {
    return $url;
}

function fifu_is_tumblr_video($url) {
    return strpos($url, 'tumblr.com') !== false;
}

function fifu_tumblr_img($url) {
    $tmp = str_replace('https://vt.media.tumblr.com', 'https://78.media.tumblr.com', $url);
    return str_replace('.mp4', '_smart1.jpg', $tmp);
}

function fifu_tumblr_social_img($url) {
    return fifu_tumblr_img($url);
}

/*
 * imgur
 */

function fifu_imgur_src($url) {
    return $url;
}

function fifu_is_imgur_video($url) {
    return strpos($url, 'imgur.com') !== false && strpos($url, 'mp4') !== false;
}

function fifu_imgur_img($url) {
    return str_replace('mp4', 'jpg', $url);
}

function fifu_imgur_social_img($url) {
    return fifu_imgur_img($url);
}

/* facebook */

function fifu_facebook_img($url) {
    return 'https://graph.facebook.com/' . fifu_facebook_id($url) . '/picture';
}

function fifu_facebook_id($url) {
    preg_match("/^(http\:\/\/|https\:\/\/)?(www\.)?(facebook\.com\/)?([^\/]+\/videos\/|watch\/\?v=)?([0-9]+)?([\/]*)/", $url, $matches);
    return sizeof($matches) > 4 ? $matches[5] : null;
}

function fifu_facebook_src($url) {
    return 'https://www.facebook.com/video/embed?video_id=' . fifu_facebook_id($url);
}

function fifu_is_facebook_video($url) {
    return strpos($url, 'facebook.com') !== false && (strpos($url, '/videos/') !== false || strpos($url, '/watch/') !== false);
}

function fifu_facebook_social_url($id) {
    return 'https://www.facebook.com/watch/?v=' . $id;
}

function fifu_facebook_social_img($url) {
    return fifu_facebook_img($url);
}

/* instagram */

function fifu_instagram_src($url) {
    return $url . 'embed/';
}

function fifu_is_instagram_video($url) {
    return strpos($url, 'instagram.com') !== false;
}

function fifu_instagram_img($url) {
    return $url . 'media/?size=l';
}

function fifu_instagram_social_img($url) {
    return fifu_instagram_img($url);
}

/* gag */

function fifu_gag_src($url) {
    return $url;
}

function fifu_is_gag_video($url) {
    return strpos($url, '9cache.com') !== false;
}

function fifu_gag_img($url) {
    return explode('_', $url)[0] . '_460c_offset0.jpg';
}

function fifu_gag_social_img($url) {
    return fifu_gag_img($url);
}

/*
 * size
 */

function fifu_video_size($type) {
    if (is_home())
        return get_option('fifu_video_' . $type . '_home');

    if (is_singular('page'))
        return get_option('fifu_video_' . $type . '_page');

    if (is_singular('post'))
        return get_option('fifu_video_' . $type . '_post');

    if (class_exists('WooCommerce')) {
        if (is_shop())
            return get_option('fifu_video_' . $type . '_shop');

        if (is_singular('product'))
            return get_option('fifu_video_' . $type . '_prod');

        if (is_archive())
            return get_option('fifu_video_' . $type . '_arch');
    }
    return null;
}

function fifu_video_size_ctgr($type) {
    return class_exists('WooCommerce') && is_archive() ? get_option('fifu_video_' . $type . '_ctgr') : null;
}

function fifu_video_ratio() {
    $width = get_option('fifu_video_width_rtio');
    $height = get_option('fifu_video_height_rtio');
    return is_numeric($width) ? $height / $width : 0;
}

function fifu_video_margin_bottom() {
    $size = '0px';
    if (class_exists('WooCommerce')) {
        if (is_shop() || is_archive())
            $size = get_option('fifu_video_margin_bottom');
    }
    return $size;
}

function fifu_video_margin_bottom_prod() {
    $size = '0px';
    if (class_exists('WooCommerce')) {
        if (is_shop() || is_archive() || is_product())
            $size = get_option('fifu_video_margin_bottom');
    }
    return $size;
}

function fifu_video_vertical_margin() {
    $size = get_option('fifu_video_vertical_margin');
    return $size ? (int) $size / 2 : 0;
}

function fifu_is_video($url) {
    return fifu_is_youtube_video($url) || fifu_is_vimeo_video($url) || fifu_is_cloudinary_video($url) || fifu_is_tumblr_video($url) || fifu_is_imgur_video($url) || fifu_is_facebook_video($url) || fifu_is_instagram_video($url) || fifu_is_gag_video($url);
}

function fifu_video_id($url) {
    if (fifu_is_youtube_video($url))
        return fifu_youtube_id($url);
    if (fifu_is_vimeo_video($url))
        return fifu_vimeo_id($url);
    if (fifu_is_facebook_video($url))
        return fifu_facebook_id($url);
    return null;
}

function fifu_video_img($url, $size) {
    if (fifu_is_youtube_video($url))
        return fifu_youtube_img($url, $size);
    if (fifu_is_vimeo_video($url))
        return fifu_vimeo_img($url, $size);
    if (fifu_is_cloudinary_video($url))
        return fifu_cloudinary_img($url);
    if (fifu_is_tumblr_video($url))
        return fifu_tumblr_img($url);
    if (fifu_is_imgur_video($url))
        return fifu_imgur_img($url);
    if (fifu_is_facebook_video($url))
        return fifu_facebook_img($url);
    if (fifu_is_instagram_video($url))
        return fifu_instagram_img($url);
    if (fifu_is_gag_video($url))
        return fifu_gag_img($url);
    return null;
}

function fifu_video_img_small($url) {
    if (fifu_is_youtube_video($url))
        return fifu_youtube_img($url, 'default');
    if (fifu_is_vimeo_video($url))
        return fifu_vimeo_img($url, 'thumbnail_small');
    if (fifu_is_cloudinary_video($url))
        return fifu_cloudinary_img($url);
    if (fifu_is_tumblr_video($url))
        return fifu_tumblr_img($url);
    if (fifu_is_imgur_video($url))
        return fifu_imgur_img($url);
    if (fifu_is_facebook_video($url))
        return fifu_facebook_img($url);
    if (fifu_is_instagram_video($url))
        return fifu_instagram_img($url);
    if (fifu_is_gag_video($url))
        return fifu_gag_img($url);
    return null;
}

function fifu_video_img_large($url) {
    if (fifu_is_youtube_video($url))
        return fifu_youtube_img($url, '0');
    if (fifu_is_vimeo_video($url))
        return fifu_vimeo_img($url, 'thumbnail_large');
    if (fifu_is_cloudinary_video($url))
        return fifu_cloudinary_img($url);
    if (fifu_is_tumblr_video($url))
        return fifu_tumblr_img($url);
    if (fifu_is_imgur_video($url))
        return fifu_imgur_img($url);
    if (fifu_is_facebook_video($url))
        return fifu_facebook_img($url);
    if (fifu_is_instagram_video($url))
        return fifu_instagram_img($url);
    if (fifu_is_gag_video($url))
        return fifu_gag_img($url);
    return null;
}

function fifu_video_src($url) {
    if (fifu_is_youtube_video($url))
        return fifu_youtube_src($url);
    if (fifu_is_vimeo_video($url))
        return fifu_vimeo_src($url);
    if (fifu_is_cloudinary_video($url))
        return fifu_cloudinary_src($url);
    if (fifu_is_tumblr_video($url))
        return fifu_tumblr_src($url);
    if (fifu_is_imgur_video($url))
        return fifu_imgur_src($url);
    if (fifu_is_facebook_video($url))
        return fifu_facebook_src($url);
    if (fifu_is_instagram_video($url))
        return fifu_instagram_src($url);
    if (fifu_is_gag_video($url))
        return fifu_gag_src($url);
    return null;
}

function fifu_video_social_url($id) {
    if (fifu_is_youtube_video($id))
        return fifu_youtube_social_url($id);
    if (fifu_is_vimeo_video($id))
        return fifu_vimeo_social_url($id);
    if (fifu_is_facebook_video($id))
        return fifu_facebook_social_url($id);
    return null;
}

function fifu_video_social_img($url) {
    if (fifu_is_youtube_video($url))
        return fifu_youtube_social_img($url);
    if (fifu_is_vimeo_video($url))
        return fifu_vimeo_social_img($url);
    if (fifu_is_cloudinary_video($url))
        return fifu_cloudinary_img($url);
    if (fifu_is_tumblr_video($url))
        return fifu_tumblr_img($url);
    if (fifu_is_imgur_video($url))
        return fifu_imgur_img($url);
    if (fifu_is_facebook_video($url))
        return fifu_facebook_img($url);
    if (fifu_is_instagram_video($url))
        return fifu_instagram_img($url);
    if (fifu_is_gag_video($url))
        return fifu_gag_img($url);
    return null;
}

/*
 * auto play
 */

function fifu_mouse_youtube_enabled() {
    return fifu_is_on('fifu_mouse_youtube');
}

function fifu_mouse_vimeo_enabled() {
    return fifu_is_on('fifu_mouse_vimeo');
}

/*
 * thumbnail
 */

function fifu_video_thumb_enabled() {
    return fifu_is_on('fifu_video_thumb') && (is_home() || (class_exists('WooCommerce') && is_shop()) || is_archive() || is_search());
}

