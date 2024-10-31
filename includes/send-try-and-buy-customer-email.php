<?php

function send_try_and_buy_customer_email($delivery_time_day, $delivery_time_duration, $email)
{
    $message = get_try_and_buy_customer_email($delivery_time_day, $delivery_time_duration);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $subject = "Your riothere try and buy order has been received!";

    wp_mail($email, $subject, $message, $headers);
}
