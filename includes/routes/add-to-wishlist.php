<?php
function riothere_add_to_wishlist_api()
{
    register_rest_route('riothere/v1', 'add-to-wishlist', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $product_id = $params['product_id'];
            $wishlist_stringified = $params['wishlist_stringified'];

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

            $wishlists = get_posts([
                'post_type' => 'wishlists',
                'status' => 'publish',
                'nopaging' => true,
            ]);

            $user_has_wishlist = false;

            foreach ($wishlists as $wishlist) {
                $user_has_wishlist = $wishlist->user_id['title'] === $current_user->data->ID;

                if ($user_has_wishlist) {
                    break;
                }
            }

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

            $current_date = date('d-m-Y');

            if (!$user_has_wishlist) {
                $row_data = [$row];

                $post_id = wp_insert_post(array(
                    'post_type' => 'wishlists',
                    'post_title' => $current_user->data->display_name . "'s wishlist",
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
                        'number_of_products' => 1,
                    ],
                ));

                // The Products (Repeater) field
                // TODO infer field key from field id
                // $obj = get_field_objects($post_id);
                update_field('field_62c42f93aa739', $row_data, $post_id);
                update_post_meta($post_id, 'wishlist_stringified', $wishlist_stringified);
            } else {
                $current_wishlist = get_posts([
                    'post_type' => 'wishlists',
                    'status' => 'publish',
                    'meta_key' => 'user_email', // easier to query by email than by id
                    'meta_value' => $current_user->user_email,
                ]);

                $current_wishlist_id = $current_wishlist[0]->ID;
                $current_products = get_field('products', $current_wishlist_id, false);
                $current_products_id = array();
                foreach ($current_products as $product) {
                    array_push($current_products_id, $product['field_62c42ff3308b2']['title']);
                }
                if (in_array($current_product->get_id(), $current_products_id)) {
                    return array(
                        'status' => 'Product already exists in the wishlist',
                    );
                }

                update_post_meta($current_wishlist_id, 'number_of_products', ((int) get_post_meta($current_wishlist_id, 'number_of_products', true)) + 1);
                update_post_meta($current_wishlist_id, 'wishlist_stringified', $wishlist_stringified);
                update_post_meta($current_wishlist_id, 'date_modified', $current_date);
                add_row('products', $row, $current_wishlist_id);
            }

            return array(
                'status' => 'ok',
            );
        }));
}

add_action('rest_api_init', 'riothere_add_to_wishlist_api');
