<?php
function riothere_slider_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Slideshow',
            'singular_name' => 'Slideshow',
            'add_new_item' => 'Add New Slideshow',
            'edit_item' => 'Edit Slideshow',
            'all_items' => 'Slideshow',
        ),
        'public' => true,
        'show_ui' => true,
        'rewrite' => array('slug' => 'slider'),
        'query_var' => true,
        'menu_icon' => 'dashicons-slides',
        'menu_position' => 1,
        'show_in_menu' => 'homepage_control',

    );
    register_post_type('slider', $args);
}
add_action('init', 'riothere_slider_init');

function riothere_disable_yoast_seo_meta_box_slider($post_types)
{
    unset($post_types['slider']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_meta_box_slider');

function riothere_remove_editor_from_slider_post_type()
{
    remove_post_type_support('slider', 'editor');
}

add_action('init', 'riothere_remove_editor_from_slider_post_type');
