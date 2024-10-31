<?php

function riothere_get_riot_categories()
{
    register_rest_route('riothere/v1', 'categories', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $args = array(
                'taxonomy' => 'product_cat',
                // 'orderby'      => $orderby,
                'show_count' => 1,
                'pad_counts' => 0,
                'hierarchical' => 1,
                // 'title_li'     => $title,
                // 'hide_empty'   => $empty
            );

            return get_categories($args);
        }));
}

add_action('rest_api_init', 'riothere_get_riot_categories');
