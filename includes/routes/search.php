<?php

function riothere_httpPost($url, $data)
{
    $response = wp_remote_post($url, array(
        'body' => json_encode($data),
        'blocking' => true,
        'headers' => array('Content-Type' => 'application/json'),
    ));
    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
}

function riothere_search()
{
    register_rest_route('riothere/v1', 'search', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            try {
                // error_log("in search api: " . print_r($request, true) . "\n", 3, 'codemonk.log');
                $params = $request->get_params();
                $per_page = 5;
                $query = "";
                $image_size = "medium";

                $products = [];
                $designers = [];
                $categories = [];

                if (array_key_exists('query', $params)) {
                    $query = $params['query'];
                }

                if (array_key_exists('per_page', $params)) {
                    $per_page = $params['per_page'];
                }

                $response = riothere_httpPost(OPENSEARCH_DOMAIN_URI . '/products/_search?size=' . $per_page, [
                    'query' => [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['title^5', 'designers^4', 'colors^3', 'l1s^2', 'l2s^1', 'l3s^1'],
                        ],
                    ],
                ]);

                $product_hits = $response->hits->total->value;
                // error_log("in search api, product_hits: " . print_r($product_hits, true) . "\n", 3, 'codemonk.log');

                if ($product_hits !== 0) {
                    $products_ids = array_map(function ($product) {
                        return $product->_source->id;
                    }, $response->hits->hits);

                    $products_map = [];
                    $products_output = [];

                    if ($products_ids && sizeof($products_ids) > 0) {
                        $products_output = wc_get_products([
                            'include' => $products_ids,
                            'limit' => -1,
                        ]);
                    }
                    // error_log("in search api, products_output: " . print_r($products_output, true) . "\n", 3, 'codemonk.log');

                    foreach ($products_output as $product) {
                        /*
                         * @todo find a better way to get product data using get_data wound get the product meta without any hook
                         * This todo could break the front end depending on what params are used on the frontend
                         * */

                        $product_data = Riothere_All_In_One_Admin::get_product_data($product->get_id(), $image_size);;
                        array_push($products, $product_data);
                    }
                }

                $response = riothere_httpPost(OPENSEARCH_DOMAIN_URI . '/designers/_search?size=' . 5, [
                    'query' => [
                        'match' => [
                            'name' => $query,
                        ],
                    ],
                ]);

                if ($response->hits->total->value !== 0) {
                    $designers = array_map(function ($designer) {
                        return $designer->_source;
                    }, $response->hits->hits);
                }

                $response = riothere_httpPost(OPENSEARCH_DOMAIN_URI . '/categories/_search?size=' . 5, [
                    'query' => [
                        'match' => [
                            'name' => $query,
                        ],
                    ],
                ]);

                if ($response->hits->total->value !== 0) {
                    $categories = array_map(function ($category) {
                        return $category->_source;
                    }, $response->hits->hits);
                }

                return [
                    'status' => 'ok',
                    'data' => [
                        'categories' => $categories,
                        'designers' => $designers,
                        'products' => $products,
                        'product_hits' => $product_hits,
                    ],
                ];
            } catch (Exception $exc) {
                return [
                    'status' => 'failed',
                ];
            }
        }));
}

add_action('rest_api_init', 'riothere_search');
