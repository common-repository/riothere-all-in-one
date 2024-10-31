<?php

function riothere_kibana_data()
{
    register_rest_route('riothere/v1', 'kibana-data', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            $users_update_batch = [];
            $orders_update_batch = [];
            $users = get_users();
            foreach ($users as $user) {
                array_push($users_update_batch, [
                    [
                        'index' => [
                            '_index' => 'users',
                            '_id' => $user->ID,
                        ],
                    ],
                    [
                        'id' => $user->ID,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->user_email,
                        'date_registered' => $user->user_registered,
                    ],
                ]);
            }

            $args = array(
                'limit' => -1,
            );
            $orders = wc_get_orders($args);

            foreach ($orders as $order) {
                $order_data = $order->get_data(); // The Order data
                array_push($orders_update_batch, [
                    [
                        'index' => [
                            '_index' => 'orders',
                            '_id' => $order->ID,
                        ],
                    ],
                    [
                        'order_id' => $order_data['id'],
                        'order_parent_id' => $order_data['parent_id'],
                        'order_status' => $order_data['status'],
                        'order_currency' => $order_data['currency'],
                        'order_version' => $order_data['version'],
                        'order_payment_method' => $order_data['payment_method'],
                        'order_payment_method_title' => $order_data['payment_method_title'],
                        'order_payment_method' => $order_data['payment_method'],
                        'order_payment_method' => $order_data['payment_method'],

                        ## Creation and modified WC_DateTime Object date string ##

                        // Using a formated date ( with php date() function as method)
                        'order_date_created' => $order_data['date_created']->date('Y-m-d H:i:s'),
                        'order_date_modified' => $order_data['date_modified']->date('Y-m-d H:i:s'),

                        // Using a timestamp ( with php getTimestamp() function as method)
                        'order_timestamp_created' => $order_data['date_created']->getTimestamp(),
                        'order_timestamp_modified' => $order_data['date_modified']->getTimestamp(),

                        'order_discount_total' => $order_data['discount_total'],
                        'order_discount_tax' => $order_data['discount_tax'],
                        'order_shipping_total' => $order_data['shipping_total'],
                        'order_shipping_tax' => $order_data['shipping_tax'],
                        'order_total' => $order_data['total'],
                        'order_total_tax' => $order_data['total_tax'],
                        'order_customer_id' => $order_data['customer_id'], // ... and so on

                        ## BILLING INFORMATION:

                        'order_billing_first_name' => $order_data['billing']['first_name'],
                        'order_billing_last_name' => $order_data['billing']['last_name'],
                        'order_billing_company' => $order_data['billing']['company'],
                        'order_billing_address_1' => $order_data['billing']['address_1'],
                        'order_billing_address_2' => $order_data['billing']['address_2'],
                        'order_billing_city' => $order_data['billing']['city'],
                        'order_billing_state' => $order_data['billing']['state'],
                        'order_billing_postcode' => $order_data['billing']['postcode'],
                        'order_billing_country' => $order_data['billing']['country'],
                        'order_billing_email' => $order_data['billing']['email'],
                        'order_billing_phone' => $order_data['billing']['phone'],

                        ## SHIPPING INFORMATION:

                        'order_shipping_first_name' => $order_data['shipping']['first_name'],
                        'order_shipping_last_name' => $order_data['shipping']['last_name'],
                        'order_shipping_company' => $order_data['shipping']['company'],
                        'order_shipping_address_1' => $order_data['shipping']['address_1'],
                        'order_shipping_address_2' => $order_data['shipping']['address_2'],
                        'order_shipping_city' => $order_data['shipping']['city'],
                        'order_shipping_state' => $order_data['shipping']['state'],
                        'order_shipping_postcode' => $order_data['shipping']['postcode'],
                        'order_shipping_country' => $order_data['shipping']['country'],
                    ],
                ]);
            }

            // return $orders_update_batch;

            try {
                $responses = riothere_bulkInsert($orders_update_batch);
                // return $responses;
                // return json_encode($responses);
                if ($responses->errors) {
                    return [
                        'status' => 'failed',
                    ];
                }

                $responses = riothere_bulkInsert($users_update_batch);

                if ($responses->errors) {
                    return [
                        'status' => 'failed',
                    ];
                }

                return [
                    'status' => 'ok',
                    'orders_update_batch' => $orders_update_batch,
                    'users_update_batch' => $users_update_batch,
                ];
            } catch (Exception $exc) {
                return [
                    'status' => 'failed',
                ];
            }
        }
    ));
}

add_action('rest_api_init', 'riothere_kibana_data');
