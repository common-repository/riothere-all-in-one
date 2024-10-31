<?php
// Creates Refund Requests Custom Post Type
function riothere_cart_discount_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Cart Promotions',
            'singular_name' => 'Cart Promotion',
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => false,
        'capabilities' => array(
            'create_posts' => true,
        ),
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'cart-discounts'),
        'query_var' => true,
        'supports' => array(
            'custom-fields',
        ),
        'show_in_menu' => 'woocommerce-marketing',
        'menu_position' => 5,
    );

    register_post_type('cart-discounts', $args);
}
add_action('init', 'riothere_cart_discount_init');

function riothere_remove_wp_seo_meta_box_cart_discounts()
{
    remove_meta_box('wpseo_meta', 'cart-discounts', 'normal');
}
add_action('add_meta_boxes', 'riothere_remove_wp_seo_meta_box_cart_discounts', 100);

function riothere_disable_yoast_seo_metabox_cart_discounts($post_types)
{
    unset($post_types['cart-discounts']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_metabox_cart_discounts');

function riothere_add_column_to_cart_discounts_table($columns)
{
    $columns_array = array(
        'value_of_cart' => 'Value of Cart (AED)',
        'discount' => 'Discount (%)',
    );

    foreach ($columns_array as $key => $value) {
        $columns[$key] = $value;
    }

    return $columns;
}
add_filter('manage_edit-cart-discounts_columns', 'riothere_add_column_to_cart_discounts_table', 999);

add_action('manage_cart-discounts_posts_custom_column', 'riothere_add_cart_discounts_content_to_column', 10, 2);
function riothere_add_cart_discounts_content_to_column($column, $cart_discounts_id)
{
    echo esc_textarea(get_field($column, $cart_discounts_id));
}
