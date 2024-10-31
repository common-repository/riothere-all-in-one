<?php

class Riothere_Catalog
{

    public static function get_category_id_by_slug($category_slug)
    {
        $category = get_term_by('slug', $category_slug, 'product_cat');

        $id = false;
        if ($category) {
            $id = $category->term_id;
        }

        return $id;
    }

    public static function get_category_by_slug($category_slug)
    {
        $category = get_term_by('slug', $category_slug, 'product_cat');

        if ($category) {
            _make_cat_compat($category);
            $category->id = $category->term_id;
        }

        return $category;
    }

    public static function get_child_categories($category_id)
    {
        $args = array(
            'taxonomy' => 'product_cat',
            'show_count' => 1,
            'pad_counts' => 0,
            'hierarchical' => 1,
            'parent' => $category_id,
        );

        $categories = get_categories($args);

        $new_categories = [];
        foreach ($categories as $category) {

            $child_categories = self::get_child_categories($category->term_id);
            if (count($child_categories) > 0) {
                $category->children = $child_categories;
            } else {
                $category->children = new stdClass();
            }

            $new_categories[] = $category;
        }

        return self::format_categories($new_categories);

    }

    private static function format_categories($categories)
    {
        $new_categories = [];

        foreach ($categories as $category) {
            $category->id = $category->term_id;
            $new_categories[$category->term_id] = $category;
        }

        return $new_categories;
    }

    public static function formatColors($colours)
    {
        $new_colours = [];

        foreach ($colours as $color) {
            $color->color = $color->description;

            if (count((array) $color->children) > 0) {
                $color->children = self::formatColors($color->children);
            }

            $new_colours[$color->id] = $color;
        }

        return $new_colours;
    }

    public static function get_products_count($args)
    {
        // Only get `listed` products
        $query_args = [
            'post_type' => 'product',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'paged' => 1,
            'meta_query' => array(
                'relation' => "AND",
                array(
                    'key' => 'status',
                    'value' => 'listed',
                    'compare' => '=',
                ),
            ),
        ];

        $current_min_price = isset($args['min']) ? floatval($args['min']) : 0;
        $current_max_price = isset($args['max']) ? floatval($args['max']) : PHP_INT_MAX;

        $query_args['meta_query'][] = apply_filters(
            'woocommerce_get_min_max_price_meta_query',
            array(
                'key' => '_price',
                'value' => array($current_min_price, $current_max_price),
                'compare' => 'BETWEEN',
                'type' => 'DECIMAL(10,' . wc_get_price_decimals() . ')',
            ),
            $args
        );

        $products_query = new WP_Query($query_args);

        return $products_query->found_posts;
    }

}

new Riothere_Catalog();
