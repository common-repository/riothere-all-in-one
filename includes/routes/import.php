<?php

/**
 * Used to import a CSV containing Product SKU and Seller Email to update
 * all the products to be linked to a certain seller
 */
function riothere_import()
{
    register_rest_route('riothere/v1', 'import', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            if (($handle = fopen("products_sku.csv", "r")) !== false) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $sku = trim($data[0]);
                    $email = trim($data[1]);
                    $row++;

                    echo "{$row}: sku = {$sku} | email = {$email}\n";

                    if ($sku != "" && $email != "") {
                        $product_id = wc_get_product_id_by_sku($sku);

                        if (!$product_id) {
                            continue;
                        }

                        $user = get_user_by('email', $email);

                        if (!$user) {
                            continue;
                        }

                        $user_id = $user->ID;

                        $result = update_field('seller', $user_id, $product_id);

                        // Update role to "Seller"
                        // wp_update_user(array('ID' => $user_id, 'role' => 'shop_manager'));
                        wp_update_user(array('ID' => $user_id, 'role' => 'seller'));
                    }
                }
                fclose($handle);
            }
        }));
}

add_action('rest_api_init', 'riothere_import');
