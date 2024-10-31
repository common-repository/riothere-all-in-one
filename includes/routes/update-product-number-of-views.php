<?php

function riothere_update_product_number_of_views()
{
    register_rest_route('riothere/v1', 'update-product-number-of-views', array(
        'methods' => 'PATCH',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $productId = $request->get_param('product_id');
            $numberOfViews = (int) get_post_meta($productId, 'number_of_views', true);
            update_post_meta($productId, 'number_of_views', ($numberOfViews + 1));
            return true;
        },
    ));
}

add_action('rest_api_init', 'riothere_update_product_number_of_views');
