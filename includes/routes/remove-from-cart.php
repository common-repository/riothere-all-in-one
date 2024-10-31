<?php

function riothere_delete_product_row_from_cart($product_id, $cart_id)
{
    $rows = get_field('products', $cart_id, false);
    $current_date = date('d/m/Y h:i:s a', time());

    $counter = 1;
    foreach ($rows as $row) {
        // field_62dec05d9b7f3 is the 'id' field.
        // TODO find a way NOT to use `field_62dec05d9b7f3` since it is auto-generated and unreadable
        if ($row['field_62dec05d9b7f3']['title'] === $product_id) {
            $current_product = wc_get_product($product_id);
            delete_row('products', $counter, $cart_id);
            update_post_meta($cart_id, 'number_of_products', ((int) get_post_meta($cart_id, 'number_of_products', true)) - 1);
            update_post_meta($cart_id, 'date_modified', $current_date);
            update_post_meta($cart_id, 'total_value_of_cart', ((float) get_post_meta($cart_id, 'total_value_of_cart', true)) - ((float) $current_product->get_price()));

            return true;
        }
        $counter++;
    }

    return false;
}

function riothere_remove_from_cart_api()
{
    register_rest_route('riothere/v1', 'remove-from-cart', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $product_ids = $params['product_ids'];

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
            $remaining_rows = count($rows);

            foreach ($product_ids as $product_id) {
                if (riothere_delete_product_row_from_cart($product_id, $cart[0]->ID)) {
                    $remaining_rows--;
                }
            }

            if ($remaining_rows === 0) {
                wp_delete_post($cart[0]->ID, false);
            }

            return array(
                'status' => 'ok',
            );

        }));
}

add_action('rest_api_init', 'riothere_remove_from_cart_api');
