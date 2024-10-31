<?php
class Riothere_All_In_One_Products
{
    public static function get_products($categories, $per_page, $offset, $order, $orderby, $image_size, $min_price, $max_price, $seller_id, $date_of_sale_from = null, $date_of_sale_to = null, $status = 'listed', $product_ids = null)
    {
        $output = [];

        // Use default arguments.
        $args = [
            'post_type' => 'product',
            'posts_per_page' => get_option('posts_per_page'),
            // 'post_status' => 'publish',
            'paged' => 1,
        ];

        $args['meta_query'] = array(
            'relation' => "AND",
        );

        if (!empty($status)) {
            $args['meta_query'][] = array(
                'key' => 'status',
                'value' => esc_attr($status),
                'compare' => '=',
            );
        }

        if (!empty($seller_id)) {
            $args['meta_query'][] = array(
                array(
                    'key' => 'seller',
                    'value' => esc_attr($seller_id),
                    'compare' => '=',
                ),
            );
        }

        // Min / Max date of sale.
        if (!empty($date_of_sale_from)) {
            $args['meta_query'][] = array(
                array(
                    'key' => 'date_of_sale',
                    'value' => esc_attr($date_of_sale_from),
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
            );
        }
        if (!empty($date_of_sale_to)) {
            $args['meta_query'][] = array(
                array(
                    'key' => 'date_of_sale',
                    'value' => esc_attr($date_of_sale_to),
                    'compare' => '<=',
                    'type' => 'DATE',
                ),
            );
        }

        // Posts per page.
        if (!empty($per_page)) {
            $args['posts_per_page'] = $per_page;
        }

        // Pagination, starts from 1.
        if (!empty($offset)) {
            $args['paged'] = $offset;
        }

        $args['orderby'] = 'date';
        $args['order'] = 'DESC';

        // Order condition. ASC/DESC.
        if (!empty($order)) {
            $args['order'] = $order;
        }

        if (!empty($orderby)) {
            if ($orderby === "PRICE") {
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
            } else if ($orderby === "BRAND") {
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = 'brand_for_sorting_purposes';
            } else if ($orderby === "NAME") {
                $args['orderby'] = 'name';
                $args['meta_key'] = '';
            } else if ($orderby === "DATE_OF_SALE") {
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = 'date_of_sale';
            } else if ($orderby === "SELLER_REVENUE") {
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'seller_revenue_aed';
            }
        }

        // If filter buy category or attributes.
        if (!empty($categories)) {
            $args['tax_query']['relation'] = "AND";
            // Category filter.
            foreach ($categories as $category_set) {
                $category_query = [
                    'relation' => 'OR',
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $category_set,
                    ],
                ];
                array_push($args['tax_query'], $category_query);
            }
        }

        // Min / Max price filter.
        if (!empty($min_price) || !empty($max_price)) {
            $price_request = [];

            if (!empty($min_price)) {
                $price_request['min_price'] = $min_price;
            }

            if (!empty($max_price)) {
                $price_request['max_price'] = $max_price;
            }
            $args['meta_query'][] = wc_get_min_max_price_meta_query($price_request);
        }

        if (is_array($product_ids)) {
            if (count($product_ids) === 0) {
                // We should not return any result if product_ids is an empty array.
                // On the other hand, if it is not passed entirely, the query
                // proceeds normally without passing anything related to product_ids
                return $output;
            }

            $args['post__in'] = $product_ids;
        }

        $the_query = new WP_Query($args);

        if (!$the_query->have_posts()) {
            return $output;
        }

        wp_reset_postdata();

        $products_id = [];
        $posts = $the_query->posts;
        $found_posts = $the_query->found_posts;
        $max_num_pages = $the_query->max_num_pages;

        foreach ($posts as $post) {
            array_push($products_id, $post->ID);
        }

        $products_output = wc_get_products([
            'include' => $products_id,
            'limit' => -1, // unlimited
        ]);

        // Because `wc_get_products` breaks sorting, we re-sort based on DB result
        $products_output_sorted = [];
        foreach ($products_id as $id) {
            foreach ($products_output as $product) {
                if ($product->get_id() === $id) {
                    array_push($products_output_sorted, $product);
                }
            }
        }

        // Orderby condition. Name/Price.
        // if (!empty($orderby)) {
        //     $products_output = wc_products_array_orderby($products_output, $orderby, strtoupper($order));
        // } else {
        //     $products_output = wc_products_array_orderby($products_output, 'date', strtoupper($order));
        // }

        $products_data = [];
        foreach ($products_output_sorted as $product) {
            /*
             * @todo find a better way to get product data using get_data wound get the product meta without any hook
             * This todo could break the front end depending on what params are used on the frontend
             * */

            $product_data = Riothere_All_In_One_Admin::get_product_data($product->get_id(), $image_size);
            array_push($products_data, $product_data);
        }

        $products_data = [
            'data' => $products_data,
            'found_posts' => $found_posts,
            'max_num_pages' => $max_num_pages,
        ];

        return $products_data;

    }

}
