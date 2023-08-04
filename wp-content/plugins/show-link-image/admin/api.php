<?php

define('FIFU_TRY_AGAIN_LATER', json_encode(array('code' => 0, 'message' => 'try again later', 'color' => 'orange')));

function fifu_api_image_url(WP_REST_Request $request) {
    $param = $request['post_id'];
    return fifu_main_image_url($param);
}

function fifu_api_sign_up(WP_REST_Request $request) {
    $first_name = $request['first_name'];
    $last_name = $request['last_name'];
    $email = $request['email'];
    $site = $_SERVER['SERVER_NAME'];
    $response = wp_safe_remote_post('https://cdn.featuredimagefromurl.com/sign-up/', array(
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode(array('site' => $site, 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'public_key' => fifu_create_keys($email))),
        'method' => 'POST',
        'data_format' => 'body',
        'blocking' => true,
        'timeout' => 10,
    ));
    if (is_wp_error($response)) {
        fifu_delete_credentials();
        return json_decode(FIFU_TRY_AGAIN_LATER);
    }

    $json = json_decode($response['http_response']->get_response_object()->body);
    if ($json->code <= 0)
        fifu_delete_credentials();

    return $json;
}

function fifu_delete_credentials() {
    delete_option('fifu_su_privkey');
    delete_option('fifu_su_email');
    delete_option('fifu_su_logged_in');
}

function fifu_api_login(WP_REST_Request $request) {
    $email = $request['email'];
    $site = $_SERVER['SERVER_NAME'];
    $time = time();
    $signature = fifu_create_signature($email, $time);
    $response = wp_safe_remote_post('https://cdn.featuredimagefromurl.com/login/', array(
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode(array('site' => $site, 'email' => $email, 'signature' => $signature, 'time' => $time)),
        'method' => 'POST',
        'data_format' => 'body',
        'blocking' => true,
        'timeout' => 10,
    ));
    if (is_wp_error($response))
        return json_decode(FIFU_TRY_AGAIN_LATER);

    $json = json_decode($response['http_response']->get_response_object()->body);
    update_option('fifu_su_logged_in', $json->code > 0, false);

    return $json;
}

function fifu_api_create_thumbnails(WP_REST_Request $request) {
    $url = esc_url_raw($request['url']);
    $post_id = $request['post_id'];
    $time = time();

    if (!$url || !$post_id)
        return $url;

    $site = $_SERVER['SERVER_NAME'];
    $signature = fifu_create_signature(null, $time);
    $response = wp_safe_remote_post('https://cdn.featuredimagefromurl.com/create-thumbnails/', array(
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode(array('url' => $url, 'site' => $site, 'post_id' => $post_id, 'signature' => $signature, 'time' => $time)),
        'method' => 'POST',
        'data_format' => 'body',
        'blocking' => true,
        'timeout' => 60,
    ));
    if (is_wp_error($response))
        return $url;

    $json = json_decode($response['http_response']->get_response_object()->body);
    $code = $json->code;
    if ($code && $code > 0)
        return $json->url;

    return $url;
}

function fifu_api_delete(WP_REST_Request $request) {
    $set = $request['ids'];
    foreach ($set as $id) {
        $time = time();
        $site = $_SERVER['SERVER_NAME'];
        $signature = fifu_create_signature(null, $time);
        $response = wp_safe_remote_post('https://cdn.featuredimagefromurl.com/delete/', array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode(array('site' => $site, 'hex_id' => $id, 'signature' => $signature, 'time' => $time)),
            'method' => 'POST',
            'data_format' => 'body',
            'blocking' => true,
            'timeout' => 60,
        ));
        if (is_wp_error($response))
            return json_decode(FIFU_TRY_AGAIN_LATER);

        $json = json_decode($response['http_response']->get_response_object()->body);
    }
    return $json;
}

function fifu_api_reset_credentials(WP_REST_Request $request) {
    fifu_delete_credentials();
    $email = $request['email'];
    $site = $_SERVER['SERVER_NAME'];
    $response = wp_safe_remote_post('https://cdn.featuredimagefromurl.com/reset-credentials/', array(
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode(array('site' => $site, 'email' => $email, 'public_key' => fifu_create_keys($email))),
        'method' => 'POST',
        'data_format' => 'body',
        'blocking' => true,
        'timeout' => 10,
    ));
    return is_wp_error($response) ? json_decode(FIFU_TRY_AGAIN_LATER) : json_decode($response['http_response']->get_response_object()->body);
}

