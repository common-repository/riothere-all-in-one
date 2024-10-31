<?php

function riothere_get_sellers_api()
{
    register_rest_route('riothere/v1', 'sellers', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();

            $per_page = $params['per_page'];
            $paged = $params['page'];
            $offset = ($paged - 1) * $per_page;

            $args = array(
                'role' => 'seller',
                "number" => $per_page,
                "offset" => $offset,

            );
            $users = get_users($args);

            $users_to_return = [];

            foreach ($users as $user) {
                $customer = new WC_Customer($user->ID);
                $seller_nickname_arr = get_user_meta($user->ID)['seller_nickname'];
                $followers = Riothere_All_In_One_Followers::get_seller_followers($user->ID);
                $following = Riothere_All_In_One_Followers::get_seller_follows($user->ID);

                $users_to_return[] = [
                    'id' => $user->ID,
                    'first_name' => $customer->get_first_name(),
                    'last_name' => $customer->get_last_name(),
                    'seller_nickname' => $seller_nickname_arr ? $seller_nickname_arr[0] : "",
                    'followers_count' => count($followers),
                    'following_count' => count($following),
                ];
            }

            return $users_to_return;

        }));
}

add_action('rest_api_init', 'riothere_get_sellers_api');
