<?php
function riothere_add_to_cart_api()
{
    register_rest_route('riothere/v1', 'add-to-cart', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $product_id = $params['product_id'];
            $cart_stringified = $params['cart_stringified'];

            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            $current_user = wp_get_current_user();
            $current_product = wc_get_product($product_id);

            if (!$current_product) {
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

            $user_has_non_fulfilled_cart = count($cart) > 0;

            $row = [
                'id' => [
                    "title" => $current_product->get_id(),
                    "url" => get_site_url() . "/wp-admin/post.php?post=" . $current_product->get_id() . "&action=edit",
                ],
                'sku' => $current_product->get_sku(),
                'name' => $current_product->get_name(),
                'price_aed' => $current_product->get_price(),
                'is_on_promotion' => (float) $current_product->get_price() !== (float) $current_product->get_regular_price() ? 'checked' : '',
            ];

            $current_date = date('d/m/Y h:i:s a', time());

            if (!$user_has_non_fulfilled_cart) {
                $row_data = [$row];

                $post_id = wp_insert_post(array(
                    'post_type' => 'carts',
                    'post_title' => $current_user->data->display_name . "'s cart",
                    'post_status' => 'publish',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'meta_input' => [
                        'user_id' => [
                            "title" => $current_user->data->ID,
                            "url" => get_site_url() . "/wp-admin/user-edit.php?user_id=" . $current_user->data->ID . "&wp_http_referer=%2Fwp-admin%2Fusers.php" . ' target = "_blank">' . $current_user->data->ID,
                        ],
                        'user_email' => $current_user->data->user_email,
                        'user_fullname' => $current_user->data->display_name,
                        'date_modified' => $current_date,
                        'number_of_products' => 1, // Note that this will be inserted as string '1'
                        'is_cart_fulfilled' => 0, // Note that this will be inserted as string '0'
                        'is_cart_abandoned' => 0, // Note that this will be inserted as string '0'
                    ],
                ));

                $current_product_price = get_field('regular_price', $current_product->get_id(), true);
                $value_to_be_added_cart = 0;

                update_post_meta($post_id, 'total_value_of_cart', $current_product->get_price());
                update_field('field_62dea57e9099f', $row_data, $post_id);
                update_post_meta($post_id, 'cart_stringified', $cart_stringified);
            } else {
                $current_cart_id = $cart[0]->ID;
                $current_products = get_field('products', $current_cart_id, false);
                $current_products_id = array();

                foreach ($current_products as $product) {
                    array_push($current_products_id, $product['field_62dec05d9b7f3']['title']);
                }

                if (in_array($current_product->get_id(), $current_products_id)) {
                    return array(
                        'status' => 'Product already exists in the cart',
                    );
                }

                update_post_meta($current_cart_id, 'total_value_of_cart', ((float) get_post_meta($current_cart_id, 'total_value_of_cart', true)) + ((float) $current_product->get_price()));
                update_post_meta($current_cart_id, 'number_of_products', ((int) get_post_meta($current_cart_id, 'number_of_products', true)) + 1);
                update_post_meta($current_cart_id, 'cart_stringified', $cart_stringified);
                update_post_meta($current_cart_id, 'date_modified', $current_date);
                add_row('products', $row, $current_cart_id);
            }

            return array(
                'status' => 'ok',
            );
        }));
}

add_action('rest_api_init', 'riothere_add_to_cart_api');
