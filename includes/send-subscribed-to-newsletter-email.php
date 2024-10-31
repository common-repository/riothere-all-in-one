<?php

function send_subscribed_to_newsletter_email($email, $coupon)
{
    $body = get_subscribed_to_newsletter_email($email,$coupon);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $title = "Subscribed to RIOT Newsletter";

    wp_mail($email, $title, $body, $headers);
}
