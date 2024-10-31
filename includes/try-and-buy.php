<?php

// The following 2 hooks have to do with adding the new custom order status for Try&Buy
function riothere_register_try_and_buy_order_status()
{
    register_post_status('wc-try-and-buy', array(
        'label' => 'Try & Buy',
        'public' => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list' => true,
        'exclude_from_search' => false,
        'label_count' => _n_noop('Try & Buy <span class="count">(%s)</span>', 'Try & Buy <span class="count">(%s)</span>'),
    ));
}
add_action('init', 'riothere_register_try_and_buy_order_status');

function riothere_add_try_and_buy_to_order_statuses($order_statuses)
{
    $new_order_statuses = array();
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-try-and-buy'] = 'Try & Buy';
        }
    }
    return $new_order_statuses;
}
add_filter('wc_order_statuses', 'riothere_add_try_and_buy_to_order_statuses');

// The following hook attaches a Try&Buy Journey 1 order to its corresponding Journey 2 order.
// It runs for Journey 2 orders
function riothere_update_try_and_buy_order_meta_on_new_order($order_id, $order)
{
    // If we find the following meta it means this is a Try&Buy Journey 2 order
    $corresponding_try_and_buy_order_id = $order->get_meta('corresponding_try_and_buy_order_id');

    if ($corresponding_try_and_buy_order_id !== '') {
        update_post_meta($corresponding_try_and_buy_order_id, 'corresponding_try_and_buy_order_id', $order_id);
    }
}
add_action('woocommerce_new_order', 'riothere_update_try_and_buy_order_meta_on_new_order', 99, 2);

function riothere_update_try_and_buy_order_meta_on_processing($order_id, $order)
{
    // If we find the following meta it means this is a Try&Buy Journey 2 order
    $corresponding_try_and_buy_order_id = $order->get_meta('corresponding_try_and_buy_order_id');

    if ($corresponding_try_and_buy_order_id !== '') {
        update_post_meta($corresponding_try_and_buy_order_id, 'order_completed', true);
    }
}
add_action('woocommerce_order_status_processing', 'riothere_update_try_and_buy_order_meta_on_processing', 99, 2);

//
function riothere_send_try_and_buy_email($order_id, $order)
{
    $delivery_time_day = $order->get_meta('time_slot_day');
    $delivery_time_duration = $order->get_meta('time_slot_duration');
    $email = $order->get_billing_email();
    $user_first_name = $order->get_billing_first_name();
    $user_last_name = $order->get_billing_last_name();

    send_try_and_buy_customer_email($delivery_time_day, $delivery_time_duration, $email);
    send_try_and_buy_admin_email($user_first_name, $user_last_name, $order_id, $delivery_time_day, $delivery_time_duration);

}
add_action('woocommerce_order_status_try-and-buy', 'riothere_send_try_and_buy_email', 99, 2);

function riothere_woocommerce_after_order_details_action($order)
{
    $corresponding_try_and_buy_order_id = $order->get_meta('corresponding_try_and_buy_order_id');

    if (!$corresponding_try_and_buy_order_id || $corresponding_try_and_buy_order_id === "") {
        return;
    }

    echo '<h3>Try & Buy</h3>';
    if ($order->has_status('try-and-buy')) {
        echo '<p>Journey 2: <a href="' . esc_url(admin_url('post.php?post=' . absint($corresponding_try_and_buy_order_id)) . '&action=edit') . '" class="order-view"><strong>#' . esc_attr($corresponding_try_and_buy_order_id) . '</strong></a></p>';
    } else {
        echo '<p>Journey 1: <a href="' . esc_url(admin_url('post.php?post=' . absint($corresponding_try_and_buy_order_id)) . '&action=edit') . '" class="order-view"><strong>#' . esc_attr($corresponding_try_and_buy_order_id) . '</strong></a></p>';
    }

}
add_action('woocommerce_admin_order_data_after_shipping_address', 'riothere_woocommerce_after_order_details_action', 99, 1);
