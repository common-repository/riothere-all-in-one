<?php
function riothere_buttons_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Buttons',
            'singular_name' => 'Button',
            'add_new_item' => 'Add New Button',
            'edit_item' => 'Edit Button',
            'all_items' => 'All Buttons',
        ),
        'public' => true,
        'show_ui' => true,
        'rewrite' => array('slug' => 'buttons'),
        'query_var' => true,
        'menu_icon' => 'dashicons-button',
        'menu_position' => 3,
        'show_in_menu' => 'homepage_control',
    );
    register_post_type('buttons', $args);
}
add_action('init', 'riothere_buttons_init');

function riothere_disable_yoast_seo_meta_box_buttons($post_types)
{
    unset($post_types['buttons']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_meta_box_buttons');

function riothere_remove_editor_from_buttons_post_type()
{
    remove_post_type_support('buttons', 'editor');
}

add_action('init', 'riothere_remove_editor_from_buttons_post_type');
