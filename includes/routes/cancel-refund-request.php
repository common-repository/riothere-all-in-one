<?php

function riothere_refund_cancel_api()
{
    register_rest_route('riothere/v1', 'cancel-refund', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();
            $refund_id = $params['refund_id'];

            if (empty($refund_id) || $refund_id === '') {
                return new WP_Error('input_is_not_correct', __('You must provide correct input', 'cancel-refund'), array('status' => 400));
            }

            $order_id = get_post_meta($refund_id, "order_id")[0]['title'];
            update_field("status", ['canceled'], $refund_id);

            $order = wc_get_order($order_id);
            $order_details = $order->get_data();

            update_field("refundRequested", false, $order_id);

            return [
                'status' => 200,
                'message' => "refund request successfully canceled",
            ];
        }));
}

add_action('rest_api_init', 'riothere_refund_cancel_api');
