<?php
// Creates Refund Requests Custom Post Type
function riothere_transit_times_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Transit Times',
            'singular_name' => 'Transit Time',
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'capabilities' => array(
            'create_posts' => true,
        ),
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'transit-times'),
        'query_var' => true,
        'menu_icon' => 'dashicons-airplane',
        'supports' => array(
            'custom-fields',
        ),
        'menu_position' => 55,
    );
    register_post_type('transit-times', $args);
}
add_action('init', 'riothere_transit_times_init');

function riothere_remove_wp_seo_meta_box_transit_times()
{
    remove_meta_box('wpseo_meta', 'transit-times', 'normal');
}
add_action('add_meta_boxes', 'riothere_remove_wp_seo_meta_box_transit_times', 100);

function riothere_disable_yoast_seo_metabox_transit_times($post_types)
{
    unset($post_types['transit-times']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_metabox_transit_times');

add_filter('manage_edit-transit-times_columns', 'riothere_add_column_to_transit_times_table', 999);
function riothere_add_column_to_transit_times_table($columns)
{
    $columns_array = array(
        'destination_country' => 'Destination Country',
        'transit_time_days' => 'Transit Time (days)',
    );

    foreach ($columns_array as $key => $value) {
        $columns[$key] = $value;
    }

    return $columns;
}

add_action('manage_transit-times_posts_custom_column', 'riothere_add_transit_time_content_to_column', 10, 2);
function riothere_add_transit_time_content_to_column($column, $transit_time_id)
{
    echo esc_textarea(get_field($column, $transit_time_id));
}
