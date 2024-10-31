<?php
// obtain-coupon-from-newsletter
function riothere_subscribe_to_newsletter()
{
    register_rest_route('riothere/v1', 'newsletter/subscribe', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'args' => array(
            'email' => array(
                'description' => __('Email address for the subscriber.', 'newsletter'),
                'type' => 'string',
                'required' => true,
            ),
        ),
        'callback' => function (WP_REST_Request $request) {
            $result = [
                'success' => true,
                'data' => null,
                'message' => 'Subscribed successfully',
            ];

            $email = $request->get_param('email');
            $subscriber = NewsletterUsers::instance()->get_user($email);

            if ($subscriber === null) {
                $newsletter_coupon_post_obj = get_field('newsletter_popup_coupon', 'option');
                $coupon = null;
                $subscriber = [
                    'email' => NewsletterModule::normalize_email($email),
                    'status' => "C",
                    'updated' => time(),
                ];
                if ($newsletter_coupon_post_obj) {
                    $coupon = new WC_Coupon($newsletter_coupon_post_obj->ID);
                    $customer_newsletter_allowed_emails = $coupon->get_meta('programmatically_allowed_emails');
                    if ($customer_newsletter_allowed_emails === '') {
                        $customer_newsletter_allowed_emails = [];
                    }
                    $customer_newsletter_allowed_emails[] = $email;
                    $customer_newsletter_allowed_emails = array_unique($customer_newsletter_allowed_emails);

                    $coupon->update_meta_data('programmatically_allowed_emails', $customer_newsletter_allowed_emails);
                    $coupon->save();
                }
                //Insert
                $subscriber = NewsletterUsers::instance()->save_user($subscriber);
                if ($subscriber === false) {
                    $result['success'] = false;
                    $result['message'] = 'Check with admin';
                } else {
                    send_subscribed_to_newsletter_email($email, $coupon);
                }
            } else {
                if ($subscriber->status === TNP_User::STATUS_UNSUBSCRIBED) {
                    NewsletterUsers::instance()->set_user_status($subscriber, TNP_User::STATUS_CONFIRMED);
                    $result = [
                        'success' => true,
                        'data' => null,
                        'message' => 'Subscribed successfully',
                    ];
                } else {
                    // @todo what if subscriber is available but with a status unsubscribed?
                    $result['success'] = false;
                    $result['message'] = 'Already Subscribed';
                }

            }

            return rest_ensure_response($result);
        },
    ));
}

add_action('rest_api_init', 'riothere_subscribe_to_newsletter');
