<?php

function send_try_and_buy_admin_email($user_first_name, $user_last_name, $order_id, $delivery_time_day, $delivery_time_duration)
{
    $message = get_try_and_buy_admin_email($user_first_name, $user_last_name, $order_id, $delivery_time_day, $delivery_time_duration);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $subject = "[riothere]: New Try & Buy order #$order_id";
    $admin_email = get_option('admin_email');

    wp_mail($admin_email, $subject, $message, $headers);
}
