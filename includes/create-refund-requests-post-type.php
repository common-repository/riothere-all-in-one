<?php
// Creates Refund Requests Custom Post Type
function riothere_refund_requests_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Refund Requests',
            'singular_name' => 'Refund Request',
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'capabilities' => array(
            'create_posts' => false,
        ),
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'refund-requests'),
        'query_var' => true,
        'menu_icon' => 'dashicons-controls-repeat',
        'supports' => array(
            'custom-fields',
        ),
        'menu_position' => 56,
    );
    register_post_type('refund-requests', $args);
}
add_action('init', 'riothere_refund_requests_init');

function riothere_remove_wp_seo_meta_box()
{
    remove_meta_box('wpseo_meta', 'refund-requests', 'normal');
}
add_action('add_meta_boxes', 'riothere_remove_wp_seo_meta_box', 100);

function riothere_disable_yoast_seo_metabox($post_types)
{
    unset($post_types['refund-requests']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_metabox');

add_filter('manage_edit-refund-requests_columns', 'riothere_add_column_to_refund_table', 999);
function riothere_add_column_to_refund_table($columns)
{
    $columns_array = array(
        'customer' => 'Customer',
        'order_id' => 'Order',
        'products' => 'Products',
        'status' => 'Status',
        'reasons' => 'Reason',
    );
    foreach ($columns_array as $key => $value) {
        $columns[$key] = $value;
    }
    return $columns;
}

add_action('manage_refund-requests_posts_custom_column', 'riothere_add_refund_content_to_column', 10, 2);
function riothere_add_refund_content_to_column($column, $refund_id)
{

    if ($column == 'order_id') {
        $value = get_field('order_id', $refund_id);
        $url = $value['url'];
        $title = $value['title'];
        echo '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($title) . '</a>';
    }

    if ($column == 'customer') {
        $value = get_field('customer', $refund_id);
        $url = $value['url'];
        $title = $value['title'];
        echo '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($title) . '</a>';
    }

    if ($column == 'products') {
        $products = get_field('products', $refund_id);
        $product_labels = [];

        if (is_array($products)) {
            foreach ($products as $value) {
                $product_id = $value["product_id"]["title"];
                $url = $value["product_id"]["url"];
                array_push(
                    $product_labels,
                    '<a href="' . $url . '" target="_blank">' . $product_id . '</a>'
                );
            }
        }

        echo esc_textarea(join(", ", $product_labels));
    }

    if ($column == 'status') {
        $statuses = get_field('status', $refund_id);
        echo is_array($statuses) ? esc_textarea($statuses[0]) : esc_textarea($statuses);
    }

    if ($column == 'reasons') {
        $reasons = get_field('reasons', $refund_id);
        echo is_array($reasons) ? esc_textarea($reasons[0]) : esc_textarea($reasons);
    }

}

function count_pending_refund_requests()
{
    $posts = get_posts(array('post_type' => 'refund-requests'));
    $count = 0;

    foreach ($posts as $post) {
        if (get_post_meta($post->ID, "status")[0][0] === "pending") {
            $count++;
        }

    }

    return $count;
}

// Adds number of pending requests in a bubble/badge in Left Menu
add_action('admin_menu', 'riothere_add_user_menu_bubble_refund_requests');
function riothere_add_user_menu_bubble_refund_requests()
{
    global $menu;
    $number_of_pending_requests = count_pending_refund_requests();

    if ($number_of_pending_requests) {
        foreach ($menu as $key => $value) {
            if ($menu[$key][2] == 'edit.php?post_type=refund-requests') {
                $menu[$key][0] .= ' <span class="awaiting-mod">' . $number_of_pending_requests . '</span>';
                return;
            }
        }
    }
}
