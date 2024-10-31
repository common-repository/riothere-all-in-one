<?php

function riothere_unsubscribe_from_newsletter()
{
    register_rest_route('riothere/v1', 'newsletter/unsubscribe', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'args' => array(
            'token' => array(
                'description' => __('Encrypted Email token as received from the server', 'newsletter'),
                'type' => 'string',
                'required' => true,
            ),
        ),
        'callback' => function (WP_REST_Request $request) {

            $token = $request->get_param('token');
            $email = riot_decrypt_string($token);
            $subscriber = NewsletterUsers::instance()->get_user($email);

            $result = [
                'success' => true,
                'data' => [
                    'email' => $email,
                ],
                'message' => 'Successfully unsubscribed',
            ];

            if ($subscriber !== null) {
                if ($subscriber->status === TNP_User::STATUS_UNSUBSCRIBED) {
                    $result['success'] = false;
                    $result['message'] = 'You are already unsubscribed from the newsletter';
                } else {
                    NewsletterUsers::instance()->set_user_status($subscriber, TNP_User::STATUS_UNSUBSCRIBED);
                }
            } else {
                $result['success'] = false;
                $result['message'] = 'You aren\'t subscribed to the newsletter';
            }

            return rest_ensure_response($result);
        },
    ));
}

add_action('rest_api_init', 'riothere_unsubscribe_from_newsletter');
