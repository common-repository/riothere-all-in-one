<?php

function riothere_isExpired($startDate, $validFor)
{
    $now = new DateTime();

    $expiryDate = clone $startDate;
    $expiryDate->add($validFor);

    return $now > $expiryDate;
}

function riothere_refund_request_api()
{
    register_rest_route('riothere/v1', 'refund-request', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $order_id = $params['order_id'];
            $products_id = $params['products'];
            $customer_id = $params['customer_id'];
            $reason = $params['reason'];

            if (empty($order_id) || $order_id === '' || empty($customer_id) || $customer_id === '') {
                return new WP_Error('input_is_not_correct', __('You must provide correct input', 'refund-request'), array('status' => 400));
            }

            $order = wc_get_order($order_id);
            $order_details = $order->get_data();
            $startDate = $order_details['date_completed'];
            if ($startDate) {
                $validFor = new DateInterval('P3D');
                $isExpired = riothere_isExpired($startDate, $validFor);
                if ($isExpired) {
                    return new WP_Error('3_days_have_passed', __('3 days have passed since the order has been completed', 'refund-request'), array('status' => 400));
                }
            }

            if ($order_details['customer_id'] != $customer_id) {
                return new WP_Error('input_is_not_correct', __('Order id or Customer id is not correct', 'refund-request'), array('status' => 400));
            }

            $isRequestedBefore = get_field("refundRequested", $order_details['id']);

            if ($isRequestedBefore) {
                return new WP_Error('refund_request_has_been_done_before', __("You've already requested a refund for this order", 'refund-request'), array('status' => 400));
            }

            $products_output = wc_get_products([
                'include' => $products_id,
                'limit' => -1, // unlimited
            ]);

            $products_data = [];
            foreach ($products_output as $product) {
                $product_data = $product->get_data();
                array_push($products_data, [
                    "product_name" => $product_data['name'],
                    "product_id" => [
                        "title" => $product_data['id'],
                        "url" => get_site_url() . "/wp-admin/post.php?post=" . $product_data['id'] . "&action=edit",
                    ],
                    "product_sku" => [
                        "title" => $product_data['sku'],
                        "url" => get_site_url() . "/wp-admin/post.php?post=" . $product_data['id'] . "&action=edit",
                    ],
                    "product_price" => $product_data['regular_price'],

                ]);
            }

            $result = [
                "order_id" => $order_id,
                "products" => $products_data,
                "customer_id" => $customer_id,
                "reason" => $reason,
            ];

            $refund_request_id = wp_insert_post(array(
                'post_type' => 'refund-requests',
                'post_status' => 'publish',
            ));

            if ($refund_request_id) {
                wp_update_post([
                    'ID' => $refund_request_id,
                    'post_type' => 'refund-requests',
                    'post_title' => 'Refund Request',
                ]);
                update_field("reasons", [$reason], $refund_request_id);
                update_field("status", ["pending"], $refund_request_id);
                update_field("customer", [
                    "title" => $customer_id,
                    "url" => get_site_url() . "/wp-admin/user-edit.php?user_id=" . $customer_id,
                ], $refund_request_id);
                update_field("order_id", [
                    "title" => $order_id,
                    "url" => get_site_url() . "/wp-admin/post.php?post=" . $order_id . "&action=edit",
                ], $refund_request_id);
                update_field("products", $products_data, $refund_request_id);
            }

            $data_to_return = [
                "refund_id" => $refund_request_id,
                "status" => get_post_meta($refund_request_id, "status"),
                "reason" => get_post_meta($refund_request_id, "reasons"),
                "products" => $products_data,
                "order_id" => $order_id,
            ];

            update_field("refundRequested", true, $order_details['id']);

            return $data_to_return;
        }));
}

add_action('rest_api_init', 'riothere_refund_request_api');
