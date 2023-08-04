<?php

add_filter('wp_insert_post_data', 'fifu_remove_first_image_ext', 10, 2);

function fifu_remove_first_image_ext($data, $postarr) {
    /* invalid or internal or ignore */
    if (!$_POST || isset($_POST['fifu_input_url']) || isset($_POST['fifu_ignore_auto_set']))
        return $data;

    $content = $postarr['post_content'];
    if (!$content)
        return $data;

    $contentClean = fifu_show_all_images($content);
    $contentClean = fifu_show_all_videos($contentClean);
    $data = str_replace($content, $contentClean, $data);

    $img = fifu_first_img_in_content($contentClean);
    $video = fifu_first_video_in_content($contentClean);

    if (!$img && !$video)
        return $data;

    $media = $img ? $img : $video;

    if (fifu_is_off('fifu_pop_first'))
        return str_replace($media, fifu_show_media($media), $data);

    return str_replace($media, fifu_hide_media($media), $data);
}

add_action('save_post', 'fifu_save_properties_ext');

function fifu_save_properties_ext($post_id) {
    if (isset($_POST['fifu_input_url']))
        return;

    $url = esc_url_raw(fifu_first_url_in_content($post_id, null, false));

    if ($url && fifu_is_on('fifu_get_first')) {
        update_post_meta($post_id, 'fifu_image_url', fifu_convert($url));
        fifu_update_fake_attach_id($post_id);
    }
}

add_action('save_post', 'fifu_save_properties_video_ext');

function fifu_save_properties_video_ext($post_id) {
    if (isset($_POST['fifu_video_input_url']))
        return;

    $url = esc_url_raw(fifu_first_url_in_content($post_id, null, true));

    if ($url && fifu_is_on('fifu_get_first')) {
        update_post_meta($post_id, 'fifu_video_url', $url);
        fifu_update_or_delete($post_id, 'fifu_video_url', $url);
    }
}

function fifu_first_img_in_content($content) {
    $matches = array();
    preg_match_all('/<img[^>]*>/', $content, $matches);
    return $matches && $matches[0] ? $matches[0][0] : null;
}

function fifu_first_video_in_content($content) {
    $matches = array();
    preg_match_all('/<iframe[^>]*(youtu|vimeo|cloudinary|tumblr|imgur|facebook|instagram|9cache)[^>]*>/', $content, $matches);
    return $matches && $matches[0] ? $matches[0][0] : null;
}

function fifu_show_all_images($content) {
    $matches = array();
    preg_match_all('/<img[^>]*display:[ ]*none[^>]*>/', $content, $matches);
    foreach ($matches[0] as $img) {
        $content = str_replace($img, fifu_show_media($img), $content);
    }
    return $content;
}

function fifu_show_all_videos($content) {
    $matches = array();
    preg_match_all('/<iframe[^>]*(youtu|vimeo|cloudinary|tumblr|imgur|facebook|instagram|9cache)[^>]*display:[ ]*none[^>]*>/', $content, $matches);
    foreach ($matches[0] as $video) {
        $content = str_replace($video, fifu_show_media($video), $content);
    }
    return $content;
}

function fifu_hide_media($img) {
    if (strpos($img, 'style="') !== false)
        return preg_replace('/style=.[^"]*["]/', 'style="display:none"', $img);
    return preg_replace('/[\/]*>/', ' style="display:none">', $img);
}

function fifu_show_media($img) {
    return preg_replace('/style=[\\\]*.display:[ ]*none[\\\]*./', '', $img);
}

function fifu_first_url_in_content($post_id, $content, $is_video) {
    $content = $content ? $content : get_post_field('post_content', $post_id);
    if (!$content)
        return;
    $matches = array();
    if ($is_video)
        preg_match_all('/<iframe[^>]*(youtu|vimeo|cloudinary|tumblr|imgur|facebook|instagram|9cache)[^>]*>/', $content, $matches);
    else
        preg_match_all('/<img[^>]*>/', $content, $matches);

    if (!$matches[0])
        return;

    $aux2 = null;

    //double quotes
    $aux1 = explode('src="', $matches[0][0]);
    if ($aux1 && count($aux1) > 1) {
        $aux2 = explode('"', $aux1[1]);
    }

    //single quotes
    if (!$aux2 || !$aux2[0]) {
        $aux1 = explode("src='", $matches[0][0]);
        if ($aux1 && count($aux1) > 1)
            $aux2 = explode("'", $aux1[1]);
    }

    return $matches && $matches[0] ? $aux2[0] : null;
}

function fifu_update_fake_attach_id($post_id) {
    fifu_db_update_fake_attach_id($post_id);
    fifu_db_update_fake_attach_id_gallery($post_id);
}