function fifu_api_list_all_su(WP_REST_Request $request) {
    $time = time();
    $site = $_SERVER['SERVER_NAME'];
    $signature = fifu_create_signature(null, $time);
    $response = wp_safe_remote_post('https://cdn.featuredimagefromurl.com/list-all/', array(
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode(array('site' => $site, 'signature' => $signature, 'time' => $time)),
        'method' => 'POST',
        'data_format' => 'body',
        'blocking' => true,
        'timeout' => 10,
    ));
    if (is_wp_error($response))
        return json_decode(FIFU_TRY_AGAIN_LATER);

    $json = json_decode($response['http_response']->get_response_object()->body);
    return $json;
}

function fifu_api_list_all_fifu(WP_REST_Request $request) {
    return fifu_get_all_urls();
}

function fifu_enable_fake_api(WP_REST_Request $request) {
    fifu_enable_fake();
}

function fifu_disable_fake_api(WP_REST_Request $request) {
    fifu_disable_fake();
    delete_option('fifu_fake_attach_id');
}

function fifu_none_fake_api(WP_REST_Request $request) {
    update_option('fifu_fake_created', null, 'no');
}

function fifu_data_clean_api(WP_REST_Request $request) {
    fifu_db_enable_clean();
    update_option('fifu_data_clean', 'toggleoff', 'no');
}

function fifu_update_all_api(WP_REST_Request $request) {
    update_option('fifu_update_all', 'toggleoff', 'no');
    fifu_db_update_all();
}

function fifu_save_dimensions_all_api(WP_REST_Request $request) {
    update_option('fifu_save_dimensions_all', 'toggleoff', 'no');

    if (fifu_is_off('fifu_save_dimensions'))
        return;

    fifu_db_save_dimensions_all();
}

function fifu_clean_dimensions_all_api(WP_REST_Request $request) {
    update_option('fifu_clean_dimensions_all', 'toggleoff', 'no');

    if (fifu_is_off('fifu_clean_dimensions'))
        return;

    fifu_db_clean_dimensions_all();
}

function fifu_test_execution_time() {
    for ($i = 0; $i <= 120; $i++) {
        error_log($i);
        sleep(1);
    }
}

add_action('rest_api_init', function () {
    register_rest_route('fifu-premium/v2', '/enable_fake_api/', array(
        'methods' => 'POST',
        'callback' => 'fifu_enable_fake_api'
    ));
    register_rest_route('fifu-premium/v2', '/disable_fake_api/', array(
        'methods' => 'POST',
        'callback' => 'fifu_enable_fake_api'
    ));
    register_rest_route('fifu-premium/v2', '/none_fake_api/', array(
        'methods' => 'POST',
        'callback' => 'fifu_none_fake_api'
    ));
    register_rest_route('fifu-premium/v2', '/data_clean_api/', array(
        'methods' => 'POST',
        'callback' => 'fifu_data_clean_api'
    ));
    register_rest_route('fifu-premium/v2', '/update_all_api/', array(
        'methods' => 'POST',
        'callback' => 'fifu_update_all_api'
    ));
    register_rest_route('fifu-premium/v2', '/save_dimensions_all_api/', array(
        'methods' => 'POST',
        'callback' => 'fifu_save_dimensions_all_api'
    ));
    register_rest_route('fifu-premium/v2', '/clean_dimensions_all_api/', array(
        'methods' => 'POST',
        'callback' => 'fifu_clean_dimensions_all_api'
    ));

    register_rest_route('fifu-premium/v1', '/url/(?P<post_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'fifu_api_image_url'
    ));
    register_rest_route('fifu-premium/v2', '/create_thumbnails/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_create_thumbnails'
    ));
    register_rest_route('fifu-premium/v2', '/sign_up/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_sign_up'
    ));
    register_rest_route('fifu-premium/v2', '/login/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_login'
    ));
    register_rest_route('fifu-premium/v2', '/reset_credentials/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_reset_credentials'
    ));
    register_rest_route('fifu-premium/v2', '/list_all_su/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_list_all_su'
    ));
    register_rest_route('fifu-premium/v2', '/list_all_fifu/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_list_all_fifu'
    ));
    register_rest_route('fifu-premium/v2', '/delete/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_delete'
    ));
    register_rest_route('fifu-premium/v2', '/add/', array(
        'methods' => 'POST',
        'callback' => 'fifu_api_add'
    ));
});

