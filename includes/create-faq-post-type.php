<?php
// Creates Refund Requests Custom Post Type
function riothere_faqs_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Faqs',
            'singular_name' => 'Faq',

        ),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => false,
        'capabilities' => array(
            'create_posts' => true,
        ),
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'faq'),
        'query_var' => true,
        'menu_icon' => 'dashicons-editor-help',
        'supports' => array(
            'custom-fields',
        ),

    );
    register_post_type('faqs', $args);
}
add_action('init', 'riothere_faqs_init');

function riothere_remove_wp_seo_meta_box_faqs()
{
    remove_meta_box('wpseo_meta', 'faqs', 'normal');
}
add_action('add_meta_boxes', 'riothere_remove_wp_seo_meta_box_faqs', 100);

function riothere_disable_yoast_seo_metabox_faqs($post_types)
{
    unset($post_types['faqs']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_metabox_faqs');

function riothere_add_column_to_faqs_table($columns)
{
    $columns_array = array(
        'question' => 'Question',
        'answer' => 'Answer',
        'faq_type' => 'Type',
        'show_on_contact_us' => 'Show On Contact Us Page',
        'order' => 'Order',
    );

    foreach ($columns_array as $key => $value) {
        $columns[$key] = $value;
    }

    return $columns;
}
add_filter('manage_edit-faqs_columns', 'riothere_add_column_to_faqs_table', 999);

add_action('manage_faqs_posts_custom_column', 'riothere_add_faq_content_to_column', 10, 2);
function riothere_add_faq_content_to_column($column, $faq_id)
{
    echo esc_textarea(get_field($column, $faq_id));
}
