<?php

function send_contact_us_email($first_name, $last_name, $email, $country_code, $phone_number, $inquiry_type, $subject, $body)
{
    $message = get_contact_us_email($first_name, $last_name, $email, $country_code, $phone_number, $inquiry_type, $body);
    $admin_email = get_option('admin_email');
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        "Reply-To: {$first_name} {$last_name} <{$email}>",
    );

    wp_mail($admin_email, $subject, $message, $headers);
}
