<?php

/*
 * Plugin Name: Woo POD Master - Show Image Url
 * Description: Hiển thị ảnh sản phẩm từ link ngoài (không cần upload lên site)
 * Version: 1.2.0
 * Author: GiaiPhapMMO.net
 * Plugin URI: https://www.facebook.com/giaiphapmmodotnet
 */
define('FIFU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FIFU_INCLUDES_DIR', FIFU_PLUGIN_DIR . 'includes');
define('FIFU_ADMIN_DIR', FIFU_PLUGIN_DIR . 'admin');

require_once (FIFU_INCLUDES_DIR . '/attachment.php');
require_once (FIFU_INCLUDES_DIR . '/convert-url.php');
require_once (FIFU_INCLUDES_DIR . '/external-post.php');
require_once (FIFU_INCLUDES_DIR . '/flickr.php');
require_once (FIFU_INCLUDES_DIR . '/genesis.php');
require_once (FIFU_INCLUDES_DIR . '/rest.php');
require_once (FIFU_INCLUDES_DIR . '/shortcode.php');
require_once (FIFU_INCLUDES_DIR . '/speedup.php');
require_once (FIFU_INCLUDES_DIR . '/thumbnail.php');
require_once (FIFU_INCLUDES_DIR . '/thumbnail-category.php');
require_once (FIFU_INCLUDES_DIR . '/util.php');
require_once (FIFU_INCLUDES_DIR . '/video.php');
require_once (FIFU_INCLUDES_DIR . '/woo.php');

require_once (FIFU_ADMIN_DIR . '/api.php');
require_once (FIFU_ADMIN_DIR . '/category.php');
require_once (FIFU_ADMIN_DIR . '/column.php');
require_once (FIFU_ADMIN_DIR . '/cron.php');
require_once (FIFU_ADMIN_DIR . '/db.php');
require_once (FIFU_ADMIN_DIR . '/menu.php');
require_once (FIFU_ADMIN_DIR . '/meta-box.php');
require_once (FIFU_ADMIN_DIR . '/rsa.php');

register_activation_hook(__FILE__, 'fifu_activate');

function fifu_activate($network_wide) {
    if (is_multisite() && $network_wide) {
        global $wpdb;
        foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
            switch_to_blog($blog_id);
            fifu_activate_actions();
        }
    } else {
        fifu_activate_actions();
    }
}

function fifu_activate_actions() {
    update_option('fifu_update_all', 'toggleoff', 'no');
    fifu_db_change_url_length();
}

register_deactivation_hook(__FILE__, 'fifu_deactivation');

function fifu_deactivation() {
    wp_clear_scheduled_hook('fifu_create_metadata_event');
}

add_action('upgrader_process_complete', 'fifu_upgrade', 10, 2);

function fifu_upgrade($upgrader_object, $options) {
    $current_plugin_path_name = plugin_basename(__FILE__);
    if ($options['action'] == 'update' && $options['type'] == 'plugin') {
        foreach ($options['plugins'] as $each_plugin) {
            if ($each_plugin == $current_plugin_path_name) {
                fifu_db_change_url_length();
                fifu_db_update_autoload();
                fifu_db_delete_deprecated_data();
            }
        }
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'fifu_action_links');
add_filter('network_admin_plugin_action_links_' . plugin_basename(__FILE__), 'fifu_action_links');

function fifu_action_links($links) {
    $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=show-image-link')) . '">Settings</a>';
    //$links[] = '<a style="color:black">Support Email:</a>';
    //$links[] = '<br><center style="width:275px;color:white;background-color:#02a0d2;border-radius:0px 30px">support@featuredimagefromurl.com</center>';
    return $links;
}

add_action('fifu_event', 'fifu_event_function');

function fifu_event_function() {
    require_once (FIFU_ADMIN_DIR . '/menu.php');
    update_option('fifu_update_all', 'toggleon', 'no');
    fifu_get_menu_html();
}

//require 'plugin_update_check.php';
//$MyUpdateChecker = new PluginUpdateChecker_2_0('https://kernl.us/api/v1/updates/5bd5998830ea77353f8bedeb/', __FILE__, 'fifu-premium', 1);
//$MyUpdateChecker->license = get_option("fifu_key");
//$MyUpdateChecker->licenseErrorMessage = "Your license is invalid or expired. Send an email to support@featuredimagefromurl.com";
//$MyUpdateChecker->remoteGetTimeout = 10;

