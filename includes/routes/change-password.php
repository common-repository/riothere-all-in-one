<?php

function riothere_change_password_api_permission()
{
    return current_user_can('read');
}

function riothere_change_password_api()
{
    register_rest_route('riothere/v1', 'change-password', array(
        'methods' => 'POST',
        'permission_callback' => 'riothere_change_password_api_permission',
        'callback' => function (WP_REST_Request $request) {
            $pattern = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$/';
            $params = $request->get_params();
            $password = $params['current_password'];
            $new_password = $params['new_password'];
            $user = wp_get_current_user();

            if ($user instanceof WP_User) {
                $is_current_password_valid = wp_check_password($password, $user->user_pass, $user->ID);

                if (!$is_current_password_valid) {
                    return new WP_Error(
                        'wrong_current_password',
                        __('The current provided password is wrong', 'riothere-all-in-one'),
                        array('status' => 400)
                    );
                }

                if (strlen($new_password) < 8) {
                    return new WP_Error(
                        'new_password_short',
                        __('password must be 8 characters long', 'riothere-all-in-one'),
                        array('status' => 400)
                    );
                }

                if (!preg_match($pattern, $new_password)) {
                    return new WP_Error(
                        'new_password_wrong_format',
                        __('password must contain uppercase, lowercase and number character',
                            'riothere-all-in-one'),
                        array('status' => 400)
                    );
                }

                wp_set_password($new_password, $user->ID);

                return [
                    'status' => 200,
                    'message' => __('password updated successfully'),
                ];
            }

            return new WP_Error(
                'authentication_problem',
                __('authentication problem', 'riothere-all-in-one'),
                array('status' => 403)
            );

        },
        'args' => array(
            'current_password' => [
                'required' => true,
                'type' => 'string',
            ],
            'new_password' => [
                'required' => true,
                'type' => 'string',
            ],
        ),
    ));
}

add_action('rest_api_init', 'riothere_change_password_api');
