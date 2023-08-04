<?php
include_once(ABSPATH.'wp-admin/includes/plugin.php');

add_action('add_meta_boxes', 'fifu_insert_meta_box');

function fifu_insert_meta_box() {
    $post_types = fifu_get_post_types();

    foreach ($post_types as $post_type) {
        if ($post_type == 'product') {
            add_meta_box('urlMetaBox', 'Show Image Link', 'fifu_show_elements', $post_type, 'side', 'low');
            if (fifu_is_on('fifu_slider'))
                add_meta_box('sliderImageUrlMetaBox', 'Featured Slider from URL', 'fifu_slider_show_elements', $post_type, 'side', 'low');
            add_meta_box('wooCommerceGalleryMetaBox', 'Image Gallery from URL', 'fifu_wc_show_elements', $post_type, 'side', 'low');
            if (fifu_is_on('fifu_variation'))
                add_meta_box('wooCommerceVariationMetaBox', 'Image Variation from URL', 'fifu_wc_variation_show_elements', $post_type, 'side', 'low');
            if (fifu_is_on('fifu_video')) {
                add_meta_box('videoUrlMetaBox', 'Featured Video from URL', 'fifu_video_show_elements', $post_type, 'side', 'low');
                add_meta_box('wooCommerceVideoGalleryMetaBox', 'Video Gallery from URL', 'fifu_video_wc_show_elements', $post_type, 'side', 'low');
            }
            if (fifu_is_on('fifu_shortcode'))
                add_meta_box('shortCodeMetaBox', 'Featured Shortcode (Beta)', 'fifu_shortcode_show_elements', $post_type, 'side', 'low');
        } else {
            if ($post_type) {
                add_meta_box('imageUrlMetaBox', 'Show Image Link', 'fifu_show_elements', $post_type, 'side', 'low');
                if (fifu_is_on('fifu_slider'))
                    add_meta_box('sliderImageUrlMetaBox', 'Featured Slider from URL', 'fifu_slider_show_elements', $post_type, 'side', 'low');
                if (fifu_is_on('fifu_video'))
                    add_meta_box('videoUrlMetaBox', 'Featured Video from URL', 'fifu_video_show_elements', $post_type, 'side', 'low');
                if (fifu_is_on('fifu_shortcode'))
                    add_meta_box('shortCodeMetaBox', 'Featured Shortcode (Beta)', 'fifu_shortcode_show_elements', $post_type, 'side', 'low');
            }
        }
    }
}

add_action('add_meta_boxes', 'fifu_add_css');

function fifu_add_css() {
    wp_register_style('show-image-link', plugins_url('/html/css/editor.css', __FILE__));
    wp_enqueue_style('show-image-link');
}

function fifu_show_elements($post) {
    $margin = 'margin-top:10px;';
    $width = 'width:100%;';
    $height = 'height:150px;';
    $align = 'text-align:left;';

    $url = get_post_meta($post->ID, 'fifu_image_url', true);
    $alt = get_post_meta($post->ID, 'fifu_image_alt', true);

    if ($url) {
        $show_button = 'display:none;';
        $show_alt = $show_image = $show_link = '';
    } else {
        $show_alt = $show_image = $show_link = 'display:none;';
        $show_button = '';
    }

    $show_ignore = fifu_is_on('fifu_get_first') || fifu_is_on('fifu_pop_first') || fifu_is_on('fifu_ovw_first') ? '' : 'display:none;';

    include 'html/meta-box.html';
}

function fifu_shortcode_show_elements($post) {
    $width = 'width:100%;';
    $align = 'text-align:left;';

    $url = get_post_meta($post->ID, 'fifu_shortcode', true);

    if ($url)
        $show_shortcode = $show_link = '';
    else
        $show_shortcode = $show_link = 'display:none;';

    include 'html/meta-box-shortcode.html';
}

function fifu_video_show_elements($post) {
    $margin = 'margin-top:10px;';
    $width = 'width:100%;';
    $height = 'height:150px;';
    $align = 'text-align:left;';

    $url = get_post_meta($post->ID, 'fifu_video_url', true);

    if ($url) {
        $show_button = 'display:none;';
        $show_video = $show_link = '';
    } else {
        $show_video = $show_link = 'display:none;';
        $show_button = '';
    }

    include 'html/meta-box-video.html';
}

