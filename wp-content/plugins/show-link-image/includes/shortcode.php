<?php

function fifu_enqueue_scripts() {
    if (fifu_is_on('fifu_shortcode')) {
        wp_enqueue_script('fifu_js_shortcode', plugins_url('includes/html/js/shortcode.js', dirname(__FILE__)), array('jquery'));
        wp_localize_script('fifu_js_shortcode', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
}

add_action('wp_enqueue_scripts', 'fifu_enqueue_scripts');

function fifu_callback_shortcode() {
    $post_id = $_POST["id"];
    $shortcode = get_post_meta($post_id, 'fifu_shortcode', true);
    if ($shortcode) {
        $longcode = do_shortcode($shortcode);
        $send_back = array("longcode" => $longcode);
        echo json_encode($send_back);
    }
    die();
}

add_action('wp_ajax_' . 'fifu_callback_shortcode', 'fifu_callback_shortcode');
add_action('wp_ajax_nopriv_' . 'fifu_callback_shortcode', 'fifu_callback_shortcode');

