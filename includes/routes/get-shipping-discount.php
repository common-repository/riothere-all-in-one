<?php

function riothere_get_shipping_discount()
{
    // Note: API expects `cart.amount` to be in AED
    register_rest_route('riothere/v1', 'shipping-discount', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {

            $params = $request->get_params();
            $order = $params['order'];
            $cart = $params['cart'];

            $free_shipping_promotions = get_posts([
                'post_type' => 'free-shipping',
                'status' => 'publish',
                'nopaging' => true,
            ]);

            $free_shipping_product_ids = array();
            $paid_shipping_product_ids = array();
            $free_shipping_product_skus = array();
            $paid_shipping_product_skus = array();

            $all_my_items = $cart['items'];
            $no_match_count = 0;

            foreach ($free_shipping_promotions as $promotion) {
                // Rule 1
                $selected_countries_are_not_specified_by_admin = !isset($promotion->selected_countries) || !is_array($promotion->selected_countries);
                $shipping_country_found_in_array = is_array($promotion->selected_countries) && in_array($order['shipping']['country'], $promotion->selected_countries);
                $shipping_country_matches_rules = $selected_countries_are_not_specified_by_admin || $shipping_country_found_in_array;

                // Rule 2
                $selected_customers_are_not_specified_by_admin = !isset($promotion->selected_customers) || !is_array($promotion->selected_customers);
                $customer_found_in_array = is_array($promotion->selected_customers) && in_array($order['billing']['email'], $promotion->selected_customers);
                $customer_matches_rules = $selected_customers_are_not_specified_by_admin || $customer_found_in_array;

                // Rule 3
                $cart_maximum_amount = isset($promotion->cart_price_less_than) && is_numeric($promotion->cart_price_less_than) ? $promotion->cart_price_less_than : INF;
                $cart_minimum_amount = isset($promotion->cart_price_greater_than) && is_numeric($promotion->cart_price_greater_than) ? $promotion->cart_price_greater_than : 0;
                $cart_matches_rules = $cart['amount'] >= $cart_minimum_amount && $cart['amount'] <= $cart_maximum_amount;

                // If all rules match, we start looking at the products
                if (!$shipping_country_matches_rules || !$customer_matches_rules || !$cart_matches_rules) {
                    $no_match_count++;
                    continue;
                }

                // If we're here, it means all rules match --> check products
                $applicable_products_promotion = Riothere_All_In_One_Free_Shipping_Promotions::get_applicable_product_ids($promotion->ID);

                foreach ($all_my_items as $item) {
                    if (in_array($item['id'], $applicable_products_promotion)) {
                        array_push($free_shipping_product_ids, $item['id']);
                        array_push($free_shipping_product_skus, $item['sku']);
                    } else {
                        array_push($paid_shipping_product_ids, $item['id']);
                        array_push($paid_shipping_product_skus, $item['sku']);
                    }
                }
            }

            if ($no_match_count === count($free_shipping_promotions)) {
                // None of the Promotions applies --> we should consider all
                // the products to be paid and none to be free
                foreach ($all_my_items as $item) {
                    array_push($paid_shipping_product_ids, $item['id']);
                    array_push($paid_shipping_product_skus, $item['sku']);
                }
            }

            return array(
                'status' => 'ok',
                'data' => array(
                    'free_shipping_product_ids' => $free_shipping_product_ids,
                    'free_shipping_product_skus' => $free_shipping_product_skus,
                    'paid_shipping_product_ids' => $paid_shipping_product_ids,
                    'paid_shipping_product_skus' => $paid_shipping_product_skus,
                ),
            );
        }));
}

add_action('rest_api_init', 'riothere_get_shipping_discount');
