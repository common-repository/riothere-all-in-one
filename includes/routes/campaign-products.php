<?php

function riothere_get_id_by_slug($page_slug)
{
    $page = get_page_by_path($page_slug);

    if ($page) {
        return $page->ID;
    } else {
        return null;
    }
}

function riothere_get_campaign_products_api()
{
    register_rest_route('riothere/v1', 'campaign-products', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {

            $params = $request->get_params();
            $slug = $params['slug'];
            $results = array(
                'status' => 'ok',
                'data' => array(),
            );
            $campaigns = get_posts([
                'post_type' => 'campaigns',
                'status' => 'any',
                'nopaging' => true,
            ]);

            $campaign_page_id = riothere_get_id_by_slug($slug);

            if ($campaigns) {
                foreach ($campaigns as $campaign) {
                    $campaign_id = $campaign->ID;
                    $selected_campaign_page_id = get_post_meta($campaign_id, 'selected_campaign_page_id', true);
                    if ($selected_campaign_page_id == $campaign_page_id) {
                        $applicable_product_ids = Riothere_All_In_One_Campaigns::get_applicable_product_ids($campaign_id);
                        $results['data'] = $applicable_product_ids;
                    }
                }
            }

            return $results;
        }));
}

add_action('rest_api_init', 'riothere_get_campaign_products_api');
