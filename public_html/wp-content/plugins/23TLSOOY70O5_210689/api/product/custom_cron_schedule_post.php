<?php
// Copy vào function.php
define('WPMS_DELAY', 0.5);  // Run the below cron task every X minutes
define('WPMS_OPTION', 'wp_missed_schedule');

function wpms_replacements_deactivate() {
    delete_option(WPMS_OPTION);
}
register_deactivation_hook(__FILE__, 'wpms_replacements_deactivate');

// Run the following code on every request
function wpms_init() {
    remove_action('publish_future_post', 'check_and_publish_future_post');
    $last = get_option(WPMS_OPTION, false);

    // Exit here if less than WPMS_DELAY minutes has passed since we last ran
    if (($last !== false) && ($last > (time() - (WPMS_DELAY * 60))))
        return;

    // Find all posts whose scheduled time has passed and publish them
    update_option(WPMS_OPTION, time());
    global $wpdb;
    $scheduledIDs = $wpdb->get_col("
        SELECT `ID` FROM `{$wpdb->posts}`
        WHERE (
          ((`post_date` > 0) AND (`post_date` <= CURRENT_TIMESTAMP()))
          OR ((`post_date_gmt` > 0) AND (`post_date_gmt` <= UTC_TIMESTAMP()))
        )
        AND `post_status` = 'future'
        LIMIT 0, 10
    ");
    if (!count($scheduledIDs))
      return;
    foreach ($scheduledIDs as $scheduledID) {
        if (!$scheduledID) continue;
        wp_publish_post($scheduledID);
    }
}
add_action('init', 'wpms_init', 0)
?>