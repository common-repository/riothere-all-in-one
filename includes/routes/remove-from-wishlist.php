<?php

function riothere_delete_product_row_from_wishlist($product_id, $wishlist_id)
{
    $rows = get_field('products', $wishlist_id, false);
    $current_date = date('d-m-Y h:i:s a', time());

    $counter = 1;
    foreach ($rows as $row) {
        // field_62c42ff3308b2 is the 'id' field.
        // TODO find a way NOT to use `field_62c42ff3308b2` since it is auto-generated and unreadable
        if ($row['field_62c42ff3308b2']['title'] === $product_id) {
            delete_row('products', $counter, $wishlist_id);
            update_post_meta($wishlist_id, 'number_of_products', ((int) get_post_meta($wishlist_id, 'number_of_products', true)) - 1);
            update_post_meta($wishlist_id, 'date_modified', $current_date);

            return true;
        }
        $counter++;
    }

    return false;
}

function riothere_remove_from_wishlist_api()
{
    register_rest_route('riothere/v1', 'remove-from-wishlist', array(
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

            $wishlist = get_posts([
                'post_type' => 'wishlists',
                'status' => 'publish',
                'nopaging' => true,
                'meta_key' => 'user_email', // easier to query by email than by id
                'meta_value' => $current_user->user_email,
            ]);

            $rows = get_field('products', $wishlist[0]->ID, false);
            $remaining_rows = count($rows);

            foreach ($product_ids as $product_id) {
                if (riothere_delete_product_row_from_wishlist($product_id, $wishlist[0]->ID)) {
                    $remaining_rows--;
                }
            }

            if ($remaining_rows === 0) {
                wp_delete_post($wishlist[0]->ID, false);
            }

            return array(
                'status' => 'ok',
            );

        }));
}

add_action('rest_api_init', 'riothere_remove_from_wishlist_api');
