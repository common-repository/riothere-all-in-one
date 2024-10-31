<?php

function riothere_get_transit_times_api()
{
    register_rest_route('riothere/v1', 'transit-times', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $args = [
                'post_type' => 'transit-times',
                'post_status' => 'publish',
            ];

            $query = new WP_Query($args);
            $posts = $query->posts;

            $data = [];

            foreach ($posts as $post) {
                array_push($data, [
                    'destination_country_code' => get_post_meta($post->ID, "destination_country")[0],
                    'transit_time_days' => get_post_meta($post->ID, "transit_time_days")[0],
                ]);
                // error_log("IN post id: " . print_r($post->ID, true) . "\n", 3, 'codemonk.log');
            }

            return $data;
        }));
}

add_action('rest_api_init', 'riothere_get_transit_times_api');
