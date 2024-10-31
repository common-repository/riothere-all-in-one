<?php

function riothere_get_try_and_buy_orders_to_retrieve_and_deliver_api()
{
    register_rest_route('riothere/v1', 'try-and-buy-orders-to-retrieve-and-deliver', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();

            $time_selected = $params['time_selected'];
            if ($time_selected == "" || $time_selected == null) {
                $time_selected = "now";
            }

            // $time_selected = 'Tuesday, June 28, 2022 ';

            $orders = wc_get_orders(
                array(
                    'status' => 'wc-try-and-buy',
                    'limit' => -1,
                )
            );

            // We need only the Try&Buy Orders that fall in the future (
            // (i.e. date of delivery greater than Admin user selection)
            $filtered_orders = [];
            foreach ($orders as $order) {
                $order_time_slot_day = $order->get_meta('time_slot_day');
                if (strtotime($order_time_slot_day) >= strtotime($time_selected)) {
                    array_push($filtered_orders, $order);
                }
            }
            $orders = $filtered_orders;
            $return_array = [];
            $timeGrantedToTryItems_hrs = 24;

            foreach ($orders as $order) {
                $items = $order->get_items();
                $corresponding_try_and_buy_order_id = $order->get_meta('corresponding_try_and_buy_order_id');
                if ($corresponding_try_and_buy_order_id && $corresponding_try_and_buy_order_id != "") {
                    // Try&Buy Orders that are complete (i.e. both Journey 1 and Journey 2) or
                    // have a journey 2 order with status failed or pending
                    $corresponding_try_and_buy_order = wc_get_order($corresponding_try_and_buy_order_id);
                    $is_order_not_completed = $corresponding_try_and_buy_order->has_status('failed') || $corresponding_try_and_buy_order->has_status('pending');
                    if (!$is_order_not_completed) {
                        // have a journey 2 order with statuses neither failed nor pending
                        $corresponding_try_and_buy_order_items = $corresponding_try_and_buy_order->get_items();

                        $item_ids = array_values(
                            array_map(function ($item) {
                                return $item->get_product_id();
                            }, $items));

                        $corresponding_try_and_buy_order_item_ids = array_values(
                            array_map(function ($item) {
                                return $item->get_product_id();
                            }, $corresponding_try_and_buy_order_items));

                        $different_items_array_of_ids = array_diff($item_ids, $corresponding_try_and_buy_order_item_ids);
                        $return_array[$order->get_id()]['items_to_retrieve'] = array_values($different_items_array_of_ids);

                        $same_items_array_of_ids = array_intersect($item_ids, $corresponding_try_and_buy_order_item_ids);
                        $return_array[$order->get_id()]['items_purchased'] = array_values($same_items_array_of_ids);
                    } else {
                        // have a journey 2 order with status failed or pending
                        $time_slot_day = $order->get_meta('time_slot_day');
                        $time_slot_duration = $order->get_meta('time_slot_duration');
                        $time_slot_duration_array = explode("-", $time_slot_duration);
                        $time_slot_to_time = "";
                        if ($time_slot_duration_array != [] && $time_slot_duration_array && $time_slot_duration_array[0] != "") {
                            $time_slot_to_time = $time_slot_duration_array[1];
                        }
                        $time_slot = $time_slot_day . $time_slot_to_time;

                        $items_array_of_ids = [];

                        foreach ($items as $item) {
                            $item_id = $item->get_product_id();
                            array_push($items_array_of_ids, $item_id);
                        }

                        if (strtotime($time_selected) < strtotime($time_slot)) {
                            $return_array[$order->get_id()]['items_to_deliver'] = array_values($items_array_of_ids);
                        } else if (strtotime($time_selected) >= (strtotime($time_slot) + $timeGrantedToTryItems_hrs * 60 * 60)) {
                            $return_array[$order->get_id()]['items_to_retrieve'] = array_values($items_array_of_ids);
                        }
                    }

                } else {
                    // Try & Buy orders that only have Journey 1 (no Journey 2)

                    $time_slot_day = $order->get_meta('time_slot_day');
                    $time_slot_duration = $order->get_meta('time_slot_duration');
                    $time_slot_duration_array = explode("-", $time_slot_duration);
                    $time_slot_to_time = "";
                    if ($time_slot_duration_array != [] && $time_slot_duration_array && $time_slot_duration_array[0] != "") {
                        $time_slot_to_time = $time_slot_duration_array[1];
                    }
                    $time_slot = $time_slot_day . $time_slot_to_time;

                    $items_array_of_ids = [];

                    foreach ($items as $item) {
                        $item_id = $item->get_product_id();
                        array_push($items_array_of_ids, $item_id);
                    }

                    if (strtotime($time_selected) < strtotime($time_slot)) {
                        $return_array[$order->get_id()]['items_to_deliver'] = array_values($items_array_of_ids);
                    } else if (strtotime($time_selected) >= (strtotime($time_slot) + $timeGrantedToTryItems_hrs * 60 * 60)) {
                        $return_array[$order->get_id()]['items_to_retrieve'] = array_values($items_array_of_ids);
                    }
                }
            }

            return array(
                'status' => 'ok',
                'data' => $return_array,
            );
        }));
}

add_action('rest_api_init', 'riothere_get_try_and_buy_orders_to_retrieve_and_deliver_api');
