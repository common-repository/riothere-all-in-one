<?php

function riothere_contact_us()
{
    register_rest_route('riothere/v1', 'contact-us', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'args' => array(
            'email' => array(
                'description' => __('Contact us email.', 'contact-us'),
                'type' => 'string',
                'required' => true,
            ),
        ),
        'callback' => function (WP_REST_Request $request) {
            $result = [
                'success' => true,
                'data' => null,
                'message' => 'Sent successfully',
            ];

            $first_name = $request->get_param('firstname');
            $last_name = $request->get_param('lastname');
            $email = $request->get_param('email');
            $country_code = $request->get_param('countrycode');
            $phone_number = $request->get_param('phonenumber');
            $inquiry_type = $request->get_param('inquirytype');
            $subject = $request->get_param('subject');
            $body = $request->get_param('body');

            send_contact_us_email($first_name, $last_name, $email, $country_code, $phone_number, $inquiry_type, $subject, $body);

            return rest_ensure_response($result);

        },
    ));
}

add_action('rest_api_init', 'riothere_contact_us');
