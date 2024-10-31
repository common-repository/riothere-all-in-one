<?php

function riothere_get_newsletter_popup_config()
{
    register_rest_route('riothere/v1', 'newsletter-popup-config', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $enable_newsletter_popup = get_field('enable_newsletter_popup', 'option');
            $data = [
                'enabled' => $enable_newsletter_popup,
                'data' => null,
            ];
            $user = wp_get_current_user();
            // no need to check for authentication because user_email will be empty in case if the user isn't logged in
            $user_email = $user->user_email;
            $subscriber = NewsletterUsers::instance()->get_user($user_email);

            if ($subscriber !== null && $subscriber->status !== TNP_User::STATUS_UNSUBSCRIBED) {
                $enable_newsletter_popup = false;
                $data['enabled'] = false;
            }
            if ($enable_newsletter_popup) {
                $newsletter_popup_image = get_field('newsletter_popup_image', 'option');
                $newsletter_popup_title = get_field('newsletter_popup_title', 'option');
                $newsletter_popup_description = get_field('newsletter_popup_description', 'option');
                $newsletter_popup_signup_button_text = get_field('newsletter_popup_signup_button_text', 'option');
                $newsletter_popup_coupon = get_field('newsletter_popup_coupon', 'option');
                $newsletter_popup_time_to_show = get_field('newsletter_popup_time_to_show', 'option');
                $data['data'] = [
                    'image' => $newsletter_popup_image,
                    'title' => $newsletter_popup_title,
                    'description' => $newsletter_popup_description,
                    'subscribe_button_text' => $newsletter_popup_signup_button_text,
                    'has_coupon' => $newsletter_popup_coupon !== false,
                    'popup_time_to_show' => $newsletter_popup_time_to_show,
                    'user_email' => $user_email,
                ];
            }

            return $data;
        },
    ));
}

add_action('rest_api_init', 'riothere_get_newsletter_popup_config');
