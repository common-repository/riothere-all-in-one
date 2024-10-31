<?php

function riothere_httpDelete($url)
{
    $response = wp_remote_request($url, array(
        'method' => 'DELETE',
    ));
    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
}

function riothere_clear_search()
{
    register_rest_route('riothere/v1', 'clear-search', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            try {
                $response = riothere_httpDelete(OPENSEARCH_DOMAIN_URI . '/categories');
                $response = riothere_httpDelete(OPENSEARCH_DOMAIN_URI . '/designers');
                $response = riothere_httpDelete(OPENSEARCH_DOMAIN_URI . '/products');

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

add_action('rest_api_init', 'riothere_clear_search');
