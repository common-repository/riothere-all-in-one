<?php
function riothere_fulfill_cart_api()
{
    register_rest_route('riothere/v1', 'fulfill-cart', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $current_user = wp_get_current_user();

            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

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

            update_post_meta($cart[0]->ID, 'is_cart_fulfilled', 1); // Note that this will be inserted as string '1'
            update_post_meta($cart[0]->ID, 'is_cart_abandoned', 0); // Note that this will be inserted as string '0'

            return array(
                'status' => 'ok',
            );
        }));
}

add_action('rest_api_init', 'riothere_fulfill_cart_api');
