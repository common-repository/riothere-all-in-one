<?php
// Creates Refund Requests Custom Post Type
function riothere_time_slots_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Try&Buy Time Slots',
            'singular_name' => 'Try&Buy Time Slot',

        ),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => false,
        'capabilities' => array(
            'create_posts' => true,
        ),
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'time-slot'),
        'query_var' => true,
        // 'menu_icon' => 'dashicons-editor-help',
        'supports' => array(
            'custom-fields',
        ),

    );
    register_post_type('time-slots', $args);
}
add_action('init', 'riothere_time_slots_init');

function riothere_remove_wp_seo_meta_box_time_slots()
{
    remove_meta_box('wpseo_meta', 'time-slots', 'normal');
}
add_action('add_meta_boxes', 'riothere_remove_wp_seo_meta_box_time_slots', 100);

function riothere_disable_yoast_seo_metabox_time_slots($post_types)
{
    unset($post_types['time-slots']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_metabox_time_slots');

function riothere_add_column_to_time_slots_table($columns)
{
    $columns_array = array(
        'day' => 'Day',
        'from_time' => 'From Time',
        'to_time' => 'To Time',
    );

    foreach ($columns_array as $key => $value) {
        $columns[$key] = $value;
    }

    return $columns;
}
add_filter('manage_edit-time-slots_columns', 'riothere_add_column_to_time_slots_table', 999);

add_action('manage_time-slots_posts_custom_column', 'riothere_add_time_slot_content_to_column', 10, 2);
function riothere_add_time_slot_content_to_column($column, $time_slot_id)
{
    echo esc_textarea(get_field($column, $time_slot_id));
}
