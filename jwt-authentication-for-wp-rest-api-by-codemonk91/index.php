<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

function riothere_overwrite_pw_reset_email_subject($subject)
{
    return $subject;
}

function riothere_overwrite_pw_reset_email_text($text, $email, $code, $expiry)
{
    return riothere_reset_password_email($email, $code, $expiry);
}

function riothere_overwrite_pw_reset_email_headers($headers)
{
    return array('Content-Type: text/html; charset=UTF-8');
}

add_filter('code_email_subject', 'riothere_overwrite_pw_reset_email_subject', 10, 1);
add_filter('code_email_text', 'riothere_overwrite_pw_reset_email_text', 10, 4);
add_filter('code_email_headers', 'riothere_overwrite_pw_reset_email_headers', 10, 1);
