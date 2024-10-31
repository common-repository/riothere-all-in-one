<?php
function riothere_sections_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Sections',
            'singular_name' => 'Section',
            'add_new_item' => 'Add New Section',
            'edit_item' => 'Edit Section',
            'all_items' => 'All Sections',
        ),
        'public' => true,
        'show_ui' => true,
        'rewrite' => array('slug' => 'sections'),
        'query_var' => true,
        'menu_icon' => 'dashicons-align-wide',
        'menu_position' => 2,
        'show_in_menu' => 'homepage_control',

    );
    register_post_type('sections', $args);
}
add_action('init', 'riothere_sections_init');

function riothere_disable_yoast_seo_meta_box_sections($post_types)
{
    unset($post_types['sections']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_meta_box_sections');

function riothere_remove_editor_from_sections_post_type()
{
    remove_post_type_support('sections', 'editor');
}

add_action('init', 'riothere_remove_editor_from_sections_post_type');