function fifu_wc_show_elements($post) {
    $margin = 'margin-top:1px;';
    $width = 'width:70%;';
    $height = 'height:150px;';
    $align = 'text-align:left;';
    $altWidth = 'width:86%;';

    for ($i = 0; $i < get_option('fifu_spinner_image'); $i ++) {
        $url[$i] = get_post_meta($post->ID, 'fifu_image_url_' . $i, true);
        $alt[$i] = get_post_meta($post->ID, 'fifu_image_alt_' . $i, true);

        if ($url[$i]) {
            $show_url[$i] = $show_button[$i] = 'display:none;';
            $show_alt[$i] = $show_image[$i] = $show_link[$i] = '';
        } else {
            $show_alt[$i] = $show_image[$i] = $show_link[$i] = 'display:none;';
            $show_url[$i] = $show_button[$i] = '';
        }

        include 'html/woo-meta-box.html';
    }
}

function fifu_wc_variation_show_elements($post) {
    $margin = 'margin-top:1px;';
    $width = 'width:70%;';
    $height = 'height:100px;';
    $align = 'text-align:left;';
    $altWidth = 'width:86%;';

    $i = 0;
    foreach (fifu_db_variantion_products($post->ID) as $var) {
        $id = $var->id;

        $aux = explode(' - ', $var->post_title);
        $title = count($aux) > 1 ? $aux[1] : '';

        $url[$i] = get_post_meta($id, 'fifu_image_url', true);
        $alt[$i] = '#' . $id . ': ' . $title;

        if ($url[$i]) {
            $show_url[$i] = $show_button[$i] = 'display:none;';
            $show_image[$i] = $show_link[$i] = '';
        } else {
            $show_image[$i] = $show_link[$i] = 'display:none;';
            $show_url[$i] = $show_button[$i] = '';
        }

        include 'html/woo-meta-box-variation.html';
        $i++;
    }

    include 'html/refresh.html';
}

function fifu_video_wc_show_elements($post) {
    $margin = 'margin-top:1px;';
    $width = 'width:70%;';
    $height = 'height:150px;';
    $align = 'text-align:left;';

    for ($i = 0; $i < get_option('fifu_spinner_video'); $i ++) {
        $url[$i] = get_post_meta($post->ID, 'fifu_video_url_' . $i, true);

        if ($url[$i]) {
            $show_url[$i] = $show_button[$i] = 'display:none;';
            $show_video[$i] = $show_link[$i] = '';
        } else {
            $show_video[$i] = $show_link[$i] = 'display:none;';
            $show_url[$i] = $show_button[$i] = '';
        }

        include 'html/woo-meta-box-video.html';
    }
}

function fifu_slider_show_elements($post) {
    $margin = 'margin-top:1px;';
    $width = 'width:65%;';
    $height = 'height:150px;';
    $align = 'text-align:left;';
    $altWidth = 'width:83%;';

    for ($i = 0; $i < get_option('fifu_spinner_slider'); $i ++) {
        $url[$i] = get_post_meta($post->ID, 'fifu_slider_image_url_' . $i, true);
        $alt[$i] = get_post_meta($post->ID, 'fifu_slider_image_alt_' . $i, true);

        if ($url[$i]) {
            $show_url[$i] = $show_button[$i] = 'display:none;';
            $show_alt[$i] = $show_image[$i] = $show_link[$i] = '';
        } else {
            $show_alt[$i] = $show_image[$i] = $show_link[$i] = 'display:none;';
            $show_url[$i] = $show_button[$i] = '';
        }

        include 'html/meta-box-slider.html';
    }
}

add_filter('wp_insert_post_data', 'fifu_remove_first_image', 10, 2);

