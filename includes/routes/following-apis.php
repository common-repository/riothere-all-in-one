<?php

// check following status for a specific seller
function riothere_is_following_seller_api()
{
    register_rest_route('riothere/v1', 'is-following-seller', array(
        'methods' => 'GET',
        'permission_callback' => function () {
            return current_user_can('read');
        },
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();

            $followed_id = $params['seller_id'];

            $status = Riothere_All_In_One_Followers::is_user_following_seller($followed_id);

            return [
                'user_follow_seller' => $status,
            ];
        }));
}

add_action('rest_api_init', 'riothere_is_following_seller_api');

// to follow a seller
function riothere_follow_seller_api()
{
    register_rest_route('riothere/v1', 'follow-seller', array(
        'methods' => 'POST',
        'permission_callback' => function () {
            return current_user_can('read');
        },
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();

            $followed_id = $params['seller_id'];

            $status = Riothere_All_In_One_Followers::follow($followed_id);

            $result = [
                'success' => false,
                'data' => null,
                'message' => 'Error has happened please try again',
            ];

            if ($status == 'success') {
                $result = [
                    'success' => true,
                    'data' => null,
                    'message' => 'followed successfully',
                ];
            }
            if ($status == 'action_already_done') {
                $result = [
                    'success' => false,
                    'data' => null,
                    'message' => 'already followed',
                ];
            }
            if ($status == 'invalid_seller_followed_id') {
                $result = [
                    'success' => false,
                    'data' => null,
                    'message' => 'the user is not a seller or does not exist',
                ];
            }

            if ($status == 'error_create_line') {
                $result = [
                    'success' => false,
                    'data' => null,
                    'message' => 'an error occurred',
                ];
            }

            return $result;

        }));
}

add_action('rest_api_init', 'riothere_follow_seller_api');

// to unfollow a seller
function riothere_unfollow_seller_api()
{
    register_rest_route('riothere/v1', 'unfollow-seller', array(
        'methods' => 'POST',
        'permission_callback' => function () {
            return current_user_can('read');
        },
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();

            $followed_id = $params['seller_id'];

            $status = Riothere_All_In_One_Followers::unfollow($followed_id);

            $result = [
                'success' => false,
                'data' => null,
                'message' => 'Error has happened please try again',
            ];

            if ($status == 'success') {
                $result = [
                    'success' => true,
                    'data' => null,
                    'message' => 'unfollowed successfully',
                ];
            }
            if ($status == 'action_already_done') {
                $result = [
                    'success' => false,
                    'data' => null,
                    'message' => 'already unfollowed',
                ];
            }

            if ($status == 'invalid_seller_followed_id') {
                $result = [
                    'success' => false,
                    'data' => null,
                    'message' => 'the user is not a seller or does not exist',
                ];
            }

            if ($status == 'error_create_line') {
                $result = [
                    'success' => false,
                    'data' => null,
                    'message' => 'an error occurred',
                ];
            }

            return $result;

        }));
}

add_action('rest_api_init', 'riothere_unfollow_seller_api');
