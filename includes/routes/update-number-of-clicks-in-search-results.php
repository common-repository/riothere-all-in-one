<?php

function riothere_update_number_of_clicks_in_search_results()
{
    register_rest_route('riothere/v1', 'update-number-of-clicks-in-search-results', array(
        'methods' => 'PATCH',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $productId = $request->get_param('product_id');
            $numberOfClicksInSearchResults = (int) get_post_meta($productId, 'number_of_clicks_in_search_results', true);
            update_post_meta($productId, 'number_of_clicks_in_search_results', ($numberOfClicksInSearchResults + 1));
            return true;
        },
    ));
}

add_action('rest_api_init', 'riothere_update_number_of_clicks_in_search_results');