function fifu_remove_first_image($data, $postarr) {
    /* invalid or external or ignore */
    if (!$_POST || !isset($_POST['fifu_input_url']) || isset($_POST['fifu_ignore_auto_set']))
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

add_action('save_post', 'fifu_save_properties');

// (giaiphapmmo) Lưu khi edit sản phẩm
function fifu_save_properties($post_id) {
    // start giaiphapmmo them vao

    // Test start
    // die(json_encode($_POST));
    // test end

    if($_SERVER['REQUEST_URI'] == '/wp-admin/admin-ajax.php') // bỏ qua lúc quick edit
        return;

    $is_set_image_url = false;
    $is_set_image_gallery = false;
    for ($i = 0; $i < get_option('fifu_spinner_image'); $i ++) {
        if (isset($_POST['fifu_input_url_' . $i]) && $_POST['fifu_input_url_' . $i] != null && $_POST['fifu_input_url_' . $i] != '') {
            $is_set_image_gallery = true;
            break;
        }
    }

    if (isset($_POST['fifu_input_url']) && $_POST['fifu_input_url'] != null && $_POST['fifu_input_url'] != '') {
        // die('fifu_input_url null');
        // die(json_encode($_POST));
        $is_set_image_url = true;
    }

    if(!$is_set_image_url && !$is_set_image_gallery) // Nếu không set hình ảnh nào thì bỏ qua
        return;

    // end giaiphapmmo them vao để xử lý lỗi sửa mất ảnh

    if (!$_POST || get_post_type($post_id) == 'nav_menu_item')
        return;

    $ignore = false;
    if (isset($_POST['fifu_ignore_auto_set']))
        $ignore = $_POST['fifu_ignore_auto_set'] == 'on';

    /* image url */
    $url = null;
    if (isset($_POST['fifu_input_url']) /*giaiphapmmo*/&& $is_set_image_url/*giaiphapmmo*/) {
        $url = esc_url_raw($_POST['fifu_input_url']);
        if (!$ignore) {
            $first = fifu_first_url_in_content($post_id, null, false);
            if ($first && fifu_is_on('fifu_get_first') && (!$url || fifu_is_on('fifu_ovw_first')))
                $url = $first;
        }
        fifu_update_or_delete($post_id, 'fifu_image_url', $url);
    }

    /* image url from wcfm */
    if (!$url && fifu_is_wcfm_activate() && isset($_POST['wcfm_products_manage_form'])) {
        $url = esc_url_raw(fifu_get_wcfm_url($_POST['wcfm_products_manage_form']));
        if ($url)
            fifu_update_or_delete($post_id, 'fifu_image_url', $url);
    }

    /* alt */
    if (isset($_POST['fifu_input_alt'])) {
        $alt = wp_strip_all_tags($_POST['fifu_input_alt']);
        $alt = !$alt && $url && fifu_is_on('fifu_auto_alt') ? get_the_title() : $alt;
        fifu_update_or_delete_value($post_id, 'fifu_image_alt', $alt);
    }

    /* gallery */
    if (get_post_type(get_the_ID()) == 'product') {
        for ($i = 0; $i < get_option('fifu_spinner_image'); $i ++) {
            if (isset($_POST['fifu_input_url_' . $i]) && isset($_POST['fifu_input_alt_' . $i])) {
                $url = esc_url_raw($_POST['fifu_input_url_' . $i]);
                $alt = wp_strip_all_tags($_POST['fifu_input_alt_' . $i]);
                fifu_update_or_delete($post_id, 'fifu_image_url_' . $i, $url);
                fifu_update_or_delete_value($post_id, 'fifu_image_alt_' . $i, $alt);
            }
        }
    }

    fifu_save($post_id);
}

function fifu_save($post_id) {
    fifu_video_save_properties($post_id);
    fifu_slider_save_properties($post_id);

    fifu_update_fake_attach_id($post_id); // giaiphapmmo test comment => khi thêm mới url sẽ tạo qua hàm này

    fifu_shortcode_save_properties($post_id);
    fifu_variation_save_properties($post_id);

    if (fifu_is_on('fifu_auto_category'))
        fifu_db_insert_auto_category_image();
}

function fifu_video_save_properties($post_id) {
    /* video url */
    if (isset($_POST['fifu_video_input_url'])) {
        $url = esc_url_raw($_POST['fifu_video_input_url']);
        $first = fifu_first_url_in_content($post_id, null, true);
        if ($first && fifu_is_on('fifu_get_first') && (!$url || fifu_is_on('fifu_ovw_first')))
            $url = $first;
        fifu_update_or_delete($post_id, 'fifu_video_url', $url);
    }

    /* gallery */
    if (get_post_type(get_the_ID()) == 'product') {
        for ($i = 0; $i < get_option('fifu_spinner_video'); $i ++) {
            if (isset($_POST['fifu_video_input_url_' . $i])) {
                $url = esc_url_raw($_POST['fifu_video_input_url_' . $i]);
                fifu_update_or_delete($post_id, 'fifu_video_url_' . $i, $url);
            }
        }
    }
}

function fifu_slider_save_properties($post_id) {
    /* slider */
    for ($i = 0; $i < get_option('fifu_spinner_slider'); $i ++) {
        if (isset($_POST['fifu_slider_input_url_' . $i]) && isset($_POST['fifu_slider_input_alt_' . $i])) {
            $url = esc_url_raw($_POST['fifu_slider_input_url_' . $i]);
            $alt = wp_strip_all_tags($_POST['fifu_slider_input_alt_' . $i]);
            fifu_update_or_delete($post_id, 'fifu_slider_image_url_' . $i, $url);
            fifu_update_or_delete_value($post_id, 'fifu_slider_image_alt_' . $i, $alt);
        }
    }
}

function fifu_shortcode_save_properties($post_id) {
    /* shortcode */
    if (isset($_POST['fifu_shortcode_input'])) {
        $url = $_POST['fifu_shortcode_input'];
        $url = preg_replace("/[\"]/", "'", $url);
        fifu_update_or_delete($post_id, 'fifu_shortcode', $url);
        set_post_thumbnail($post_id, 0);
    }
}

function fifu_variation_save_properties($post_id) {
    /* variation */
    if (get_post_type(get_the_ID()) == 'product') {
        $i = 0;
        foreach (fifu_db_variantion_products($post_id) as $var) {
            $id = $var->id;
            $title = explode(' - ', $var->post_title)[1];
            if (isset($_POST['fifu_input_url_var_' . $i])) {
                $url = esc_url_raw($_POST['fifu_input_url_var_' . $i]);
                fifu_update_or_delete_var($id, 'fifu_image_url', $url);
                fifu_update_or_delete_value($id, 'fifu_image_alt', $title);
            }
            $i++;
        }
    }
}

function fifu_update_or_delete_var($post_id, $field, $url) {
    if ($url && fifu_valid_url($url))
        update_post_meta($post_id, $field, fifu_convert($url));
    else
        delete_post_meta($post_id, $field);
    fifu_update_fake_attach_id($post_id);
}

function fifu_update_or_delete($post_id, $field, $url) {
    if ($url && fifu_valid_url($url)) {
        update_post_meta($post_id, $field, $field != 'fifu_video_url' ? fifu_convert($url) : $url);
    } else
        delete_post_meta($post_id, $field, $url);
}

function fifu_update_or_delete_value($post_id, $field, $value) {
    if ($value)
        update_post_meta($post_id, $field, $value);
    else
        delete_post_meta($post_id, $field, $value);
}

add_action('pmxi_before_xml_import', 'fifu_before_xml_import', 10, 1);

function fifu_before_xml_import($import_id) {
    if (fifu_is_on('fifu_auto_category')) {
        update_option('fifu_auto_category', 'toggleoff');
        update_option('fifu_auto_category_waiting', true);
    }
}

add_action('pmxi_after_xml_import', 'fifu_after_xml_import', 10, 1);

function fifu_after_xml_import($import_id) {
    if (get_option('fifu_auto_category_waiting')) {
        update_option('fifu_auto_category', 'toggleon');
        fifu_db_insert_auto_category_image();
        update_option('fifu_auto_category_created', true, 'no');
        delete_option('fifu_auto_category_waiting');
    } else
        fifu_db_insert_attachment_category();
}

add_action('pmxi_saved_post', 'fifu_wai_save');

function fifu_wai_save($post_id) {
    $urls = rtrim(get_post_meta($post_id, 'fifu_list_url', true), '|');
    $alts = rtrim(get_post_meta($post_id, 'fifu_list_alt', true), '|');
    if ($urls) {
        $urls = explode("|", $urls);
        if ($alts)
            $alts = explode("|", $alts);
        $alts = ($alts && count($urls) == count($alts)) ? $alts : null;
        $i = 0;
        $i_alt = 0;
        $has_main_image = false;
        foreach ($urls as $url) {
            if ($alts)
                $alt = $alts[$i_alt++];

            if (!$has_main_image) {
                if (fifu_valid_url($url)) {
                    fifu_update_or_delete($post_id, 'fifu_image_url', $url);
                    fifu_update_or_delete($post_id, 'fifu_image_alt', $alt);
                    $has_main_image = true;
                }
            } else {
                if (fifu_valid_url($url)) {
                    fifu_update_or_delete($post_id, 'fifu_image_url_' . $i, $url);
                    fifu_update_or_delete($post_id, 'fifu_image_alt_' . $i, $alt);
                    $i++;
                }
            }
        }
    } else {
        $url = get_post_meta($post_id, 'fifu_image_url', true);
        $alt = get_post_meta($post_id, 'fifu_image_alt', true);
        if (!fifu_valid_url($url)) {
            delete_post_meta($post_id, 'fifu_image_url', $url);
            delete_post_meta($post_id, 'fifu_image_alt', $alt);
        } else {
            fifu_update_or_delete($post_id, 'fifu_image_url', $url);
            fifu_update_or_delete($post_id, 'fifu_image_alt', $alt);
        }
    }
}

add_action('pmxi_saved_post', 'fifu_wai_video_save');

function fifu_wai_video_save($post_id) {
    $urls = get_post_meta($post_id, 'fifu_list_video_url', true);
    if ($urls) {
        $urls = explode("|", $urls);
        $i = 0;
        $has_main_image = !empty(get_post_meta($post_id, 'fifu_image_url', true));
        foreach ($urls as $url) {
            if (!$has_main_image) {
                fifu_update_or_delete($post_id, 'fifu_video_url', $url);
                $has_main_image = true;
            } else {
                fifu_update_or_delete($post_id, 'fifu_video_url_' . $i, $url);
                $i++;
            }
        }
    } else {
        $url = get_post_meta($post_id, 'fifu_video_url', true);
        if ($url)
            fifu_update_or_delete($post_id, 'fifu_video_url', $url);
    }
}

add_action('pmxi_saved_post', 'fifu_slider_wai_save');

function fifu_slider_wai_save($post_id) {
    $list = get_post_meta($post_id, 'fifu_slider_list_url', true);
    if ($list) {
        $list = explode("|", $list);
        $i = 0;
        foreach ($list as $url)
            fifu_update_or_delete($post_id, 'fifu_slider_image_url_' . $i++, $url);
    }
    fifu_update_fake_attach_id($post_id);
}

add_action('before_delete_post', 'fifu_db_before_delete_post');

add_action('wp_trash_post', 'fifu_db_delete_category_image');

/* product variation, save metadata */

add_action('woocommerce_rest_insert_product_variation_object', 'fifu_save_product_variation', 10, 3);

function fifu_save_product_variation($object, $request, $insert) {
    fifu_save_product($object, $request, $insert);
}

add_action('woocommerce_rest_insert_product_object', 'fifu_save_product', 10, 3);

function fifu_save_product($object, $request, $insert) {
    $post_id = $object->get_id();
    $alts = null;
    $urls = null;

    foreach ($object->get_meta_data() as $data) {
        if ($data->key == 'fifu_list_alt')
            $alts = $data->value;
        if ($data->key == 'fifu_list_url')
            $urls = $data->value;
    }

    $urls = explode("|", $urls);
    $alts = explode("|", $alts);

    if ($insert)
        fifu_db_insert($post_id, $urls, $alts);
    else
        fifu_db_update($post_id, $urls, $alts);
}

add_action('woocommerce_rest_insert_product_cat', 'fifu_save_product_category', 10, 2);

function fifu_save_product_category($object, $request) {
    $body = json_decode($request->get_body());
    if (!$body || !$body->meta_data)
        return;

    $term_id = $object->term_id;

    foreach ($body->meta_data as $meta) {
        if ($meta->key == 'fifu_image_url' || $meta->key == 'fifu_image_alt')
            update_term_meta($term_id, $meta->key, $meta->value);
    }
    fifu_db_ctgr_update_fake_attach_id($term_id);
}

/* product variation, show gallery */

add_filter('woocommerce_before_single_product_summary', 'fifu_woocommerce_show_product_images', 20);

function fifu_woocommerce_show_product_images($a) {
    global $product;
    $aux = explode("?", "$_SERVER[REQUEST_URI]");
    $aux = count($aux) > 1 ? $aux[1] : null;
    $aux = explode("=", $aux);
    $meta_key = count($aux) > 0 ? $aux[0] : null;
    $meta_value = count($aux) > 1 ? $aux[1] : null;
    $ids = fifu_db_image_ids($meta_key, $meta_value, $product->get_id());
    if ($ids) {
        $aux = explode(",", $ids);
        $thumbnail_id = $aux[0];
        $aux = explode(',', $ids, 2);
        $gallery_ids = count($aux) > 1 ? $aux[1] : $aux;
        $product->set_image_id($thumbnail_id);
        $product->set_gallery_image_ids($gallery_ids);
    }
}

/* regular woocommerce import */

add_action('woocommerce_product_import_inserted_product_object', 'fifu_woocommerce_import');

function fifu_woocommerce_import($object) {
    $post_id = $object->get_id();
    fifu_wai_save($post_id);
    fifu_update_fake_attach_id($post_id);
}

/* wcfm */

function fifu_is_wcfm_activate() {
    return is_plugin_active('wc-frontend-manager/wc_frontend_manager.php');
}


function fifu_get_wcfm_url($content) {
    $url = explode('fifu_image_url=', $content)[1];
    return $url ? urldecode(explode('&', $url)[0]) : null;
}

