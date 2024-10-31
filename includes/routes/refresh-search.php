<?php

function riothere_bulkInsert($action_pairs)
{
    $url = OPENSEARCH_DOMAIN_URI . "/_bulk";
    $data = "";

    foreach ($action_pairs as $action_pair) {
        $data .= json_encode($action_pair[0]) . "\n";
        $data .= json_encode($action_pair[1]) . "\n";
    }

    $response = wp_remote_post($url, array(
        'body' => $data,
        'blocking' => true,
        'headers' => array('Content-Type' => 'application/json'),
    ));
    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
}

function riothere_refresh_search()
{
    register_rest_route('riothere/v1', 'refresh-search', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            $categories = get_categories(array(
                'taxonomy' => 'product_cat',
                'limit' => 1,
            ));

            $map = array();

            foreach ($categories as $category) {
                $map[$category->term_id] = $category;
            }

            $new = array();

            foreach ($map as $category) {
                $parent_id = $category->term_id;
                $path = [];
                $last_id = 0;
                $i = 1;
                while ($parent_id != 0) {
                    array_push($path, $map[$parent_id]);
                    $last_id = $parent_id;
                    $parent_id = $map[$parent_id]->parent;
                    $i++;
                }
                $new[$category->term_id] = array(
                    'term_id' => $category->term_id,
                    'cat_name' => $category->cat_name,
                    'slug' => $category->slug,
                    'parent' => $category->parent,
                    'root_category' => $last_id == 0 ? array('slug' => '') : $map[$last_id],
                    'level' => $i,
                    'path' => array_reverse($path),
                );
            }

            // Use default arguments.
            $args = [
                'post_type' => 'product',
                'posts_per_page' => -1, // all posts
                'post_status' => 'publish',
                'paged' => 1,
            ];

            // Only get `listed` products
            $args['meta_query'] = array(
                'relation' => "AND",
                array(
                    'key' => 'status',
                    'value' => 'listed',
                    'compare' => '=',
                ),
            );

            $the_query = new WP_Query($args);

            if (!$the_query->have_posts()) {
                return [
                    'status' => 'failed',
                    'message' => 'no products to sync',
                ];
            }

            wp_reset_postdata();

            $products_id = [];
            $posts = $the_query->posts;

            foreach ($posts as $post) {
                array_push($products_id, $post->ID);
            }

            $fetched_products = [];

            if ($products_id && sizeof($products_id) > 0) {
                $fetched_products = wc_get_products([
                    'include' => $products_id,
                    'limit' => -1, // unlimited
                ]);
            }

            // Because `wc_get_products` breaks sorting, we re-sort based on DB result
            $fetched_products_sorted = [];
            foreach ($products_id as $id) {
                foreach ($fetched_products as $product) {
                    if ($product->get_id() === $id) {
                        array_push($fetched_products_sorted, $product);
                    }
                }
            }

            $designers_update_batch = [];
            $categories_update_batch = [];

            foreach ($new as $category) {
                if ($category['root_category']->slug === 'designers' && $category['slug'] !== 'designers') {
                    array_push($designers_update_batch, [
                        [
                            'index' => [
                                '_index' => 'designers',
                                '_id' => $category['term_id'],
                            ],
                        ],
                        [
                            'id' => $category['term_id'],
                            'name' => $category['cat_name'],
                            'slug' => $category['slug'],
                        ],
                    ]);
                }
                if ($category['root_category']->slug === 'categories' && $category['slug'] !== 'categories') {
                    $category_id = $category['term_id'];
                    $parent_slugs = array();
                    $parent_names = array();
                    $level = 0;
                    while ($category_id != 0) {
                        $level++;
                        $category_id = $new[$category_id]['parent'];
                        $slug = $new[$category_id]['slug'];
                        $name = $new[$category_id]['cat_name'];

                        // Populate the parents array if the ID is not 0 (root of tree)
                        // and if slug is not "categories" (all categories have
                        // "categories" as root. Useless to store this.)
                        if ($category_id != 0 && $slug !== "categories") {
                            $parent_slugs[] = $slug;
                            $parent_names[] = $name;
                        }

                    }
                    array_push($categories_update_batch, [
                        [
                            'index' => [
                                '_index' => 'categories',
                                '_id' => $category['term_id'],
                            ],
                        ],
                        [
                            'id' => $category['term_id'],
                            'name' => $category['cat_name'],
                            'slug' => $category['slug'],
                            'level' => $level - 1, // L1 category? L2? L3?
                            'parent_slugs' => $parent_slugs, // slugs of all parent categories (from direct parent to grandparent etc...)
                            'parent_names' => $parent_names, // names of all parent categories (from direct parent to grandparent etc...)
                        ],
                    ]);
                }

            }

            $product_update_batch = array_map(function ($product) use ($new) {

                $categories_map = array();

                foreach ($product->get_category_ids() as $category_id) {
                    if (!array_key_exists($new[$category_id]['root_category']->slug, $categories_map)) {
                        $categories_map[$new[$category_id]['root_category']->slug] = [];
                    }
                    array_push($categories_map[$new[$category_id]['root_category']->slug], $new[$category_id]);
                }

                $riot_categories = array_key_exists('categories', $categories_map) ? $categories_map['categories'] : [];

                $bypath = array(
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                );

                foreach ($riot_categories as $riot_category) {
                    $i = 1;
                    foreach ($riot_category['path'] as $riot_category_path) {
                        array_push($bypath[$i], $riot_category_path->cat_name);
                        $i++;
                    }
                    $i = 1;
                }

                $sizes = array_key_exists('sizes', $categories_map) ? array_map(function ($category) {
                    return $category['cat_name'];
                }, $categories_map['sizes']) : [];

                $designers = array_key_exists('designers', $categories_map) ? array_map(function ($category) {
                    return $category['cat_name'];
                }, $categories_map['designers']) : [];

                $colors = array_key_exists('colors', $categories_map) ? array_map(function ($category) {
                    return $category['cat_name'];
                }, $categories_map['colors']) : [];

                return [
                    [
                        'index' => [
                            '_index' => 'products',
                            '_id' => $product->get_id(),
                        ],
                    ],
                    [
                        'id' => $product->get_id(),
                        'title' => $product->get_title(),
                        'sizes' => $sizes,
                        'colors' => $colors,
                        'designers' => $designers,
                        'l1s' => $bypath[2],
                        'l2s' => $bypath[3],
                        'l3s' => $bypath[4],
                    ],
                ];
            }, $fetched_products_sorted);

            try {
                $responses = riothere_bulkInsert($product_update_batch);

                if ($responses->errors) {
                    return [
                        'status' => 'failed',
                    ];
                }

                $responses = riothere_bulkInsert($designers_update_batch);

                if ($responses->errors) {
                    return [
                        'status' => 'failed',
                    ];
                }

                $responses = riothere_bulkInsert($categories_update_batch);

                return [
                    'status' => $responses->errors ? 'failed' : 'ok',
                    'product_update_batch' => $product_update_batch,
                    'designers_update_batch' => $designers_update_batch,
                    'categories_update_batch' => $categories_update_batch,
                ];

            } catch (Exception $exc) {
                return [
                    'status' => 'failed',
                ];
            }
        }));
}

add_action('rest_api_init', 'riothere_refresh_search');
