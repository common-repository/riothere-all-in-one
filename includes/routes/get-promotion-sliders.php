<?php

// get_promotion_sliders

function riothere_get_promotion_sliders_api()
{
    register_rest_route('riothere/v1', 'promotion-sliders', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {

            $promotion_sliders = Riothere_All_In_One_Promotions::get_promotion_sliders();

            $data = [];
            foreach ($promotion_sliders as $promotion_slider) {
                $slider_data = [
                    'title' => $promotion_slider['title'],
                ];
                $product_ids = $promotion_slider['product_ids'];
                $products = [];

                foreach ($product_ids as $product_id) {
                    $products[] = Riothere_All_In_One_Admin::get_product_data($product_id);
                }
                $slider_data['products'] = $products;
                $data[] = $slider_data;
            }

            return $data;
        }
    ));
}

add_action('rest_api_init', 'riothere_get_promotion_sliders_api');
