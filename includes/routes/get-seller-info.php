<?php

function riothere_get_seller_info_api()
{
    register_rest_route('riothere/v1', 'seller', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $seller_id = $params['seller_id'];

            if (empty($seller_id)) {
                return [];
            }

            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            ];

            // Only get `listed` products
            $args['meta_query'] = array(
                'relation' => "AND",
                array(
                    'key' => 'status',
                    'value' => 'listed',
                    'compare' => '=',
                ),
            );

            $args['meta_query'] = array(
                'relation' => "AND",
                array(
                    'key' => 'seller',
                    'value' => esc_attr($seller_id),
                    'compare' => '=',
                ),
            );

            $products_count_query = new WP_Query($args);

            $customer = new WC_Customer($seller_id);
            $seller_nickname_arr = get_user_meta($seller_id)['seller_nickname'];
            $followers = Riothere_All_In_One_Followers::get_seller_followers($seller_id);
            $following = Riothere_All_In_One_Followers::get_seller_follows($seller_id);

            $user_to_return = [
                'id' => $seller_id,
                'first_name' => $customer->get_first_name(),
                'last_name' => $customer->get_last_name(),
                'seller_nickname' => $seller_nickname_arr ? $seller_nickname_arr[0] : "",
                'date_joined' => $customer->get_date_created()->date('F, jS, Y'),
                'products_number' => $products_count_query->found_posts,
                'followers_count' => count($followers),
                'following_count' => count($following),
            ];

            return $user_to_return;

        }));
}

add_action('rest_api_init', 'riothere_get_seller_info_api');
