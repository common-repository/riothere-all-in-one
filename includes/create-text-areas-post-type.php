<?php
function riothere_text_areas_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Text Areas',
            'singular_name' => 'Text Area',
            'add_new_item' => 'Add New Text Area',
            'edit_item' => 'Edit Text Area',
            'all_items' => 'All Text Areas',
        ),
        'public' => true,
        'show_ui' => true,
        'rewrite' => array('slug' => 'text-areas'),
        'query_var' => true,
        'menu_icon' => 'dashicons-text',
        'menu_position' => 4,
        'show_in_menu' => 'homepage_control',

    );
    register_post_type('text-areas', $args);
}
add_action('init', 'riothere_text_areas_init');

function riothere_disable_yoast_seo_meta_box_text_areas($post_types)
{
    unset($post_types['text-areas']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_meta_box_text_areas');
