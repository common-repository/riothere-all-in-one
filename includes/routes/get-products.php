<?php

function riothere_products_api()
{
    register_rest_route('riothere/v1', 'products', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $categories = json_decode($params['categories']);
            $per_page = $params['per_page'];
            $offset = $params['offset'];
            $order = $params['order'];
            $orderby = $params['order_by'];
            $image_size = $params['image_size'];
            $min_price = json_decode($params['min_price']);
            $max_price = json_decode($params['max_price']);
            $seller_id = json_decode($params['seller_id']);
            $product_ids = json_decode($params['product_ids']);

            if (!$image_size) {
                $image_size = "medium";
            }

            $products_result = Riothere_All_In_One_Products::get_products(
                $categories,
                $per_page,
                $offset,
                $order,
                strtoupper($orderby),
                $image_size,
                $min_price,
                $max_price,
                $seller_id,
                null,
                null,
                "Listed",
                $product_ids
            );

            return $products_result;
        }));
}

add_action('rest_api_init', 'riothere_products_api');
