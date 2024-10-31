<?php

function riothere_clear_kibana_data()
{
    register_rest_route('riothere/v1', 'clear-kibana-data', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            try {
                $response = httpDelete(OPENSEARCH_DOMAIN_URI . '/users');
                $response = httpDelete(OPENSEARCH_DOMAIN_URI . '/orders');

                return [
                    'status' => 'ok',
                ];
            } catch (Exception $exc) {
                return [
                    'status' => 'failed',
                ];
            }
        }));
}

add_action('rest_api_init', 'riothere_clear_kibana_data');
