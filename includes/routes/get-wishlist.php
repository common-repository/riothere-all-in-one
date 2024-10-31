<?php
function riothere_get_wishlist_api()
{
    register_rest_route('riothere/v1', 'wishlist', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            $current_user = wp_get_current_user();

            $wishlist = get_posts([
                'post_type' => 'wishlists',
                'status' => 'publish',
                'nopaging' => true,
                'meta_key' => 'user_email', // easier to query by email than by id
                'meta_value' => $current_user->user_email,
            ]);

            $rows = get_field('products', $wishlist[0]->ID, false);
            $product_ids = array();

            foreach ($rows as $row) {
                // field_62c42ff3308b2 is the 'id' field.
                array_push($product_ids, $row['field_62c42ff3308b2']['title']);
            }

            return array(
                'status' => 'ok',
                'data' => array(
                    'product_ids' => $product_ids,
                ),
            );
        }));
}

add_action('rest_api_init', 'riothere_get_wishlist_api');
