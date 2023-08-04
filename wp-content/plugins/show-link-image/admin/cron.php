<?php

function fifu_add_cron_schedules($schedules) {
    if (!isset($schedules["fifu_schedule_metadata"])) {
        $schedules['fifu_schedule_metadata'] = array(
            'interval' => get_option('fifu_spinner_cron_metadata') * 60,
            'display' => __('fifu-parameter')
        );
    }
    return $schedules;
}

add_filter('cron_schedules', 'fifu_add_cron_schedules');

function fifu_create_metadata_hook() {
    fifu_db_insert_attachment();
    if (fifu_is_on('fifu_auto_category'))
        fifu_db_insert_auto_category_image();
}

add_action('fifu_create_metadata_event', 'fifu_create_metadata_hook');

