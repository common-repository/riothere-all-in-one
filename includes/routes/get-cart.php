<?php
function riothere_get_cart_api()
{
    register_rest_route('riothere/v1', 'cart', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            $current_user = wp_get_current_user();

            $cart = get_posts([
                'post_type' => 'carts',
                'status' => 'publish',
                'nopaging' => true,
                'meta_query' => array(
                    array(
                        'key' => 'user_email',
                        'value' => $current_user->user_email, // easier to query by email than by id
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                        'value' => '0',
                        'compare' => '=',
                    ),
                ),
            ]);

            $rows = get_field('products', $cart[0]->ID, false);
            $product_ids = array();

            foreach ($rows as $row) {
                // field_62dec05d9b7f3 is the 'id' field.
                array_push($product_ids, $row['field_62dec05d9b7f3']['title']);
            }

            return array(
                'status' => 'ok',
                'data' => array(
                    'product_ids' => $product_ids,
                ),
            );
        }));
}

add_action('rest_api_init', 'riothere_get_cart_api');
