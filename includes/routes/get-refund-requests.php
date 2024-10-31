<?php

function riothere_get_refund_requests_api()
{
    register_rest_route('riothere/v1', 'refunds', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $customer_id = $params['customer_id'];

            if (empty($customer_id) || $customer_id === '') {
                return new WP_Error('input_is_not_correct', __('You must provide correct input', 'refunds'), array('status' => 400));
            }

            $args = [
                'post_type' => 'refund-requests',
                'post_status' => 'publish',
            ];

            $query = new WP_Query($args);
            $posts = $query->posts;

            $data = [];
            foreach ($posts as $post) {
                $customer = get_post_meta($post->ID, "customer")[0];
                if ($customer['title'] == $customer_id) {
                    $status = get_post_meta($post->ID, "status")[0];
                    $order_total = get_post_meta($post->ID, "order_total");
                    $return_date = get_post_meta($post->ID, "pickup_time")[0];
                    $order = get_post_meta($post->ID, "order_id")[0];
                    $products = get_field("products", $post->ID);

                    $order_id = $order['title'];
                    $order = wc_get_order($order_id);
                    $order_details = $order->get_data();

                    array_push($data, [
                        'customer_id' => $customer['title'],
                        'order_id' => $order_id,
                        'status' => $status,
                        'total' => $order_details['total'],
                        'products' => $products,
                        'id' => $post->ID,
                        'return_date' => $return_date,
                    ]);
                }
            }

            return $data;
        }));
}

add_action('rest_api_init', 'riothere_get_refund_requests_api');
